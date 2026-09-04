<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Error Reporting / Monitoring
    |--------------------------------------------------------------------------
    |
    | DSN for the error monitoring service (e.g. Sentry). When set, the
    | ReportingServiceProvider registers the reporting client with Laravel.
    |
    */

    'dsn' => env('REPORTING_DSN', env('SENTRY_LARAVEL_DSN', 'https://reporting.fixitautoservices.com/ingest')),

];
