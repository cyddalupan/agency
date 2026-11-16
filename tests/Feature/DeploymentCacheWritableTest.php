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
 *
 * These are DEPLOYMENT PREFLIGHTS, not unit tests: they inspect the host they
 * run on. They are deliberately strict — a skipped permission check on a
 * preflight reports "nothing wrong" when the truth is "I couldn't look", which
 * is how a broken host reaches production looking healthy. deploy.sh fixes the
 * permissions; this proves it did.
 *
 * Run against a fresh checkout, the base directory may legitimately not exist
 * yet; that is still a failure, because the app cannot serve requests from a
 * checkout with no cache directory.
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
    public function cache_data_dir_and_its_prefix_dirs_are_web_user_writable(): void
    {
        $base = storage_path('framework/cache/data');

        // Absence is a failure, not a skip. deploy.sh is expected to leave this
        // directory present and writable on every host it runs on.
        $this->assertDirectoryExists(
            $base,
            'file cache data dir is missing; deploy.sh should have created it'
        );

        // Check the base itself. The prefix-dir loop below iterates over
        // whatever exists, so an empty directory would otherwise pass without
        // testing anything at all — the original bug was a writability problem,
        // and an empty dir proves nothing about writability.
        $this->assertDirIsWebUserWritable($base);

        foreach (glob($base.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $this->assertDirIsWebUserWritable($dir);
        }
    }

    /**
     * One assertion for both properties, so a failure names the directory and
     * says precisely what is wrong with it. Ownership and writability were
     * previously two tests, which meant a root-owned 0755 dir failed one while
     * passing the other, with no message stating the actual condition.
     */
    private function assertDirIsWebUserWritable(string $dir): void
    {
        $uid   = fileowner($dir);
        $perms = fileperms($dir) & 0777;
        $groupWritable = (bool) ($perms & 0020);
        $otherWritable = (bool) ($perms & 0002);

        $this->assertNotSame(
            0,
            $uid,
            "cache dir must not be root-owned: {$dir} (owner=root, perms=".decoct($perms).')'
        );

        $this->assertTrue(
            $groupWritable || $otherWritable,
            "cache dir not writable by web user: {$dir} (owner={$uid}, perms=".decoct($perms)
                .'); deploy.sh should chmod prefix dirs 2775'
        );
    }
}
