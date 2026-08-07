<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\FileStore;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression guard for the login-time 500:
 *
 *   ErrorException  Filesystem.php:204
 *   file_put_contents(../storage/framework/cache/data/da/0f/...): No such file or directory
 *
 * Root cause: storage/framework/cache/data/<hash-prefix> dirs owned by root
 * (created by deploy.sh's view:cache/route:cache run as root), so the web
 * user (www-data) can't create nested subdirs like "0f/" inside them.
 */
class DeploymentCacheWritableTest extends TestCase
{
    #[Test]
    public function file_cache_can_round_trip_a_value_into_a_temp_dir(): void
    {
        // Exercise the real file-cache code path (FileStore) against a temp
        // directory so we don't pollute production storage/.framework/cache.
        $tmp = sys_get_temp_dir().'/landas_cache_test_'.uniqid('', true);
        try {
            $store = new FileStore(new Filesystem(), $tmp);

            $key = 'deployment_health_'.uniqid('', true);
            $store->put($key, 'ok', 30);
            $this->assertSame('ok', $store->get($key));
            $store->forget($key);
            $this->assertNull($store->get($key));
        } finally {
            (new Filesystem())->deleteDirectory($tmp);
        }
    }

    #[Test]
    public function no_cache_data_prefix_dir_is_owned_by_root(): void
    {
        $base = storage_path('framework/cache/data');

        if (! is_dir($base)) {
            $this->markTestSkipped('file cache data dir not present');
        }

        foreach (glob($base.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $owner = fileowner($dir);
            $this->assertNotSame(
                0,
                $owner,
                "cache data prefix dir must not be root-owned: {$dir}"
            );
        }
    }

    #[Test]
    public function cache_data_prefix_dirs_are_writable_by_group_or_others(): void
    {
        $base = storage_path('framework/cache/data');

        if (! is_dir($base)) {
            $this->markTestSkipped('file cache data dir not present');
        }

        foreach (glob($base.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $perms = fileperms($dir) & 0777;
            $groupWritable = (bool) ($perms & 0020); // group write
            $otherWritable = (bool) ($perms & 0002); // other write
            $this->assertTrue(
                $groupWritable || $otherWritable,
                "cache data prefix dir not writable by web user: {$dir} (perms ".decoct($perms).')'
            );
        }
    }
}
