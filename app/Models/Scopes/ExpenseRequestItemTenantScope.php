<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Card 6b008b8e7c2a220f1cd6d2e1 — tenant isolation for `expense_request_items`.
 *
 * The table has no `agency_id` and is not getting one: its tenant is its
 * parent request's tenant, and that stays the single source of truth (see
 * ExpenseRequestItem::resolveAuditAgencyId()). What changes is that the
 * inheritance is now ENFORCED on every query instead of being a comment.
 *
 * "Which tenant is bound" is not answered here. It is TenantScope::tenantFilter()
 * — the same resolution the shipped, QA-passed scope uses (super_admin, then
 * Tenant::bypass(), then container, then session, then console, then
 * tenant_unbound_allowed, then throw). Same exceptions, same order. This class
 * only adds a `where` to the given builder, so there is exactly one place that
 * decides who the tenant is.
 *
 * Why a correlated `IN (SELECT id ...)` and not `whereHas('expenseRequest')`:
 *   - only the request's `id` is selected from the subquery, so a global scope
 *     on ExpenseRequest cannot make the select list ambiguous;
 *   - the one raw query-builder call site that needed it (GeneralLedger's
 *     aliased join) builds the same predicate with constrain() below, so the
 *     Eloquent scope and that join are one statement, not two things that can
 *     drift;
 *   - no join means the outer query's aliases (the controllers alias
 *     expense_requests as `er` / `dup_items` / `agg_items`) cannot collide.
 */
class ExpenseRequestItemTenantScope implements Scope
{
    /** Column of the outer (items) query the subquery correlates on. */
    private const ITEMS_FK = 'expense_request_id';

    public function apply(Builder $builder, Model $model): void
    {
        $agencyId = TenantScope::tenantFilter($model->getTable());

        if ($agencyId !== null) {
            $this->constrain($builder->getQuery(), $model->getTable(), $agencyId);
        }
    }

    /**
     * The same predicate for a query-builder statement (DB::table, or an
     * Eloquent builder's toBase() around a join). $itemsTable is the table or
     * alias the items side is known by in THAT statement.
     */
    public static function constrain(QueryBuilder $query, string $itemsTable, ?int $agencyId): QueryBuilder
    {
        if ($agencyId === null) {
            return $query;
        }

        return $query->whereIn(
            $itemsTable . '.' . self::ITEMS_FK,
            static function (QueryBuilder $sub) use ($agencyId) {
                $sub->select('id')
                    ->from('expense_requests')
                    ->where('agency_id', $agencyId)
                    ->whereNull('deleted_at');
            }
        );
    }

    /**
     * The `deleted_at` in the subquery is not decoration. ExpenseRequest is
     * soft-deleted while `expense_request_items` rows are not, and 0 rows are
     * orphaned, so a removed request keeps its items — which is why the two
     * financial readers (GeneralLedger accountTotals, ExpenseRequest current
     * totals) already say `whereNull('er.deleted_at')` themselves. Items of a
     * deleted request have no tenant that anyone can see (the parent is hidden
     * from every tenant-bound read), so they are not returned as if they
     * belonged to the tenant.
     *
     * A consequence, measured on the live data: the 6 lines belonging to a
     * soft-deleted request are not listed (they were already absent from both
     * money readers, so this fixes 6 lines appearing with no request behind
     * them, not the totals), and a bulk status update through such a request
     * now reports "No matching transactions" instead of moving 6 lines. That
     * is the same stop-the-write this project asked for elsewhere: the request
     * header is not writable once deleted, so its lines are not either.
     */
}
