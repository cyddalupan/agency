<?php

declare(strict_types=1);

namespace App\Services\GodMode;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * Card 6b07f35fc3903d22f1b8444f — the two-axis billing state for one agency.
 *
 * Cyd's correction (2026-11-26) is the whole reason this is a class and not a
 * ternary in `village()`: **status is not one value.** An agency can be Paid up
 * on the initial and Delayed on the monthly *at the same time*, and collapsing
 * that into a single enum silently erases the fact that they honoured the
 * down payment. So there are two independent axes and the village renders both.
 *
 * Rules, all decided by Cyd on 2026-11-26:
 *   - instalments are due at the END of the period, so you are never late on
 *     the day before a due date;
 *   - the interval is CALENDAR MONTHS, not 30 days;
 *   - the anchor is the date of the FIRST payment, not `plan.starts_on`
 *     ("the down payment is the first payment");
 *   - partial payments advance a running SUM, they do not complete instalments;
 *   - no grace period at all (`OVERDUE_GRACE_DAYS = 0`) — show it immediately.
 *
 * There is no stored expected row anywhere: everything here is derived on read.
 *
 * This class holds no database access. `village()` does three queries and hands
 * the numbers in, so the arithmetic is testable without a database and cannot
 * quietly become an N+1.
 */
final class VillageBillingState
{
    public const INITIAL_NONE = 'initial_none';
    public const INITIAL_PAYING = 'initial_paying';
    public const INITIAL_PAID = 'initial_paid';

    public const MONTHLY_IDLE = 'monthly_idle';
    public const MONTHLY_OK = 'monthly_ok';
    public const MONTHLY_LATE = 'monthly_late';

    /**
     * Cyd: *"no grace period we need to see it immediately"*. A named constant
     * so the decision is visible in code rather than hidden in an arithmetic
     * comparison somebody could "tidy up".
     */
    public const OVERDUE_GRACE_DAYS = 0;

    /**
     * @param  array{initial_payment: string|float, initial_terms: int, monthly_amount: string|float, terms: int, starts_on: string|null}|null  $plan
     * @param  array{initial_paid: string|float, initial_count: int, monthly_paid: string|float, monthly_count: int}  $paid
     * @param  array<int, array{paid_on: string, amount: string|float, payment_type: string}>  $payments  non-voided, any order
     */
    public function __construct(
        private readonly ?array $plan,
        private readonly array $paid,
        private readonly array $payments,
        private readonly CarbonImmutable $today,
    ) {
    }

    /** @return array{initial_state: string, monthly_state: string, state: string, initial_cleared: bool, initial_cleared_on: ?string, initial_due_on: ?string, monthly_due_on: ?string, monthly_expected: int, late_initial: bool, late_monthly: bool} */
    public function axes(): array
    {
        $initialPaid = (float) ($this->paid['initial_paid'] ?? 0);
        $initialCount = (int) ($this->paid['initial_count'] ?? 0);
        $initialDue = $this->plan ? (float) $this->plan['initial_payment'] : 0.0;

        // ── Axis 1: the initial (down-payment) agreement ──
        $cleared = $this->plan !== null
            && $initialCount > 0
            && $initialPaid + 0.00001 >= $initialDue;

        $initialState = match (true) {
            $this->plan === null || $initialCount === 0 => self::INITIAL_NONE,
            ! $cleared => self::INITIAL_PAYING,
            default => self::INITIAL_PAID,
        };

        // Anchor for the initial schedule: the first payment's date. "They pay
        // the down payment which is considered first payment."
        $anchor = $this->firstPaymentDate();

        $initialClearedOn = $cleared ? $this->dateInitialCleared() : null;

        // How many initial instalments SHOULD have been paid by today. Due at
        // the end of each period, so instalment n lands on anchor + n months
        // (instalment 1 is one month after the anchor payment).
        $initialExpected = $anchor === null
            ? 0
            : $this->instalmentsDue($anchor, (int) ($this->plan['initial_terms'] ?? 0));

        // Late on the initial axis means: the sum paid so far has not covered
        // the instalments that were already due. Comparing COUNTS alone would
        // call a ₱30,000 part-payment "one instalment done" — Cyd's rule is
        // that partials advance the amount, not the instalment count.
        $initialOutstandingForDue = $this->plan === null || (int) ($this->plan['initial_terms'] ?? 0) <= 0
            ? 0.0
            : (($initialDue / (int) $this->plan['initial_terms']) * $initialExpected);

        $lateInitial = $anchor !== null
            && $initialExpected > 0
            && $initialPaid + 0.00001 < $initialOutstandingForDue
            && ! $cleared;

        // ── Axis 2: the monthly retainer, only in force once the initial clears ──
        $monthlyState = self::MONTHLY_IDLE;
        $monthlyExpected = 0;
        $lateMonthly = false;

        if ($cleared && $initialClearedOn !== null) {
            // The retainer is a RECURRING monthly charge, unlike the initial
            // schedule where the anchor payment IS instalment 1. Month 1 of the
            // retainer is the month right after the initial cleared, and its
            // instalment falls due at the END of that month — so instalment 1
            // is owed from `cleared_on + 1 month`, not on the clearing day
            // itself. Anchoring at the clearing date would bill the client for
            // a month they had not yet had.
            $monthlyAnchor = $initialClearedOn;
            $monthlyExpected = $this->instalmentsDueFrom($monthlyAnchor->addMonthNoOverflow(), (int) ($this->plan['terms'] ?? 0));

            // Same rule as the initial axis: compare MONEY, not counts, so a
            // part-paid instalment still counts as owed.
            $monthlyOwed = (float) ($this->plan['monthly_amount'] ?? 0) * $monthlyExpected;
            $monthlyPaid = (float) ($this->paid['monthly_paid'] ?? 0);

            $lateMonthly = $monthlyPaid + 0.00001 < $monthlyOwed;
            $monthlyState = $lateMonthly ? self::MONTHLY_LATE : self::MONTHLY_OK;
        }

        return [
            'initial_state'       => $initialState,
            'monthly_state'       => $monthlyState,
            'initial_cleared'     => $cleared,
            'initial_cleared_on'  => $initialClearedOn?->toDateString(),
            // Next instalment not yet due: index is 0-based off the anchor, and
            // `$initialExpected` counts instalments already fallen due, so the
            // next one sits at that index (instalment n -> index n-1).
            'initial_due_on'      => $anchor !== null ? $this->dueDateFor($anchor, $initialExpected)->toDateString() : null,
            'monthly_due_on'      => isset($monthlyAnchor) ? $this->dueDateFor($monthlyAnchor->addMonthNoOverflow(), max(0, $monthlyExpected - 1))->toDateString() : null,
            'monthly_expected'    => $monthlyExpected,
            'late_initial'        => $lateInitial,
            'late_monthly'        => $lateMonthly,
            'state'               => $this->compatLabel($initialState, $lateInitial, $lateMonthly),
        ];
    }

    /**
     * The single label the sprite mapping and `data-state` still read.
     *
     * Kept deliberately boring: it only has the four values the existing art
     * understands, so this card cannot break the village's appearance. The
     * truth lives in `initial_state` + `monthly_state`; this is the shadow they
     * cast for code that has not been taught about two axes yet.
     */
    private function compatLabel(string $initialState, bool $lateInitial, bool $lateMonthly): string
    {
        return match (true) {
            $initialState === self::INITIAL_NONE => 'none',
            $initialState === self::INITIAL_PAYING => $lateInitial ? 'overdue' : 'pending',
            $lateMonthly => 'overdue',
            default => 'settled',
        };
    }

    /**
     * Calendar-month day, clamped to the end of the target month.
     *
     * The clamp must always be computed from the ORIGINAL anchor day, never
     * from a previously clamped date, or a 31st anchor drifts to the 28th and
     * stays there. `$index` is 0-based: index 0 is the anchor itself.
     */
    public function dueDateFor(CarbonImmutable $anchor, int $index): CarbonImmutable
    {
        $months = $anchor->month - 1 + $index;
        $year = $anchor->year + intdiv($months, 12);
        $month = ($months % 12) + 1;
        $day = min($anchor->day, Carbon::create($year, $month, 1)->daysInMonth);

        return CarbonImmutable::create($year, $month, $day)->startOfDay();
    }

    /**
     * Count of instalments fallen due, where `$first` is instalment 1's date.
     *
     * Used for the monthly retainer, whose instalment 1 lands on
     * `cleared_on + 1 month` rather than on an anchor payment date.
     */
    private function instalmentsDueFrom(CarbonImmutable $first, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        $due = 0;
        for ($i = 0; $i < $total; $i++) {
            $date = $this->dueDateFor($first, $i);
            if ($date->lessThanOrEqualTo($this->today->startOfDay())) {
                $due = $i + 1;
            } else {
                break;
            }
        }

        return $due;
    }

    /**
     * How many instalments are due by `today`.
     *
     * The anchor IS instalment 1 — Cyd: *"they pay the down payment which is
     * considered first payment"*. So instalment n falls on
     * `anchor + (n-1) months`: with a 2026-01-15 anchor, instalment 2 is due
     * 2026-02-15, exactly as the worked example says.
     *
     * `$total` bounds how many can ever fall due (`initial_terms` / `terms`).
     *
     * Returns the INDEX of the last instalment that has fallen due, which is
     * also the count of instalments owing. Due at the END of the period with
     * zero grace: an instalment is owing the moment its date arrives.
     */
    private function instalmentsDue(CarbonImmutable $anchor, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        $due = 0;
        for ($i = 1; $i <= $total; $i++) {
            $date = $this->dueDateFor($anchor, $i - 1);
            if ($date->lessThanOrEqualTo($this->today->startOfDay())) {
                $due = $i;
            } else {
                break;
            }
        }

        return $due;
    }

    /** Whole calendar months from `$from` to `$to`, floored at zero. */
    private function wholeMonthsBetween(CarbonImmutable $from, CarbonImmutable $to): int
    {
        if ($to->lessThan($from)) {
            return 0;
        }

        $months = ($to->year - $from->year) * 12 + ($to->month - $from->month);

        return max(0, $months);
    }

    /** The earliest non-voided initial payment date — the schedule anchor. */
    private function firstPaymentDate(): ?CarbonImmutable
    {
        $dates = [];
        foreach ($this->payments as $p) {
            if (($p['payment_type'] ?? null) === 'initial' && ! empty($p['paid_on'])) {
                $dates[] = CarbonImmutable::parse($p['paid_on'])->startOfDay();
            }
        }

        if ($dates === []) {
            return null;
        }

        sort($dates);

        return $dates[0];
    }

    /**
     * The date the running total first reached `initial_payment`.
     *
     * This is the monthly anchor: the retainer starts counting from the month
     * the down payment was settled, not from the plan's start date.
     */
    private function dateInitialCleared(): ?CarbonImmutable
    {
        if ($this->plan === null) {
            return null;
        }

        $target = (float) $this->plan['initial_payment'];
        $rows = [];
        foreach ($this->payments as $p) {
            if (($p['payment_type'] ?? null) === 'initial' && ! empty($p['paid_on'])) {
                $rows[] = $p;
            }
        }

        usort($rows, fn ($a, $b) => strcmp((string) $a['paid_on'], (string) $b['paid_on']));

        $running = 0.0;
        foreach ($rows as $p) {
            $running += (float) $p['amount'];
            if ($running + 0.00001 >= $target) {
                return CarbonImmutable::parse($p['paid_on'])->startOfDay();
            }
        }

        return null;
    }
}
