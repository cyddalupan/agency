<?php

namespace App\Console\SafeCommands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class _SafeMigrateRefresh extends Command
{
    protected $signature = 'migrate:refresh
        {--database= : The database connection to use}
        {--force : Force the operation to run when in production}
        {--seed : Indicates if the seed task should be re-run}
        {--seeder= : The class name of the root seeder}
        {--step : Force the migrations to be run so they can be rolled back individually}
    ';

    protected $description = '🚫 Disabled for safety';

    public function handle(): int
    {
        if (app()->environment('testing')) {
            Artisan::call('migrate:refresh', $this->option());
            return 0;
        }

        $this->error('🚫 migrate:refresh is disabled on this server for safety.');
        $this->line('   Restore from a backup instead if you need to reset the database.');
        return 0;
    }
}
