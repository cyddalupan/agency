<?php

return [
    // One USD->PHP rate for every screen (card 6b05c0bc, Option A, Bitoy
    // 2026-11-25). All reports read this value; nothing fetches a rate at
    // render time. It is updated once a day by `php artisan fx:refresh-rate`,
    // which logs old -> new. A stored peso amount is never re-derived from
    // this value: agent_deductions stamps fx_rate at creation (see the
    // 2026_11_26_000001 migration).
    //
    // When EXPENSE_USD_TO_PHP is absent the default below applies. Do not
    // lower this to 1.0 as a fallback: 1.0 renders $470 as P470.
    'usd_to_php' => env('EXPENSE_USD_TO_PHP', 63.769743),
];
