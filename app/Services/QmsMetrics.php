<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ControlledDocument;
use App\Models\CorrectiveAction;
use App\Models\InternalAudit;
use App\Models\NonConformity;
use App\Models\Risk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * QMS-20 (card 6afa0db1de9f235c9653f26f): the numbers on the dashboard.
 *
 * Spec §17/§26: a metric must be traceable to the records behind it and must
 * carry its reporting period. The worst outcome for this card is a tile that
 * says 7 while the list it links to says 8, so the query that counts and the
 * query that lists are the *same* definition — each tile exposes a `query()`
 * and a `url()`, and the dashboard and the tests both drive them.
 *
 * Deliberately no percentage or target-based KPI: Quality Objectives is a
 * separate future card, and inventing a target here would mean inventing the
 * standard the agency is being measured against.
 */
class QmsMetrics
{
    /**
     * The tile set. Order matters: it is the order shown on the dashboard, and
     * it runs roughly worst-first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function tiles(?Carbon $now = null): array
    {
        $now = ($now ?? now())->copy();
        $today = $now->copy()->startOfDay();

        return [
            [
                'key'     => 'open_ncs',
                'label'   => 'Open non-conformities',
                'hint'    => 'Recorded and not yet closed.',
                'period'  => 'Current open items',
                'count'   => $this->openNonConformities()->count(),
                'url'     => route('non_conformities.index', ['tab' => 'open']),
                'tone'    => 'neutral',
                'as_of'   => $now,
            ],
            [
                'key'     => 'overdue_ncs',
                'label'   => 'Overdue non-conformities',
                'hint'    => 'Past their due date and still open.',
                'period'  => 'Due before ' . $today->format('d M Y'),
                'count'   => $this->overdueNonConformities($today)->count(),
                'url'     => route('non_conformities.index', ['tab' => 'overdue']),
                'tone'    => 'bad',
                'as_of'   => $now,
            ],
            [
                'key'     => 'overdue_actions',
                'label'   => 'Overdue corrective actions',
                'hint'    => 'Past due and not yet marked complete.',
                'period'  => 'Due before ' . $today->format('d M Y'),
                'count'   => $this->overdueCorrectiveActions($today)->count(),
                'url'     => route('metrics.overdue_actions'),
                'tone'    => 'bad',
                'as_of'   => $now,
            ],
            [
                'key'     => 'risks_high',
                'label'   => 'High risks',
                'hint'    => 'Live risks assessed as high.',
                'period'  => 'Currently open',
                'count'   => $this->risksByLevel(Risk::LEVEL_HIGH)->count(),
                'url'     => route('risks.index', ['level' => Risk::LEVEL_HIGH]),
                'tone'    => 'warn',
                'as_of'   => $now,
            ],
            [
                'key'     => 'risks_critical',
                'label'   => 'Critical risks',
                'hint'    => 'Live risks assessed as critical.',
                'period'  => 'Currently open',
                'count'   => $this->risksByLevel(Risk::LEVEL_CRITICAL)->count(),
                'url'     => route('risks.index', ['level' => Risk::LEVEL_CRITICAL]),
                'tone'    => 'bad',
                'as_of'   => $now,
            ],
            [
                'key'     => 'complaints_overdue',
                'label'   => 'Overdue complaints',
                'hint'    => 'Past the promised acknowledgement or resolution date.',
                'period'  => 'Promised before ' . $today->format('d M Y'),
                'count'   => $this->overdueComplaints($today)->count(),
                'url'     => route('complaints.index', ['status' => 'overdue']),
                'tone'    => 'bad',
                'as_of'   => $now,
            ],
            [
                'key'     => 'documents_due',
                'label'   => 'Documents due for review',
                'hint'    => 'On or before their next review date.',
                'period'  => 'Due by ' . $today->copy()->addDays(30)->format('d M Y'),
                'count'   => $this->documentsDueForReview($today)->count(),
                'url'     => route('metrics.documents_due'),
                'tone'    => 'warn',
                'as_of'   => $now,
            ],
            [
                'key'     => 'audits_upcoming',
                'label'   => 'Upcoming audits',
                'hint'    => 'Scheduled and not yet conducted.',
                'period'  => 'Next 30 days to ' . $today->copy()->addDays(30)->format('d M Y'),
                'count'   => $this->upcomingAudits($today)->count(),
                'url'     => route('metrics.upcoming_audits'),
                'tone'    => 'neutral',
                'as_of'   => $now,
            ],
        ];
    }

    // ---------------------------------------------------------------------
    // The definitions. Each one is the single source of truth for both the
    // tile's number and the list it links to.
    // ---------------------------------------------------------------------

    /** Mirrors NonConformityController's "open" tab exactly. */
    public function openNonConformities(): Builder
    {
        return NonConformity::query()
            ->where('status', '!=', NonConformity::STATUS_CLOSED);
    }

    /** Mirrors NonConformityController's "overdue" tab exactly. */
    public function overdueNonConformities(?Carbon $today = null): Builder
    {
        $today = ($today ?? now())->copy()->startOfDay();

        return NonConformity::query()
            ->where('status', '!=', NonConformity::STATUS_CLOSED)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today);
    }

    public function overdueCorrectiveActions(?Carbon $today = null): Builder
    {
        $today = ($today ?? now())->copy()->startOfDay();

        return CorrectiveAction::query()
            ->whereNull('completed_date')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today);
    }

    /** Mirrors RiskController's `level` filter, plus "still live". */
    public function risksByLevel(string $level): Builder
    {
        return Risk::query()
            ->where('status', '!=', Risk::STATUS_CLOSED)
            ->where('risk_level', $level);
    }

    /** Mirrors ComplaintController's "overdue" tab exactly. */
    public function overdueComplaints(?Carbon $today = null): Builder
    {
        $today = ($today ?? now())->copy()->startOfDay();

        return Complaint::query()
            ->whereNotIn('status', Complaint::CLOSED_STATUSES)
            ->where(function ($inner) use ($today) {
                $inner->where(function ($ack) use ($today) {
                    $ack->whereNull('acknowledged_at')
                        ->whereNotNull('target_ack_date')
                        ->whereDate('target_ack_date', '<', $today);
                })->orWhere(function ($resolve) use ($today) {
                    $resolve->whereNull('resolved_at')
                        ->whereNotNull('target_resolve_date')
                        ->whereDate('target_resolve_date', '<', $today);
                });
            });
    }

    /**
     * Documents whose review window has opened. A document due in the far
     * future is not a task yet, so the window is 30 days.
     */
    public function documentsDueForReview(?Carbon $today = null): Builder
    {
        $today = ($today ?? now())->copy()->startOfDay();

        return ControlledDocument::query()
            ->whereNotNull('next_review_date')
            ->whereDate('next_review_date', '<=', $today->copy()->addDays(30))
            ->whereNotIn('status', [
                ControlledDocument::STATUS_SUPERSEDED,
                ControlledDocument::STATUS_OBSOLETE,
            ]);
    }

    public function upcomingAudits(?Carbon $today = null): Builder
    {
        $today = ($today ?? now())->copy()->startOfDay();

        return InternalAudit::query()
            ->whereNull('conducted_date')
            ->whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '<=', $today->copy()->addDays(30))
            ->where('status', '!=', InternalAudit::STATUS_CLOSED);
    }
}
