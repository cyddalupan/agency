<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ControlledDocument;
use App\Models\CorrectiveAction;
use App\Models\InternalAudit;
use App\Models\NonConformity;
use App\Models\Risk;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * QMS-19 (card 6afa0db0a24da820db1980db): the unified deadline feed.
 *
 * Spec §16 wants one calendar of everything with a due date. There is
 * deliberately no `deadlines` table: each source already knows its own date and
 * its own idea of "still live", so this class reads them at query time through
 * a small adapter per source. If this ever becomes slow the answer is a
 * materialised table behind this same interface, not a schema change made in
 * advance of the evidence.
 *
 * Every adapter returns the same shape:
 *   source, source_label, ref, title, due (Carbon), bucket, url, severity
 *
 * "Still live" is per source and is the subtle part: a closed risk has a review
 * date, but chasing it would be noise. Each adapter decides what counts as
 * outstanding, and says so in a comment.
 */
class ComplianceDeadlineFeed
{
    public const BUCKET_OVERDUE    = 'overdue';
    public const BUCKET_THIS_WEEK  = 'this_week';
    public const BUCKET_THIS_MONTH = 'this_month';
    public const BUCKET_LATER      = 'later';

    public const BUCKETS = [
        self::BUCKET_OVERDUE    => 'Overdue',
        self::BUCKET_THIS_WEEK  => 'This week',
        self::BUCKET_THIS_MONTH => 'This month',
        self::BUCKET_LATER      => 'Later',
    ];

    public const BUCKET_BADGES = [
        self::BUCKET_OVERDUE    => 'badge-error',
        self::BUCKET_THIS_WEEK  => 'badge-warning',
        self::BUCKET_THIS_MONTH => 'badge-info',
        self::BUCKET_LATER      => 'badge-ghost',
    ];

    /**
     * Which bucket a due date falls in, relative to $now.
     *
     * Overdue is by day, not by second: something due earlier today is due
     * today, not late. That is how a person reads a calendar.
     */
    public function bucketFor(Carbon $due, ?Carbon $now = null): string
    {
        $now = ($now ?? now())->copy()->startOfDay();
        $dueDay = $due->copy()->startOfDay();

        if ($dueDay->lt($now)) {
            return self::BUCKET_OVERDUE;
        }

        // "This week" runs to the end of the current week (Sunday).
        $endOfWeek = $now->copy()->endOfWeek();

        if ($dueDay->lte($endOfWeek)) {
            return self::BUCKET_THIS_WEEK;
        }

        if ($dueDay->lte($now->copy()->endOfMonth())) {
            return self::BUCKET_THIS_MONTH;
        }

        return self::BUCKET_LATER;
    }

    /**
     * Every live deadline, sorted: overdue first (most overdue at the top),
     * then by date ascending.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function all(?Carbon $now = null): Collection
    {
        $now = $now ?? now();

        $items = collect()
            ->merge($this->fromControlledDocuments())
            ->merge($this->fromRisks())
            ->merge($this->fromComplaints())
            ->merge($this->fromNonConformities())
            ->merge($this->fromCorrectiveActions())
            ->merge($this->fromInternalAudits());

        return $items
            ->map(function (array $item) use ($now) {
                $item['bucket'] = $this->bucketFor($item['due'], $now);
                $item['days'] = (int) now()->startOfDay()->diffInDays($item['due']->copy()->startOfDay(), false);

                return $item;
            })
            ->sort(function (array $a, array $b) {
                // Overdue sorts above everything, and within overdue the oldest
                // (most overdue) comes first.
                $aOver = $a['bucket'] === self::BUCKET_OVERDUE;
                $bOver = $b['bucket'] === self::BUCKET_OVERDUE;

                if ($aOver !== $bOver) {
                    return $aOver ? -1 : 1;
                }

                return $a['due'] <=> $b['due'];
            })
            ->values();
    }

    /**
     * @return array<string, Collection<int, array<string, mixed>>>
     */
    public function grouped(?Carbon $now = null): array
    {
        $all = $this->all($now);

        $out = [];
        foreach (array_keys(self::BUCKETS) as $bucket) {
            $out[$bucket] = $all->where('bucket', $bucket)->values();
        }

        return $out;
    }

    /**
     * @return array<string, int>
     */
    public function counts(?Carbon $now = null): array
    {
        $grouped = $this->grouped($now);

        return array_map(fn (Collection $c) => $c->count(), $grouped);
    }

    // ---------------------------------------------------------------------
    // Adapters. One per source, each returning the same array shape.
    // ---------------------------------------------------------------------

    /** Approved or in-review documents still owe a periodic review. */
    private function fromControlledDocuments(): Collection
    {
        return ControlledDocument::query()
            ->whereNotNull('next_review_date')
            ->whereNotIn('status', [
                ControlledDocument::STATUS_SUPERSEDED,
                ControlledDocument::STATUS_OBSOLETE,
            ])
            ->get()
            ->map(fn (ControlledDocument $d) => $this->item(
                source: 'controlled_document',
                sourceLabel: 'Document review',
                ref: $d->code,
                title: $d->title,
                due: $d->next_review_date,
                url: $this->link('controlled_documents.show', $d->id, 'controlled_documents.index'),
                severity: 'info',
            ));
    }

    /**
     * A closed risk is not chased. Both its target (treat it) and its review
     * (look again) date are deadlines, and they are genuinely different
     * obligations, so they are emitted separately and labelled as such.
     */
    private function fromRisks(): Collection
    {
        $risks = Risk::query()
            ->where('status', '!=', Risk::STATUS_CLOSED)
            ->get();

        $items = collect();

        foreach ($risks as $risk) {
            if ($risk->target_date) {
                $items->push($this->item(
                    source: 'risk',
                    sourceLabel: 'Risk target',
                    ref: 'RISK-' . $risk->id,
                    title: $risk->title,
                    due: $risk->target_date,
                    url: $this->link('risks.show', $risk->id, 'risks.index'),
                    severity: $risk->risk_level ?? 'info',
                ));
            }

            if ($risk->review_date) {
                $items->push($this->item(
                    source: 'risk',
                    sourceLabel: 'Risk review',
                    ref: 'RISK-' . $risk->id,
                    title: $risk->title,
                    due: $risk->review_date,
                    url: $this->link('risks.show', $risk->id, 'risks.index'),
                    severity: $risk->risk_level ?? 'info',
                ));
            }
        }

        return $items;
    }

    /**
     * A resolved or closed complaint is done. While it is live there are two
     * separate promises: acknowledge by X, resolve by Y.
     */
    private function fromComplaints(): Collection
    {
        $complaints = Complaint::query()
            ->whereNotIn('status', [
                Complaint::STATUS_RESOLVED,
                Complaint::STATUS_CLOSED,
            ])
            ->get();

        $items = collect();

        foreach ($complaints as $c) {
            if ($c->target_ack_date) {
                $items->push($this->item(
                    source: 'complaint',
                    sourceLabel: 'Acknowledge complaint',
                    ref: $c->ref_no ?? ('CMP-' . $c->id),
                    title: $c->category ?: $c->description ?: 'Complaint',
                    due: $c->target_ack_date,
                    url: route('complaints.show', $c->id),
                    severity: 'major',
                ));
            }

            if ($c->target_resolve_date) {
                $items->push($this->item(
                    source: 'complaint',
                    sourceLabel: 'Resolve complaint',
                    ref: $c->ref_no ?? ('CMP-' . $c->id),
                    title: $c->category ?: $c->description ?: 'Complaint',
                    due: $c->target_resolve_date,
                    url: route('complaints.show', $c->id),
                    severity: 'major',
                ));
            }
        }

        return $items;
    }

    /** An open non-conformity owes its due date; a closed one does not. */
    private function fromNonConformities(): Collection
    {
        return NonConformity::query()
            ->whereNotNull('due_date')
            ->where('status', '!=', NonConformity::STATUS_CLOSED)
            ->get()
            ->map(fn (NonConformity $nc) => $this->item(
                source: 'non_conformity',
                sourceLabel: 'Non-conformity due',
                ref: $nc->ref_no,
                title: $nc->description,
                due: $nc->due_date,
                url: route('non_conformities.show', $nc),
                severity: $nc->severity,
            ));
    }

    /** A corrective action owes its due date until it is marked complete. */
    private function fromCorrectiveActions(): Collection
    {
        return CorrectiveAction::query()
            ->whereNotNull('due_date')
            ->whereNull('completed_date')
            ->get()
            ->map(fn (CorrectiveAction $ca) => $this->item(
                source: 'corrective_action',
                sourceLabel: 'Corrective action due',
                ref: $ca->non_conformity?->ref_no ?? ('CA-' . $ca->id),
                title: $ca->action_text,
                due: $ca->due_date,
                url: route('non_conformities.show', $ca->non_conformity_id),
                severity: 'major',
            ));
    }

    /** A scheduled audit that has not been conducted yet is upcoming. */
    private function fromInternalAudits(): Collection
    {
        return InternalAudit::query()
            ->whereNotNull('scheduled_date')
            ->whereNull('conducted_date')
            ->where('status', '!=', InternalAudit::STATUS_CLOSED)
            ->get()
            ->map(fn (InternalAudit $a) => $this->item(
                source: 'internal_audit',
                sourceLabel: 'Audit scheduled',
                ref: $a->ref_no,
                title: $a->title,
                due: $a->scheduled_date,
                url: route('internal_audits.show', $a->id),
                severity: 'info',
            ));
    }

    // ---------------------------------------------------------------------

    /**
     * Build a link, falling back to the module's own screen when a named route
     * does not exist. A calendar entry with a link to the list is still
     * useful; one that throws `RouteNotFoundException` takes the page with it.
     */
    private function link(string $route, mixed $params, string $fallback): string
    {
        try {
            return route($route, $params);
        } catch (\Throwable) {
            return route($fallback);
        }
    }

    private function item(
        string $source,
        string $sourceLabel,
        string $ref,
        string $title,
        $due,
        string $url,
        string $severity,
    ): array {
        return [
            'source'       => $source,
            'source_label' => $sourceLabel,
            'ref'          => $ref,
            'title'        => trim(preg_replace('/\s+/', ' ', (string) $title)),
            'due'          => $due instanceof Carbon ? $due->copy() : Carbon::parse($due),
            'url'          => $url,
            'severity'     => $severity,
            'bucket'       => null,
            'days'         => null,
        ];
    }
}
