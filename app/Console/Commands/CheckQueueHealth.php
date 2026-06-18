<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckQueueHealth extends Command
{
    protected $signature = 'app:check-queue-health';
    protected $description = 'Check job queue health and report issues';

    public function handle(): int
    {
        $this->info('Checking queue health...');

        // Check failed_jobs table
        $failedCount = DB::table('failed_jobs')->count();
        if ($failedCount > 0) {
            $this->warn("⚠ {$failedCount} failed job(s) found");

            $recent = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subHours(24))
                ->count();
            if ($recent > 0) {
                $this->error("{$recent} failed job(s) in the last 24 hours!");
            }

            logger()->warning('Queue health: failed jobs detected', [
                'total' => $failedCount,
                'last_24h' => $recent,
            ]);
        } else {
            $this->info('✓ No failed jobs');
        }

        return Command::SUCCESS;
    }
}
