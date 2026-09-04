<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Backup Retention
    |--------------------------------------------------------------------------
    |
    | How many backups of each type to keep before pruning old files.
    | Retention is enforced by the backup:database command after each run.
    |
    */

    'retention' => [
        'daily'   => 7,
        'weekly'  => 4,
        'monthly' => 3,
    ],

];
