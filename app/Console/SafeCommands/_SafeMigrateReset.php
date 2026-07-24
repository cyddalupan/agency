<?php

namespace App\Console\SafeCommands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class _SafeMigrateReset extends Command
{
    protected $signature = 'migrate:reset
        {--database= : The database connection to use}
        {--force : Force the operation to run when in production}
        {--pretend : Dump the SQL queries instead of running them}
        {--step : Force the migrations to be run so they can be rolled back individually}
    ';

    protected $description = '🚫 Disabled for safety';

    public function handle(): int
    {
        if (app()->environment('testing')) {
            Artisan::call('migrate:reset', $this->option());
            return 0;
        }

        $this->error('🚫 migrate:reset is disabled on this server for safety.');
        $this->line('   Restore from a backup instead if you need to reset the database.');
        return 0;
    }
}
