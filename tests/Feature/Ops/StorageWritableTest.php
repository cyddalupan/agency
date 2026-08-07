<?php

namespace Tests\Feature\Ops;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Storage writability regression guard (LANDAS infra issue 2026-08-04).
 *
 * A previous breakage: mixed ownership under storage/framework/cache/data
 * (root-owned hash dirs created by root CLI runs) made Apache's www-data
 * process unable to write cache entries (file_put_contents: Failed to open
 * stream). This suite guards that the default cache store and the storage
 * directories the web process needs are actually writable at runtime.
 */
class StorageWritableTest extends TestCase
{
    #[Test]
    public function file_store_can_put_and_get_a_value(): void
    {
        $key = 'storage-writable-test-' . uniqid();
        $value = ['ok' => true, 'now' => now()->toIso8601String()];

        Cache::put($key, $value, 60);
        $read = Cache::get($key);

        $this->assertSame($value, $read);
        Cache::forget($key);
    }

    #[Test]
    public function storage_framework_directories_are_writable_by_the_web_user(): void
    {
        $files = new Filesystem;
        $directories = [
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ];

        foreach ($directories as $dir) {
            $this->assertDirectoryExists($dir, "Missing storage dir: {$dir}");
            $probe = $dir . '/.storage-writable-probe-' . uniqid();
            $written = @file_put_contents($probe, 'ok');
            if ($written === false) {
                $this->fail("Not writable by current process: {$dir}");
            }
            @unlink($probe);
            $this->assertTrue(true, "Writable: {$dir}");
        }
    }

    #[Test]
    public function file_store_can_create_a_nested_hash_directory(): void
    {
        // Laravel's FileStore writes to a nested hash path cache/data/xx/yy/hash.
        // If a stale root-owned xx/yy dir exists, www-data cannot mkdir/write there
        // -> file_put_contents: Failed to open stream (the gulf 500 regression).
        $dataDir = storage_path('framework/cache/data');
        $this->assertDirectoryExists($dataDir);

        $u1 = substr(hash('sha256', uniqid('', true)), 0, 2);
        $u2 = substr(hash('sha256', uniqid('', true) . '-b'), 0, 2);
        $nested = $dataDir . '/' . $u1 . '/' . $u2;
        $made = @mkdir($nested, 0755, true);
        $this->assertTrue($made, "Cannot create nested cache dir: {$nested}");

        $probe = $nested . '/.storage-writable-probe-' . uniqid();
        $written = @file_put_contents($probe, 'ok');
        $this->assertNotFalse($written, "Cannot write nested cache file: {$probe}");
        @unlink($probe);
        $this->assertTrue(@rmdir($u1 === $u2 ? $nested : $nested), 'cleanup nested');
        if ($u1 !== $u2) {
            @rmdir($dataDir . '/' . $u1);
        }
    }
}
