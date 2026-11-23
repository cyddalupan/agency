<?php

namespace App\Support\Db;

/**
 * MySQL expression for the expense-request live-reference unique key.
 *
 * Card 6b038452. MySQL 8 has no partial indexes, so "one live row per
 * (agency_id, reference_no)" is expressed as a functional index whose second
 * key part is this expression: the reference for a live row, NULL for a
 * soft-deleted one. MySQL never treats two NULLs in a UNIQUE index as a
 * conflict, so soft-deleted history is unconstrained while live duplicates are
 * refused.
 *
 * Single source of truth: the migration and the MySQL DDL test both use this,
 * so a test cannot pass on a different expression than the one that ships.
 *
 * Do NOT replace this with a plain unique (agency_id, reference_no): it fails
 * with ERROR 1062 on production data. Do NOT add deleted_at as a third key
 * column: two live rows both have deleted_at NULL, which MySQL treats as
 * distinct, and nothing is constrained at all. Both were tested on 8.0.46,
 * 2026-11-23.
 */
final class ExpenseRequestLiveReferenceKey
{
    public const MYSQL_EXPRESSION = '(IF(deleted_at IS NULL, reference_no, NULL))';

    public static function mysqlKeyParts(string $table = 'expense_requests', string $columns = 'agency_id'): string
    {
        return $columns . ', ' . self::MYSQL_EXPRESSION;
    }
}
