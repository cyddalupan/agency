<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Normalize and verify storage permissions.
 *
 * Prevents recurrence of: storage/framework/cache/data entries owned by
 * root (from root CLI/artisan runs) blocking the www-data web process from
 * writing cache/sessions/views -> HTTP 500 (file_put_contents: Failed to
 * open stream). Run `php artisan storage:permissions --fix` after any
 * root-owned artisan/test invocation.
 */
class StoragePermissions extends Command
{
    protected $signature = 'storage:permissions
        {--fix : chown storage to the web user (www-data)}';

    protected $description = 'Verify (and optionally fix) storage writability for the web process.';

    public function handle(): int
    {
        $storage = storage_path();
        $required = [
            'framework/cache',
            'framework/cache/data',
            'framework/sessions',
            'framework/views',
            'logs',
        ];

        $allOk = true;
        foreach ($required as $sub) {
            if (! $this->probeWritable($storage . '/' . $sub)) {
                $allOk = false;
            }
        }

        // Probe a fresh nested hash dir under cache/data (the FileStore path
        // that broke when stale root-owned xx/yy dirs blocked www-data).
        if (! $this->probeNested($storage . '/framework/cache/data')) {
            $allOk = false;
        }

        if ($this->option('fix') && ! $allOk) {
            $this->info('Running ownership fix: chown -R www-data:www-data storage ...');
            exec('chown -R www-data:www-data ' . escapeshellarg($storage), $out, $code);
            $this->info($code === 0 ? 'chown completed.' : 'chown failed (exit ' . $code . ').');

            $allOk = true;
            foreach ($required as $sub) {
                if (! $this->probeWritable($storage . '/' . $sub)) {
                    $allOk = false;
                }
            }
            if (! $this->probeNested($storage . '/framework/cache/data')) {
                $allOk = false;
            }
        }

        $this->newLine();
        $this->info($allOk ? 'Storage permissions OK.' : 'Storage permissions BROKEN. Run with --fix.');
        return $allOk ? self::SUCCESS : self::FAILURE;
    }

    private function probeWritable(string $dir): bool
    {
        $probe = $dir . '/.storage-writable-probe-' . uniqid();
        $ok = @file_put_contents($probe, 'ok') !== false;
        if ($ok) {
            @unlink($probe);
        }
        $short = str_replace(storage_path() . '/', '', $dir);
        $this->line(sprintf('  [%s] %s', $ok ? 'OK' : 'WRITE-FAIL', $short));
        return $ok;
    }

    private function probeNested(string $dataDir): bool
    {
        $u1 = substr(hash('sha256', uniqid('', true)), 0, 2);
        $u2 = substr(hash('sha256', uniqid('', true) . '-b'), 0, 2);
        $nested = $dataDir . '/' . $u1 . '/' . $u2;
        $ok = @mkdir($nested, 0755, true) && @file_put_contents($nested . '/.probe', 'ok') !== false;
        $this->line(sprintf('  [%s] framework/cache/data/<nested hash dir>', $ok ? 'OK' : 'WRITE-FAIL'));
        @unlink($nested . '/.probe');
        @rmdir($nested);
        @rmdir($dataDir . '/' . $u1);
        return $ok;
    }
}
