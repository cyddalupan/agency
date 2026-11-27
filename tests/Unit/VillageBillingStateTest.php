<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\GodMode\VillageBillingState;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Card 6b07f35fc3903d22f1b8444f — every row of Cyd's worked example, plus the
 * edge cases the card calls out (clamping, anchoring, zero grace, overpay).
 *
 * A plain PHPUnit TestCase on purpose: VillageBillingState touches no database
 * and no container, so the arithmetic can be proven without booting Laravel.
 *
 * The example: ₱200,000 over 3 instalments (₱66,666.67/mo), ₱6,000 monthly
 * retainer, down payment paid 2026-01-15.
 */
class VillageBillingStateTest extends TestCase
{
    private function plan(array $overrides = []): array
    {
        return array_merge([
            'initial_payment' => '200000.00',
            'initial_terms'   => 3,
            'monthly_amount'  => '6000.00',
            'terms'           => 12,
            'starts_on'       => '2026-01-01',
        ], $overrides);
    }

    /** @param array<int, array{0:string,1:string,2:string}> $payments [type, amount, paid_on] */
    private function state(string $today, array $payments, ?array $plan = null): array
    {
        $rows = [];
        $initialSum = 0.0; $initialCount = 0;
        $monthlySum = 0.0; $monthlyCount = 0;

        foreach ($payments as [$type, $amount, $on]) {
            $rows[] = ['payment_type' => $type, 'amount' => $amount, 'paid_on' => $on];
            if ($type === 'initial') { $initialSum += (float) $amount; $initialCount++; }
            else { $monthlySum += (float) $amount; $monthlyCount++; }
        }

        return (new VillageBillingState(
            $plan ?? $this->plan(),
            [
                'initial_paid'  => (string) $initialSum,
                'initial_count' => $initialCount,
                'monthly_paid'  => (string) $monthlySum,
                'monthly_count' => $monthlyCount,
            ],
            $rows,
            CarbonImmutable::parse($today)->startOfDay()
        ))->axes();
    }

    public function test_plan_created_nothing_paid_is_no_billing_yet(): void
    {
        $s = $this->state('2026-01-10', []);
        $this->assertSame(VillageBillingState::INITIAL_NONE, $s['initial_state']);
        $this->assertSame(VillageBillingState::MONTHLY_IDLE, $s['monthly_state']);
        $this->assertSame('none', $s['state']);
    }

    public function test_first_payment_is_paying_in_and_anchors_the_schedule(): void
    {
        $s = $this->state('2026-01-15', [['initial', '66666.67', '2026-01-15']]);
        $this->assertSame(VillageBillingState::INITIAL_PAYING, $s['initial_state']);
        $this->assertSame('2026-02-15', $s['initial_due_on'], 'anchor must be the payment date, not starts_on');
        $this->assertFalse($s['late_initial']);
    }

    public function test_second_instalment_due_and_unpaid_is_paying_in_plus_late(): void
    {
        $s = $this->state('2026-02-15', [['initial', '66666.67', '2026-01-15']]);
        $this->assertSame(VillageBillingState::INITIAL_PAYING, $s['initial_state']);
        $this->assertTrue($s['late_initial']);
        $this->assertSame('overdue', $s['state']);
    }

    public function test_day_before_due_is_not_late(): void
    {
        $s = $this->state('2026-02-14', [['initial', '66666.67', '2026-01-15']]);
        $this->assertFalse($s['late_initial'], 'due at END of period: 02-14 is not late for a 02-15 instalment');
    }

    public function test_clearing_the_initial_is_paid_up(): void
    {
        $s = $this->state('2026-03-15', [
            ['initial', '66666.67', '2026-01-15'],
            ['initial', '66666.67', '2026-02-15'],
            ['initial', '66666.66', '2026-03-15'],
        ]);
        $this->assertSame(VillageBillingState::INITIAL_PAID, $s['initial_state']);
        $this->assertTrue($s['initial_cleared']);
        $this->assertSame('settled', $s['state']);
    }

    /** The row the single-status model loses. */
    public function test_paid_up_and_delayed_coexist(): void
    {
        $s = $this->state('2026-04-15', [
            ['initial', '200000.00', '2026-03-15'],
        ]);
        $this->assertSame(VillageBillingState::INITIAL_PAID, $s['initial_state'], 'body stays Paid up');
        $this->assertSame(VillageBillingState::MONTHLY_LATE, $s['monthly_state'], 'marker is Delayed');
        $this->assertTrue($s['late_monthly']);
        // Compatibility label is the pessimistic one; the two axes carry the truth.
        $this->assertSame('overdue', $s['state']);
    }

    public function test_paying_the_retainer_clears_the_marker(): void
    {
        $s = $this->state('2026-04-20', [
            ['initial', '200000.00', '2026-03-15'],
            ['monthly', '6000.00', '2026-04-20'],
        ]);
        $this->assertSame(VillageBillingState::INITIAL_PAID, $s['initial_state']);
        $this->assertSame(VillageBillingState::MONTHLY_OK, $s['monthly_state']);
        $this->assertSame('settled', $s['state']);
    }

    /** AC5: a 31st anchor clamps to Feb 28/29 and returns to Mar 31 with no drift. */
    public function test_month_end_anchor_clamps_without_drifting(): void
    {
        $state = new VillageBillingState(null, [], [], CarbonImmutable::parse('2026-01-31'));
        $anchor = CarbonImmutable::parse('2026-01-31');

        $this->assertSame('2026-02-28', $state->dueDateFor($anchor, 1)->toDateString());
        $this->assertSame('2026-03-31', $state->dueDateFor($anchor, 2)->toDateString(), 'clamp from the ORIGINAL day');
        $this->assertSame('2026-04-30', $state->dueDateFor($anchor, 3)->toDateString());
    }

    public function test_leap_year_february(): void
    {
        $state = new VillageBillingState(null, [], [], CarbonImmutable::parse('2028-01-31'));
        $this->assertSame('2028-02-29', $state->dueDateFor(CarbonImmutable::parse('2028-01-31'), 1)->toDateString());
    }

    /** AC6: zero grace — due today and unpaid is late today. */
    public function test_instalment_due_today_is_late_today(): void
    {
        $s = $this->state('2026-02-15', [['initial', '66666.67', '2026-01-15']]);
        $this->assertTrue($s['late_initial']);
    }

    /** AC7: a partial payment advances the sum but does not complete an instalment. */
    public function test_partial_payment_advances_sum_not_instalments(): void
    {
        $s = $this->state('2026-02-15', [['initial', '30000.00', '2026-01-15']]);
        $this->assertSame(VillageBillingState::INITIAL_PAYING, $s['initial_state']);
        $this->assertTrue($s['late_initial'], 'still short, so the next due date still arrives');
    }

    /** AC9: overpaying shows honestly rather than being clamped. */
    public function test_overpaid_initial_is_paid_up(): void
    {
        $s = $this->state('2026-01-20', [['initial', '250000.00', '2026-01-15']]);
        $this->assertSame(VillageBillingState::INITIAL_PAID, $s['initial_state']);
        $this->assertTrue($s['initial_cleared']);
    }

    /** AC8: voiding the only payment drops the agency back to No billing. */
    public function test_no_payments_left_returns_to_none(): void
    {
        $s = $this->state('2026-02-20', []);
        $this->assertSame(VillageBillingState::INITIAL_NONE, $s['initial_state']);
        $this->assertSame('none', $s['state']);
    }

    /** AC4: starts_on is irrelevant to due dates. */
    public function test_starts_on_does_not_anchor_due_dates(): void
    {
        $s = $this->state('2026-01-15', [['initial', '66666.67', '2026-01-15']], $this->plan(['starts_on' => '2025-06-01']));
        $this->assertSame('2026-02-15', $s['initial_due_on']);
    }

    public function test_no_plan_means_no_billing(): void
    {
        $state = new VillageBillingState(null, [
            'initial_paid' => '0', 'initial_count' => 0,
            'monthly_paid' => '0', 'monthly_count' => 0,
        ], [], CarbonImmutable::parse('2026-01-15'));

        $s = $state->axes();
        $this->assertSame(VillageBillingState::INITIAL_NONE, $s['initial_state']);
        $this->assertSame('none', $s['state']);
    }
}
