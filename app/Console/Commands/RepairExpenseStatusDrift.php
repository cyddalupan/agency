<?php

namespace App\Console\Commands;

use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repair expense requests whose line items disagree with the parent status.
 *
 * Card 6b07b9c8. Before that card's fix, bulkUpdateStatus() repaired drifted
 * items silently when the request was already in the target status: no history,
 * no counter. If the second attempt never came, the request stayed one status
 * ahead of its own transactions.
 *
 * Dry-run by default. Pass --apply to write. Idempotent: a second run finds
 * nothing to do.
 */
class RepairExpenseStatusDrift extends Command
{
    protected $signature = 'expense:repair-drifted-statuses
                            {--agency= : Limit to one agency id}
                            {--apply : Actually write the repair (default is dry-run)}
                            {--limit=500 : Safety cap on requests processed in one run}';

    protected $description = 'Repair expense requests whose line items disagree with the request status (dry-run by default)';

    public function handle(): int
    {
        $agency = $this->option('agency');
        $apply  = (bool) $this->option('apply');
        $limit  = (int) $this->option('limit');

        $query = DB::table('expense_requests as r')
            ->join('expense_request_items as i', 'i.expense_request_id', '=', 'r.id')
            ->whereNull('r.deleted_at')
            ->whereColumn('r.status', '!=', 'i.status')
            ->when($agency, fn ($q) => $q->where('r.agency_id', $agency))
            ->select('r.id', 'r.agency_id', 'r.reference_no', 'r.status as request_status')
            ->distinct()
            ->orderBy('r.id')
            ->limit($limit);

        $requests = $query->get();

        if ($requests->isEmpty()) {
            $this->info('No drifted expense requests found. Nothing to repair.');
            return self::SUCCESS;
        }

        $this->warn(($apply ? 'APPLYING' : 'DRY RUN — pass --apply to write') . ' — ' . $requests->count() . ' request(s) with drifted transactions');

        $rows = [];
        $totalItems = 0;

        foreach ($requests as $row) {
            $drifted = DB::table('expense_request_items')
                ->where('expense_request_id', $row->id)
                ->where('status', '!=', $row->request_status)
                ->get(['id', 'status']);

            if ($drifted->isEmpty()) {
                continue;
            }

            $totalItems += $drifted->count();
            $rows[] = [
                $row->id,
                $row->reference_no,
                $row->request_status,
                $drifted->pluck('status')->unique()->implode(','),
                $drifted->count(),
            ];
        }

        $this->table(
            ['request', 'ref', 'request status', 'item statuses', 'items'],
            $rows
        );

        $this->info("Total: {$totalItems} drifted transaction(s) across " . count($rows) . ' request(s).');

        if (! $apply) {
            $this->comment('Dry run only. Nothing written.');
            return self::SUCCESS;
        }

        $repaired = 0;

        foreach ($requests as $row) {
            $expenseRequest = ExpenseRequest::find($row->id);

            if (! $expenseRequest) {
                continue;
            }

            $target = $expenseRequest->status;

            DB::transaction(function () use ($expenseRequest, $target, &$repaired) {
                $drifted = $expenseRequest->items()->where('status', '!=', $target)->get();

                if ($drifted->isEmpty()) {
                    return;
                }

                foreach ($drifted as $item) {
                    $item->update(['status' => $target]);
                }

                ExpenseRequestStatusHistory::create([
                    'expense_request_id' => $expenseRequest->id,
                    'agency_id'          => $expenseRequest->agency_id,
                    'user_id'            => null,
                    'from_status'        => $target,
                    'to_status'          => $target,
                    'note'               => 'Repaired ' . $drifted->count()
                        . ' drifted transaction(s) set to '
                        . (ExpenseRequest::STATUS_LABELS[$target] ?? $target)
                        . ' (card 6b07b9c8)',
                ]);

                $repaired += $drifted->count();
            });
        }

        $this->info("Repaired {$repaired} transaction(s).");

        return self::SUCCESS;
    }
}
