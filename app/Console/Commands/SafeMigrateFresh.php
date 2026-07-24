<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SafeMigrateFresh extends Command
{
    protected $signature = 'migrate:fresh
        {--database= : The database connection to use}
        {--drop-views : Drop all tables and views}
        {--drop-types : Drop all tables and types}
        {--force : Force the operation to run when in production}
        {--seed : Indicates if the seed task should be re-run}
        {--seeder= : The class name of the root seeder}
        {--step : Force the migrations to be run so they can be rolled back individually}
        {--graceful : Return a successful exit code even if the migration fails}
    ';

    protected $description = '🚫 Disabled for safety';

    public function handle(): int
    {
        $this->error('🚫 migrate:fresh is disabled on this server for safety.');
        $this->line('   Restore from a backup instead if you need to reset the database.');
        return 0;
    }
}
