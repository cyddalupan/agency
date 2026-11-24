<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Card 6b056e02: every transaction edit appears in the Status / Transaction
 * History panel ("Amount: 54,000.00 -> 5,400.00"), with ids resolved to names.
 *
 * The panels read `receivable_histories` / `expense_request_histories`, NOT
 * `audit_events` — and line-item amounts have no historical audit rows at all,
 * so the diff is written into the history row's free-text `note` explicitly.
 * One grouped row per save (the timelines render one <li> / <tr> per row).
 *
 * The display format is `Amount: 54,000.00 -> 5,400.00; Particular: "a" -> "b"`.
 * Callers prepend the actor ("Edited by ...") — see editedNote().
 */
class TransactionEditLog
{
    /** Fields belonging to the request header, compared before the lines. */
    private const HEADER_FIELDS = ['date', 'branch_id', 'notes'];

    /**
     * Human label per diffable field. A field not listed here is ignored.
     */
    private const FIELD_LABELS = [
        // Receivable
        'date'          => 'Date',
        'ref_ar'        => 'Ref AR',
        'agent_id'      => 'Agent',
        'applicant_id'  => 'Applicant',
        'amount'        => 'Amount',
        'account'       => 'Account',
        'debit_account' => 'Debit Account',
        'type'          => 'Type',
        'mode'          => 'Mode',
        'particular'    => 'Particular',
        // Expense request header
        'branch_id'     => 'Branch',
        'notes'         => 'Notes',
        // Expense request line
        'charge'        => 'Charge',
        'country_id'    => 'Country',
        'currency'      => 'Currency',
        'payment'       => 'Payment',
        'account_id'    => 'Account',
    ];

    /** Money fields formatted as 54,000.00 rather than a raw number. */
    private const MONEY_FIELDS = ['amount', 'payment'];

    /** Free-text fields quoted so boundaries are visible in the timeline. */
    private const TEXT_FIELDS = ['ref_ar', 'account', 'debit_account', 'type', 'mode', 'particular', 'notes', 'charge', 'currency'];

    /** Model|field pairs whose value is an id that must be shown as a name. */
    private const ID_LOOKUPS = [
        ['agent_id', Agent::class, 'name'],
        ['applicant_id', Applicant::class, null],
        ['branch_id', \App\Models\Branch::class, 'name'],
        ['country_id', \App\Models\Country::class, 'name'],
        ['account_id', Account::class, null],
    ];

    /**
     * Build the note body for a Receivable edit. Returns null when nothing
     * changed (acceptance criterion 8: no row for a no-op save).
     */
    public function receivableChanges(Receivable $receivable, array $original): ?string
    {
        $changes = $this->diff(
            $receivable->getAttributes(),
            $original,
            array_keys(self::FIELD_LABELS),
            $receivable->agency_id,
        );

        return $changes === [] ? null : implode('; ', $changes);
    }

    /**
     * Build the note body for an Expense Request edit, header + line items.
     *
     * @param  array  $originalHeader  attributes captured before update()
     * @param  array  $originalItems   [itemId => attributes] captured before update()
     * @param  array  $itemsAfter      [itemId => attributes] captured after update()
     */
    public function expenseRequestChanges(
        int $agencyId,
        array $originalHeader,
        array $currentHeader,
        array $originalItems,
        array $itemsAfter
    ): ?string {
        $lines = $this->diff($currentHeader, $originalHeader, self::HEADER_FIELDS, $agencyId);

        // Only compare the mutable line fields; status/file belong to other flows.
        $lineFields = ['charge', 'agent_id', 'applicant_id', 'country_id', 'currency', 'amount', 'payment', 'account_id', 'particular'];

        foreach ($itemsAfter as $id => $after) {
            $position = $after['__position'] ?? null;

            if (! array_key_exists($id, $originalItems)) {
                $lines[] = $this->addedLine($after, $position);
                continue;
            }

            $fieldChanges = $this->diff($after, $originalItems[$id], $lineFields, $agencyId);
            if ($fieldChanges !== []) {
                $prefix = $position ? "Line {$position}" : 'Line';
                $lines[] = $prefix . ' ' . implode('; ', $fieldChanges);
            }
        }

        foreach ($originalItems as $id => $before) {
            if (! array_key_exists($id, $itemsAfter)) {
                $lines[] = $this->removedLine($before);
            }
        }

        return $lines === [] ? null : implode('; ', $lines);
    }

    /**
     * Build the note body for a Payment edit (employer-bill ledger).
     */
    public function paymentChanges(Model $payment, array $original): ?string
    {
        $changes = $this->diff(
            $payment->getAttributes(),
            $original,
            ['bill_id', 'amount', 'category', 'type', 'reference_no', 'status', 'payment_date', 'notes'],
            $payment->agency_id,
        );

        return $changes === [] ? null : implode('; ', $changes);
    }

    /**
     * Prefix for the note. The panels render `note` as the only free text, so
     * the actor is part of the sentence, not a separate column.
     */
    public function editedNote(string $actorName, string $changes): string
    {
        return 'Edited by ' . $actorName . ' - ' . $changes;
    }

    /**
     * One row per touched expense request for the Payments tab writers.
     * Card 6b056e02 AC12: `Payment: 0.00 -> 5,400.00` must appear in History
     * after a bulk upload, grouped per request rather than per line.
     *
     * @param  array  $byRequest  [expenseRequestId => ['model' => ExpenseRequest, 'segments' => string[]]]
     */
    public function writePaymentHistory(array $byRequest, string $actorName): void
    {
        foreach ($byRequest as $entry) {
            /** @var \App\Models\ExpenseRequest $expenseRequest */
            $expenseRequest = $entry['model'];
            $segments = array_values(array_filter($entry['segments'] ?? []));
            if ($segments === []) {
                continue;
            }

            $this->write(\App\Models\ExpenseRequestStatusHistory::class, [
                'expense_request_id' => $expenseRequest->id,
                'agency_id'          => $expenseRequest->agency_id,
                'user_id'            => auth()->id(),
                'from_status'        => $expenseRequest->status,
                'to_status'          => $expenseRequest->status,
                'note'               => $this->editedNote($actorName, implode('; ', $segments)),
            ]);
        }
    }

    /**
     * Write the history row, swallowing (but reporting) failures: the edit
     * itself must still commit — the same rule AuditsChanges follows.
     */
    public function write(string $historyModel, array $attributes): void
    {
        try {
            $historyModel::create($attributes);
        } catch (Throwable $e) {
            report($e);
        }
    }

    // ------------------------------------------------------------------ diff

    /**
     * @return string[]  formatted "Label: before -> after" segments
     */
    private function diff(array $after, array $before, array $fields, ?int $agencyId): array
    {
        $out = [];

        foreach ($fields as $field) {
            if (! array_key_exists($field, self::FIELD_LABELS)) {
                continue;
            }

            $old = $before[$field] ?? null;
            $new = $after[$field] ?? null;

            // Numbers and dates arrive as 5400 vs "5400.00"; compare loosely.
            if ($this->isBlank($old) && $this->isBlank($new)) {
                continue;
            }

            // A field the caller cannot change must never appear in the diff.
            if (! array_key_exists($field, $before) && ! array_key_exists($field, $after)) {
                continue;
            }
            if (! $this->isBlank($old) && ! $this->isBlank($new)
                && $field === 'date' && $this->normaliseDate($old) === $this->normaliseDate($new)) {
                continue;
            }
            if (! $this->isBlank($old) && ! $this->isBlank($new)
                && in_array($field, self::MONEY_FIELDS, true)
                && abs((float) $old - (float) $new) < 0.00001) {
                continue;
            }
            if (! $this->isBlank($old) && ! $this->isBlank($new)
                && in_array($field, self::TEXT_FIELDS, true) && (string) $old === (string) $new) {
                continue;
            }
            if (! $this->isBlank($old) && ! $this->isBlank($new)
                && ! in_array($field, self::TEXT_FIELDS, true)
                && ! in_array($field, self::MONEY_FIELDS, true)
                && $field !== 'date'
                && (string) $old === (string) $new) {
                continue;
            }
            $label = self::FIELD_LABELS[$field];
            $out[] = $label . ': ' . $this->formatValue($field, $old, $agencyId)
                . ' -> ' . $this->formatValue($field, $new, $agencyId);
        }

        return $out;
    }

    private function isBlank($value): bool
    {
        return $value === null || $value === '';
    }

    private function normaliseDate($value): string
    {
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private function formatValue(string $field, $value, ?int $agencyId)
    {
        if ($this->isBlank($value)) {
            return '(none)';
        }

        if ($field === 'date') {
            try {
                return \Illuminate\Support\Carbon::parse($value)->format('M d, Y');
            } catch (Throwable) {
                return (string) $value;
            }
        }

        if (in_array($field, self::MONEY_FIELDS, true)) {
            return number_format((float) $value, 2);
        }

        foreach (self::ID_LOOKUPS as [$lookupField, $model, $labelColumn]) {
            if ($lookupField !== $field) {
                continue;
            }

            return $this->resolveId($model, $value, $labelColumn, $agencyId, $field);
        }

        if (in_array($field, self::TEXT_FIELDS, true)) {
            return '"' . $value . '"';
        }

        return (string) $value;
    }

    /**
     * Resolve an id to a name. History rows must never read "account_id 14".
     */
    private function resolveId(string $model, $id, ?string $column, ?int $agencyId, string $field): string
    {
        try {
            /** @var Model|null $found */
            $found = $model::query()->withoutGlobalScopes()->find($id);
        } catch (Throwable $e) {
            report($e);
            return '#' . $id;
        }

        if (! $found) {
            return '#' . $id;
        }

        // Applicant: "Last, First" — the form every screen of this app uses.
        if ($model === Applicant::class) {
            return trim(($found->last_name ?? '') . ', ' . ($found->first_name ?? ''), ', ') ?: '#' . $id;
        }

        // Account: "Main -> Sub" so the parent is legible on a line edit.
        if ($model === Account::class) {
            $name = $found->name ?? ('#' . $id);
            if (! empty($found->parent_id)) {
                $parent = Account::query()->withoutGlobalScopes()->find($found->parent_id);
                if ($parent) {
                    return ($parent->name ?? '') . ' -> ' . $name;
                }
            }
            return $name;
        }

        return (string) ($found->{$column} ?? ('#' . $id));
    }

    /**
     * "Line 2 added - 5,400.00 Visa fee"  ($item = plain attributes array)
     */
    private function addedLine(array $item, ?int $position): string
    {
        $prefix = $position ? "Line {$position}" : 'Line';
        $particular = trim((string) ($item['particular'] ?? ''));

        return $prefix . ' added - ' . number_format((float) ($item['amount'] ?? 0), 2)
            . ($particular !== '' ? ' ' . $particular : '');
    }

    /**
     * "Line removed - 1,200.00 Courier"
     */
    private function removedLine(array $item): string
    {
        $particular = trim((string) ($item['particular'] ?? ''));

        return 'Line removed - ' . number_format((float) ($item['amount'] ?? 0), 2)
            . ($particular !== '' ? ' ' . $particular : '');
    }
}
