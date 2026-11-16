<?php

namespace App\Http\Controllers;

use App\Services\ComplianceDeadlineFeed;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * QMS-19 (card 6afa0db0a24da820db1980db): the compliance calendar.
 *
 * Deliberately thin and read-only: it asks the feed for the grouped deadlines
 * and renders them. There is no write path and no second copy of the bucketing
 * rule — that lives in ComplianceDeadlineFeed and is tested there.
 */
class ComplianceCalendarController extends Controller
{
    private const ROLES = ['super_admin', 'admin', 'qms_manager', 'internal_auditor'];

    public function index(Request $request, ComplianceDeadlineFeed $feed): View
    {
        abort_unless(
            in_array((string) $request->user()?->user_type, self::ROLES, true),
            403,
            'You are not allowed to view the compliance calendar.'
        );

        // $source is a display filter only; the counts are always the full
        // picture, so filtering does not make an overdue item look gone.
        $source = $request->query('source');
        $grouped = $feed->grouped();
        $counts = array_map(fn ($c) => $c->count(), $grouped);

        if (is_string($source) && $source !== '') {
            $grouped = array_map(
                fn ($items) => $items->where('source', $source)->values(),
                $grouped
            );
        }

        $sources = collect($feed->all())
            ->map(fn (array $i) => ['key' => $i['source'], 'label' => $i['source_label']])
            ->unique('key')
            ->values();

        return view('compliance_calendar.index', [
            'grouped' => $grouped,
            'counts'  => $counts,
            'total'   => array_sum($counts),
            'overdue' => $counts[ComplianceDeadlineFeed::BUCKET_OVERDUE] ?? 0,
            'buckets' => ComplianceDeadlineFeed::BUCKETS,
            'badges'  => ComplianceDeadlineFeed::BUCKET_BADGES,
            'sources' => $sources,
            'source'  => is_string($source) ? $source : null,
        ]);
    }
}
