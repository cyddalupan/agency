<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SafeDbWipe extends Command
{
    protected $signature = 'db:wipe
        {--database= : The database connection to use}
        {--drop-views : Drop all tables and views}
        {--drop-types : Drop all tables and types}
        {--force : Force the operation to run when in production}
    ';

    protected $description = '🚫 Disabled for safety';

    public function handle(): int
    {
        $this->error('🚫 db:wipe is disabled on this server for safety.');
        $this->line('   Restore from a backup instead if you need to reset the database.');
        return 0;
    }
}
