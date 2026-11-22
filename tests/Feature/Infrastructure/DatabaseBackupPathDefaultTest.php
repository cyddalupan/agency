<?php

namespace Tests\Feature\Infrastructure;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pins the production backup directory.
 *
 * storage/backups is where the scheduler writes and where the git-tracked
 * 2026-09-04 backup lives. DatabaseBackupAndMonitoringTest points backup.path
 * at a test-only directory, so the default has to be asserted here, in a test
 * that does not install that override.
 */
class DatabaseBackupPathDefaultTest extends TestCase
{
    #[Test]
    public function backup_path_defaults_to_storage_backups(): void
    {
        $this->assertSame(
            storage_path('backups'),
            config('backup.path'),
            'config(backup.path) must default to storage_path(backups).'
        );
    }

    /**
     * Reads the config file itself, so a change to the default is caught even
     * if something upstream also overrides config('backup.path') at boot.
     */
    #[Test]
    public function config_file_default_is_storage_backups(): void
    {
        $this->assertSame(
            storage_path('backups'),
            $this->configuredPathWithNoEnvOverride(),
            'config/backup.php must resolve backup.path to storage_path(backups) when BACKUP_PATH is unset.'
        );
    }

    /**
     * BACKUP_PATH is the documented escape hatch; prove it actually moves the
     * path rather than being ignored.
     */
    #[Test]
    public function backup_path_env_override_is_honoured(): void
    {
        $previousEnv = $_ENV['BACKUP_PATH'] ?? null;
        $previousServer = $_SERVER['BACKUP_PATH'] ?? null;

        $_ENV['BACKUP_PATH'] = '/tmp/landas-backup-env-test';
        $_SERVER['BACKUP_PATH'] = '/tmp/landas-backup-env-test';

        try {
            $config = require config_path('backup.php');
        } finally {
            $this->restoreEnv($previousEnv, $previousServer);
        }

        $this->assertSame('/tmp/landas-backup-env-test', $config['path'],
            'BACKUP_PATH must override the backup path.'
        );
    }

    private function configuredPathWithNoEnvOverride(): string
    {
        $previousEnv = $_ENV['BACKUP_PATH'] ?? null;
        $previousServer = $_SERVER['BACKUP_PATH'] ?? null;

        unset($_ENV['BACKUP_PATH'], $_SERVER['BACKUP_PATH']);

        try {
            $config = require config_path('backup.php');
        } finally {
            $this->restoreEnv($previousEnv, $previousServer);
        }

        return (string) $config['path'];
    }

    private function restoreEnv(?string $env, ?string $server): void
    {
        if ($env === null) {
            unset($_ENV['BACKUP_PATH']);
        } else {
            $_ENV['BACKUP_PATH'] = $env;
        }

        if ($server === null) {
            unset($_SERVER['BACKUP_PATH']);
        } else {
            $_SERVER['BACKUP_PATH'] = $server;
        }
    }
}
