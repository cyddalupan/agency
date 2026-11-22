<?php

namespace Tests\Feature\Infrastructure;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DatabaseBackupAndMonitoringTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Where backup:database is pointed for these tests. Never the production
     * storage/backups: that directory holds a git-tracked backup, and the
     * retention pass below deletes whatever exceeds retention.daily.
     */
    private string $testBackupDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testBackupDir = storage_path('framework/testing/backups');
        (new Filesystem)->deleteDirectory($this->testBackupDir);
        (new Filesystem)->makeDirectory($this->testBackupDir, 0755, true);

        config(['backup.path' => $this->testBackupDir]);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->testBackupDir);

        // Drop the override so a later test in this process sees the real
        // default, and so does anything that reads config after us.
        config(['backup.path' => storage_path('backups')]);

        parent::tearDown();
    }

    // ─── Backup Command ─────────────────────────────────────────────────

    #[Test]
    public function backup_database_artisan_command_is_registered(): void
    {
        /** @var \Illuminate\Contracts\Console\Kernel $kernel */
        $kernel = $this->app->make(\Illuminate\Contracts\Console\Kernel::class);
        $command = $kernel->all()['backup:database'] ?? null;

        $this->assertNotNull($command,
            'The backup:database artisan command must be registered in the kernel.'
        );
        $this->assertInstanceOf(\Illuminate\Console\Command::class, $command);
        $this->assertNotEmpty($command->getDescription(),
            'backup:database command must have a non-empty description.'
        );
    }

    #[Test]
    public function backup_database_command_creates_backup_file(): void
    {
        $this->assertDirectoryExists($this->testBackupDir);

        $this->artisan('backup:database')
            ->assertSuccessful();

        $files = glob($this->testBackupDir . '/*.sql');
        $this->assertNotEmpty($files,
            'Running backup:database should produce at least one .sql file in the configured backup path.'
        );

        $this->assertGreaterThan(0, filesize($files[0]),
            'The created backup file must be non-empty.'
        );
    }

    /**
     * The command must write where config says, not to a hardcoded storage
     * subdirectory. This is what makes the test path above meaningful.
     */
    #[Test]
    public function backup_database_writes_to_the_configured_path_only(): void
    {
        $productionDir = storage_path('backups');
        $productionBefore = $this->listing($productionDir);

        $this->artisan('backup:database')->assertSuccessful();

        $this->assertNotEmpty(glob($this->testBackupDir . '/*.sql'),
            'backup:database ignored config(backup.path).'
        );
        $this->assertSame($productionBefore, $this->listing($productionDir),
            'backup:database wrote into storage/backups despite the config override.'
        );
    }

    /**
     * Retention is a second, separate use of the backup directory. If it keeps
     * reading storage_path('backups') it will prune production files from a
     * test run — the exact accident this card exists to stop.
     */
    #[Test]
    public function retention_prunes_the_configured_path_and_leaves_production_alone(): void
    {
        $productionDir = storage_path('backups');
        $productionBefore = $this->listing($productionDir);

        $keep = (int) config('backup.retention.daily');
        $seed = $keep + 3;

        // Seed more than retention keeps, oldest first, so the pruned ones are
        // unambiguous.
        for ($i = 0; $i < $seed; $i++) {
            $file = sprintf('%s/database_daily_2020-01-01_00-00-%02d.sql', $this->testBackupDir, $i);
            file_put_contents($file, "seed {$i}\n");
            touch($file, 1_600_000_000 + $i);
        }

        $this->artisan('backup:database')->assertSuccessful();

        $remaining = glob($this->testBackupDir . '/*.sql');
        $this->assertCount($keep, $remaining,
            "Retention must keep exactly backup.retention.daily ({$keep}) daily files in the configured path."
        );

        // The oldest seeds are the ones gone; the newest ones and the fresh
        // backup survive.
        $this->assertFileDoesNotExist($this->testBackupDir . '/database_daily_2020-01-01_00-00-00.sql');

        $this->assertSame($productionBefore, $this->listing($productionDir),
            'Retention pruned files in storage/backups. enforceRetention() is not reading config(backup.path).'
        );
    }

    /** @return list<string> filenames in $dir, sorted; [] if it does not exist. */
    private function listing(string $dir): array
    {
        if (! is_dir($dir)) {
            return [];
        }

        $names = array_map('basename', glob($dir . '/*') ?: []);
        sort($names);

        return $names;
    }

    // ─── Backup Schedule ────────────────────────────────────────────────

    #[Test]
    public function schedule_has_backup_task_registered(): void
    {
        $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
        $events = $schedule->events();

        $backupEvents = array_filter($events, function ($event) {
            return str_contains($event->command ?? $event->description ?? '', 'backup:database');
        });

        $this->assertNotEmpty($backupEvents,
            'The schedule must contain at least one event for backup:database.'
        );
    }

    #[Test]
    public function backup_schedule_runs_daily(): void
    {
        $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
        $events = $schedule->events();

        foreach ($events as $event) {
            if (str_contains($event->command ?? $event->description ?? '', 'backup:database')) {
                // Laravel express cron expressions as full strings
                // daily() = "0 0 * * *"
                $this->assertStringContainsString('0 0 * * *', $event->expression,
                    'The backup schedule should run daily at midnight (0 0 * * *).'
                );
                return;
            }
        }

        $this->fail('No backup:database event found in schedule to verify frequency.');
    }

    // ─── Backup Retention Configuration ─────────────────────────────────

    #[Test]
    public function backup_config_file_exists(): void
    {
        $this->assertFileExists(config_path('backup.php'),
            'A config/backup.php file must exist for backup configuration.'
        );
    }

    #[Test]
    public function backup_config_has_retention_settings(): void
    {
        $config = config('backup');
        $this->assertIsArray($config, 'config(backup) must return an array.');

        $this->assertArrayHasKey('retention', $config,
            'Backup config must have a retention key.'
        );

        $retention = $config['retention'];
        $this->assertArrayHasKey('daily', $retention,
            'Retention must specify daily backup count to keep.'
        );
        $this->assertArrayHasKey('weekly', $retention,
            'Retention must specify weekly backup count to keep.'
        );
        $this->assertArrayHasKey('monthly', $retention,
            'Retention must specify monthly backup count to keep.'
        );

        $this->assertGreaterThanOrEqual(7, $retention['daily'],
            'Daily retention should keep at least 7 backups.'
        );
        $this->assertGreaterThanOrEqual(4, $retention['weekly'],
            'Weekly retention should keep at least 4 backups.'
        );
        $this->assertGreaterThanOrEqual(3, $retention['monthly'],
            'Monthly retention should keep at least 3 backups.'
        );
    }

    // ─── Failed Job & Queue Monitoring ──────────────────────────────────

    #[Test]
    public function failed_jobs_table_exists_in_database(): void
    {
        $this->assertTrue(
            \Schema::hasTable('failed_jobs'),
            'The failed_jobs table must exist for queue failure monitoring.'
        );
    }

    #[Test]
    public function failed_jobs_monitoring_command_is_registered(): void
    {
        /** @var \Illuminate\Console\Application $artisan */
        $artisan = $this->app->make(\Illuminate\Contracts\Console\Kernel::class);
        $commands = $artisan->all();

        // queue:monitor is a built-in Laravel command, but we check it's available
        $this->assertArrayHasKey('queue:monitor', $commands,
            'The queue:monitor command must be registered.'
        );
    }

    #[Test]
    public function schedule_monitors_failed_jobs(): void
    {
        $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
        $events = $schedule->events();

        $monitorEvents = array_filter($events, function ($event) {
            return str_contains($event->command ?? $event->description ?? '', 'queue:monitor')
                || str_contains($event->command ?? $event->description ?? '', 'failed');
        });

        $this->assertNotEmpty($monitorEvents,
            'The schedule must contain an event monitoring failed jobs or queue health.'
        );
    }

    // ─── Health Check Endpoint ──────────────────────────────────────────

    #[Test]
    public function health_check_route_is_registered(): void
    {
        $routes = \Route::getRoutes();
        $healthRoute = collect($routes)->first(function ($route) {
            return in_array('health', [
                $route->uri(),
                trim($route->uri(), '/'),
            ]);
        });

        $this->assertNotNull($healthRoute,
            'A /health route must be registered.'
        );
    }

    #[Test]
    public function health_endpoint_returns_success(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);

        // Should return JSON with status info
        $content = $response->json();
        $this->assertIsArray($content);

        $this->assertArrayHasKey('status', $content,
            'Health response must include a status key.'
        );
        $this->assertEquals('healthy', $content['status'],
            'Health endpoint status must be "healthy".'
        );
        $this->assertArrayHasKey('timestamp', $content,
            'Health response must include a timestamp.'
        );
    }

    // ─── Error Monitoring Setup ─────────────────────────────────────────

    #[Test]
    public function error_monitoring_service_provider_is_registered(): void
    {
        $providers = config('app.providers', []);

        // Check for a monitoring/observability provider
        $hasMonitoringProvider = collect($providers)->contains(function ($provider) {
            return str_contains($provider, 'Sentry')
                || str_contains($provider, 'ReportingServiceProvider');
        });

        $this->assertTrue($hasMonitoringProvider,
            'An error monitoring service provider (e.g., Sentry or custom ReportingServiceProvider) must be registered.'
        );
    }

    #[Test]
    public function error_monitoring_dsn_is_configured(): void
    {
        $sentryDsn = config('sentry.dsn');
        $reportingDsn = config('reporting.dsn');
        $hasDsn = !empty($sentryDsn) || !empty($reportingDsn);

        $this->assertTrue($hasDsn,
            'An error monitoring DSN must be configured (e.g., Sentry DSN).'
        );
    }
}
