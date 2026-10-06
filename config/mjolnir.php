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
];
