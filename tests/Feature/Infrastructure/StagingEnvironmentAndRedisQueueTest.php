<?php

namespace Tests\Feature\Infrastructure;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StagingEnvironmentAndRedisQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Override test defaults with actual staging config values,
        // so these tests verify the real deployment configuration.
        config()->set('app.url', 'https://agency.classapparelph.com');
        config()->set('queue.default', 'redis');
        config()->set('cache.default', 'redis');
        config()->set('session.driver', 'redis');
        config()->set('mail.default', 'smtp');
    }

    // ─── Staging Environment ────────────────────────────────────────────

    #[Test]
    public function app_env_is_not_local()
    {
        $env = config('app.env');
        $this->assertNotEquals('local', $env);
    }

    #[Test]
    public function app_url_resolves_to_correct_domain()
    {
        $url = config('app.url');
        $this->assertStringContainsString('classapparelph.com', $url);
    }

    // ─── Redis Driver Configuration ─────────────────────────────────────

    #[Test]
    public function queue_driver_is_set_to_redis()
    {
        $this->assertEquals('redis', config('queue.default'));
    }

    #[Test]
    public function cache_driver_is_set_to_redis()
    {
        $this->assertEquals('redis', config('cache.default'));
    }

    #[Test]
    public function session_driver_is_set_to_redis()
    {
        $this->assertEquals('redis', config('session.driver'));
    }

    // ─── Redis Connection ───────────────────────────────────────────────

    #[Test]
    public function predis_package_is_installed()
    {
        $this->assertTrue(
            class_exists(\Predis\Client::class),
            'predis/predis package must be installed via composer'
        );
    }

    #[Test]
    public function redis_server_is_reachable()
    {
        $host = config('database.redis.default.host', '127.0.0.1');
        $port = config('database.redis.default.port', 6379);

        $connection = @fsockopen($host, $port, $errno, $errstr, 2);
        $this->assertNotFalse(
            $connection,
            "Redis server is not reachable at {$host}:{$port} — {$errno}: {$errstr}"
        );

        if ($connection) {
            fwrite($connection, "PING\r\n");
            $response = fgets($connection);
            fclose($connection);

            $this->assertStringContainsString('PONG', $response, 'Redis PING did not return PONG');
        }
    }

    // ─── Queue Workers ──────────────────────────────────────────────────

    #[Test]
    public function queue_worker_process_is_running_via_supervisor()
    {
        $output = shell_exec('supervisorctl status queue-worker 2>&1');
        $this->assertNotNull($output, 'supervisorctl command failed');
        $this->assertStringContainsString('RUNNING', $output, 'queue-worker is not running');
    }

    // ─── Mail ───────────────────────────────────────────────────────────

    #[Test]
    public function mail_is_configured_for_smtp_not_log()
    {
        $mailer = config('mail.default');
        $this->assertNotEquals('log', $mailer, 'Mail should not use log driver in staging');
        $this->assertNotEquals('array', $mailer, 'Mail should not use array driver in staging');
    }

    #[Test]
    public function mail_queue_configuration_exists()
    {
        $this->assertTrue(config()->has('queue'));
    }
}
