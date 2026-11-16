<?php

namespace App\Http\Controllers;

use App\Services\QmsMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * QMS-20 (card 6afa0db1de9f235c9653f26f): the QMS dashboard.
 *
 * Every tile is a number plus the link to the list that produces it. The link
 * is not decoration: three of the eight tiles had no filtered list to point at,
 * so those lists are built here too (overdue actions, documents due, upcoming
 * audits). A tile whose link 404s, or whose list disagrees with its number, is
 * worse than no tile at all — so both come from QmsMetrics and only from there.
 *
 * No percentage or target-based KPI: Quality Objectives does not exist yet.
 */
class QmsDashboardController extends Controller
{
    private const ROLES = ['super_admin', 'admin', 'qms_manager', 'internal_auditor'];

    public function index(Request $request, QmsMetrics $metrics): View
    {
        $this->authorise($request);

        $now = now();

        return view('qms.dashboard', [
            'tiles'  => $metrics->tiles($now),
            'asOf'   => $now,
            'period' => $now->format('F Y'),
        ]);
    }

    /** Corrective actions past due and not yet complete. */
    public function overdueActions(Request $request, QmsMetrics $metrics): View
    {
        $this->authorise($request);

        $today = now()->startOfDay();

        $actions = $metrics->overdueCorrectiveActions($today)
            ->with(['nonConformity', 'owner'])
            ->orderBy('due_date')
            ->paginate(50);

        return view('qms.metric_list', [
            'title'  => 'Overdue corrective actions',
            'hint'   => 'Past their due date and not yet marked complete.',
            'period' => 'Due before ' . $today->format('d M Y'),
            'count'  => $metrics->overdueCorrectiveActions($today)->count(),
            'rows'   => $actions->map(fn ($a) => [
                'ref'   => $a->nonConformity?->ref_no ?? ('CA-' . $a->id),
                'title' => $a->action_text,
                'due'   => $a->due_date,
                'meta'  => $a->owner ? trim($a->owner->surname . ', ' . $a->owner->name) : 'Unassigned',
                'url'   => route('non_conformities.show', $a->non_conformity_id),
            ]),
            'paginator' => $actions,
            'id'        => 'overdue-actions',
            'backUrl'   => route('qms.dashboard'),
        ]);
    }

    /** Controlled documents whose review window has opened. */
    public function documentsDue(Request $request, QmsMetrics $metrics): View
    {
        $this->authorise($request);

        $today = now()->startOfDay();

        $docs = $metrics->documentsDueForReview($today)
            ->orderBy('next_review_date')
            ->paginate(50);

        return view('qms.metric_list', [
            'title'  => 'Documents due for review',
            'hint'   => 'On or before their next review date, within the next 30 days.',
            'period' => 'Due by ' . $today->copy()->addDays(30)->format('d M Y'),
            'count'  => $metrics->documentsDueForReview($today)->count(),
            'rows'   => $docs->map(fn ($d) => [
                'ref'   => $d->code,
                'title' => $d->title,
                'due'   => $d->next_review_date,
                'meta'  => $d->status,
                'url'   => route('controlled_documents.index'),
            ]),
            'paginator' => $docs,
            'id'        => 'documents-due',
            'backUrl'   => route('qms.dashboard'),
        ]);
    }

    /** Audits scheduled within the next 30 days that have not been conducted. */
    public function upcomingAudits(Request $request, QmsMetrics $metrics): View
    {
        $this->authorise($request);

        $today = now()->startOfDay();

        $audits = $metrics->upcomingAudits($today)
            ->orderBy('scheduled_date')
            ->paginate(50);

        return view('qms.metric_list', [
            'title'  => 'Upcoming audits',
            'hint'   => 'Scheduled and not yet conducted.',
            'period' => 'Through ' . $today->copy()->addDays(30)->format('d M Y'),
            'count'  => $metrics->upcomingAudits($today)->count(),
            'rows'   => $audits->map(fn ($a) => [
                'ref'   => $a->ref_no,
                'title' => $a->title,
                'due'   => $a->scheduled_date,
                'meta'  => $a->status,
                'url'   => route('internal_audits.show', $a->id),
            ]),
            'paginator' => $audits,
            'id'        => 'upcoming-audits',
            'backUrl'   => route('qms.dashboard'),
        ]);
    }

    private function authorise(Request $request): void
    {
        abort_unless(
            in_array((string) $request->user()?->user_type, self::ROLES, true),
            403,
            'You are not allowed to view the QMS dashboard.'
        );
    }
}
