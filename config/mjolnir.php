<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Privileged ("sees everything") accounts
    |--------------------------------------------------------------------------
    |
    | Client request (2026-09-28): only MAE, EVELYN and ANGEL should see the
    | full picture — every Receivable/Expense record (not just their own),
    | the Backout/Cancelled/Repat folder, the Statistics report, the full
    | Reports tab, and be allowed to create Agent Deductions.
    |
    | Everyone else is scoped to "own records only" and loses the gated items.
    |
    | Matched case-insensitively against User::name (trimmed) and the local
    | part of User::email. Overridable via env MJOLNIR_PRIVILEGED_ACCOUNTS
    | (comma-separated), e.g. "MAE,EVELYN,ANGEL".
    */
    'privileged_accounts' => array_values(array_filter(array_map(
        fn ($v) => strtoupper(trim($v)),
        explode(',', (string) env('MJOLNIR_PRIVILEGED_ACCOUNTS', 'MAE,EVELYN,ANGEL'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Branch user restrictions (TEMPORARY kill-switch)
    |--------------------------------------------------------------------------
    |
    | When TRUE (default) branch accounts keep the normal Branch feature:
    | they are auto-scoped to their own branch, get 403 on other branches'
    | records, have locked branch dropdowns, and a trimmed sidebar.
    |
    | When FALSE the branch restrictions are lifted GLOBALLY: branch accounts
    | can see / edit records for ANY branch (and pick any branch on forms).
    | Module access per account type is unchanged. Set via env
    | `BRANCH_RESTRICTIONS=false` and re-run `php artisan config:clear`.
    |
    | (Mjolnir/Cyd 2026-10-14: "temporarily disable the branch user
    | restrictions" so branch users can fix wrong data across branches.)
    */
    'branch_restrictions' => filter_var(
        env('BRANCH_RESTRICTIONS', true),
        FILTER_VALIDATE_BOOLEAN
    ),
];
