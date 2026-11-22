<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Backup Directory
    |--------------------------------------------------------------------------
    |
    | Where backup:database writes dump files and where its retention pass
    | prunes them. Both paths read this key. Default stays storage/backups;
    | the test suite points it elsewhere so it never deletes tracked files.
    |
    */

    'path' => env('BACKUP_PATH', storage_path('backups')),

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
