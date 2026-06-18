<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DatabaseBackup extends Command
{
    protected $signature = 'app:database-backup {--type=daily : daily|weekly|monthly}';
    protected $description = 'Create a database backup with retention management';

    private string $backupDisk = 'local';
    private string $backupDir = 'backups';

    private array $retention = [
        'daily'   => 7,
        'weekly'  => 4,
        'monthly' => 3,
    ];

    public function handle(): int
    {
        $type = $this->option('type');
        if (!in_array($type, ['daily', 'weekly', 'monthly'])) {
            $this->error("Invalid type: {$type}. Use daily, weekly, or monthly.");
            return Command::FAILURE;
        }

        $dbName     = config('database.connections.mysql.database');
        $dbUser     = config('database.connections.mysql.username');
        $dbPassword = config('database.connections.mysql.password');
        $dbHost     = config('database.connections.mysql.host');

        $now    = Carbon::now('Asia/Manila');
        $stamp  = $now->format('Y-m-d_H-i-s');

        // Directory: backups/daily/YYYY/MM/ for daily, backups/weekly/ for weekly, backups/monthly/ for monthly
        $typeDir = match($type) {
            'daily'   => "{$this->backupDir}/{$type}/{$now->year}/{$now->format('m')}",
            'weekly'  => "{$this->backupDir}/{$type}/{$now->year}/week-{$now->isoWeek()}",
            'monthly' => "{$this->backupDir}/{$type}/{$now->year}",
        };

        $filename = "{$dbName}_{$type}_{$stamp}.sql.gz";
        $tmpPath  = storage_path("app/{$filename}");
        $fullPath = storage_path("app/{$typeDir}/{$filename}");

        // Create directory structure
        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        // Dump database, gzip it
        $passwordArg = escapeshellarg($dbPassword);
        $cmd = sprintf(
            'MYSQL_PWD=%s mysqldump --host=%s --user=%s --single-transaction --routines --triggers --events %s 2>/dev/null | gzip > %s',
            $passwordArg,
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbName),
            escapeshellarg($tmpPath)
        );

        $this->info("Running: mysqldump for {$dbName} ({$type})...");
        $startTime = microtime(true);
        exec($cmd, $output, $exitCode);
        $elapsed = round(microtime(true) - $startTime, 2);

        if ($exitCode !== 0 || !file_exists($tmpPath) || filesize($tmpPath) < 100) {
            $this->error("Backup failed (exit code: {$exitCode})");
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
            logger()->error('Database backup command failed', [
                'db' => $dbName,
                'type' => $type,
                'exit_code' => $exitCode,
            ]);
            return Command::FAILURE;
        }

        // Move to final location
        rename($tmpPath, $fullPath);

        $size = $this->humanSize(filesize($fullPath));
        $this->info("✓ Backup created: {$fullPath} ({$size}) in {$elapsed}s");

        // Log success
        logger()->info('Database backup created', [
            'db' => $dbName,
            'type' => $type,
            'file' => $filename,
            'size' => $size,
            'duration' => "{$elapsed}s",
        ]);

        // Enforce retention
        $this->enforceRetention($type);

        // Create latest symlink
        $this->updateLatestSymlink($fullPath, $dbName, $type);

        return Command::SUCCESS;
    }

    private function enforceRetention(string $type): void
    {
        $keep = $this->retention[$type];
        $dir  = storage_path("app/{$this->backupDir}/{$type}");
        if (!is_dir($dir)) {
            return;
        }

        // Collect all backup files, sorted by modification time (newest first)
        $files = $this->rGlob("{$dir}/*.sql.gz");
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        $deleted = 0;
        foreach (array_slice($files, $keep) as $oldFile) {
            if (is_file($oldFile)) {
                unlink($oldFile);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            // Clean empty dirs
            $this->cleanEmptyDirs("{$dir}");
            $this->info("Retention: removed {$deleted} old backup(s), keeping {$keep} {$type}");
        }
    }

    private function updateLatestSymlink(string $file, string $dbName, string $type): void
    {
        $linkPath = storage_path("app/{$this->backupDir}/{$dbName}_{$type}_latest.sql.gz");
        if (file_exists($linkPath)) {
            unlink($linkPath);
        }
        symlink($file, $linkPath);
    }

    private function rGlob(string $pattern): array
    {
        $files = glob($pattern);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR) as $dir) {
            $files = array_merge($files, $this->rGlob("{$dir}/" . basename($pattern)));
        }
        return $files;
    }

    private function cleanEmptyDirs(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $children = array_diff(scandir($dir), ['.', '..']);
        if (empty($children)) {
            rmdir($dir);
            // Also clean parent if empty
            $parent = dirname($dir);
            if ($parent !== $dir) {
                $this->cleanEmptyDirs($parent);
            }
        }
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
