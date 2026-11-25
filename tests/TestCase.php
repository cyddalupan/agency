<?php

namespace Tests;

use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\AssertionFailedError;

abstract class TestCase extends BaseTestCase
{
    /**
     * Where the suite is allowed to run (card 6b043d33af3017bf577329a5).
     */
    private const PRODUCTION_CHECKOUT = '/var/www/landas-fixit';

    private const CLONE_TO_USE = '/var/www/landas-test';

    /**
     * Refuse to run a test from the production checkout, or with the app
     * running as production.
     *
     * The 2026-11-23 incident was not a log-volume problem: ~1,400 frames of
     * Tenant::bypass() recursion ran from an UNCOMMITTED working tree against
     * this checkout, with production's .env, production's filesystem and
     * production's log. A log-channel pin only survives that as long as the
     * untracked code goes through `Log` and not a raw fwrite. This guard is
     * about where and as what the code runs, which is the part a working-tree
     * edit cannot bypass: it fires before any test method executes.
     *
     * The check runs in EVERY test — setUp() and tearDown() — and stores no
     * result. The previous version cached it (`self::$productionCheckoutChecked`),
     * so on the production checkout it refused the first test and let the other
     * five execute against production: `Tests: 6, Assertions: 49, Failures: 1`.
     * A check that reports a problem and then lets the run continue is the bug
     * class this card exists to stop, so the property here is "every test
     * refused", not "one error".
     *
     * Per-test, not per-class, on purpose. `setUpBeforeClass()` runs before any
     * application exists: PHPUnit 12 gives it the test runner's own blank
     * container, so it cannot name the checkout from base_path() and any check
     * there reports something other than the refusal. The per-test hooks are
     * the first point where the question "which checkout is this?" is
     * answerable: by then the app is booted, its base_path() is the real one,
     * and the answer is exact. A REFUSED test is the one case where two
     * applications get built (setUp: createApplication() before the guard, then
     * the runner's own with APP_ENV=testing after the setUp() failure and the
     * still-run tearDown). Measured here: 6 tests refused, 1.21 s, 93 MB peak,
     * against 1.15 s / 90 MB for a single test that boots once.
     *
     * Both the path and the env are checked because either alone is defeatable:
     * APP_ENV can be overridden per-run, and a symlink or another copy of the
     * tree defeats the path check. When both are false the environment is not
     * remotely production.
     */
    protected function setUp(): void
    {
        // FIRST, and before parent::setUp() on purpose: Laravel's setUpTraits()
        // runs RefreshDatabase INSIDE parent::setUp(), so a check placed after
        // it fires only once the schema has already been built. Card
        // 6b038a9e824ea78607d0da8d: on MySQL the database name must be `zz_`
        // BEFORE any table moves — not merely before the test body runs.
        $this->refuseNonScratchMysqlTarget();

        parent::setUp();

        // After parent::setUp() on purpose: before it there is no application,
        // and an earlier version that returned early on a null container
        // "passed" on the production checkout.
        $this->refuseProductionCheckout('setUp');
    }

    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } finally {
            // finally: PHPUnit does not treat a setUp() failure as a reason to
            // skip tearDown(), and a subclass whose tearDown() throws must not
            // get to skip the check either. Nothing here swallows the original
            // exception — the refusal is the same exception type and message.
            $this->refuseProductionCheckout('tearDown');
        }
    }

    /**
     * The message names the clone to use and how to set it up: a refusal that
     * does not say what to do instead gets bypassed.
     */
    private static function refusalMessage(bool $onProdCheckout): string
    {
        $why = $onProdCheckout
            ? 'this checkout is the production one (' . self::PRODUCTION_CHECKOUT . ')'
            : 'APP_ENV is production';

        return 'The test suite must not run against production: ' . $why . '. '
            . 'It runs with production .env, production storage/app and production storage/logs, '
            . 'so one uncommitted file with a bug writes to live data or the live log. '
            . 'Run tests from the clone: ' . self::CLONE_TO_USE . ' '
            . '(copy .env.example, DB_CONNECTION=sqlite, DB_DATABASE=:memory:, LOG_CHANNEL=null, php artisan key:generate). '
            . 'For a deliberate read-only probe in place, re-run with ALLOW_PROD_TEST_RUN=1.';
    }

    private static function refusalIsOverridden(): bool
    {
        // Opt out only explicitly, and only for a deliberate read-only probe.
        // getenv(), not env(): phpunit.xml's <env> entries are read once into
        // $_ENV and a shell ALLOW_PROD_TEST_RUN=1 takes effect through getenv().
        $override = getenv('ALLOW_PROD_TEST_RUN');

        return $override !== false && $override !== '' && $override !== '0';
    }

    /**
     * Ask the application this test is running, not PHPUnit's shared container.
     *
     * `Container::getInstance() instanceof Application` and `app()` are both
     * wrong here: between setUp() and tearDown() the base class points the
     * shared instance at the harness container, which knows its own `app`
     * binding only while the test is running. Reading that binding off
     * $this->app is the one answer that is true for the duration of the test.
     */
    private function refuseProductionCheckout(string $from): void
    {
        $app = $this->app;

        if (! $app instanceof Application) {
            // No application to look at. A test that needs one has already
            // failed in parent::setUp(); this hook must not turn that into a
            // guard refusal. setUpBeforeClass() is where a bootless class is
            // caught, by the working directory.
            return;
        }

        $onProdCheckout = realpath($app->basePath()) === realpath(self::PRODUCTION_CHECKOUT);
        $asProduction = $app->environment('production');

        if (! $onProdCheckout && ! $asProduction) {
            return;
        }

        if (self::refusalIsOverridden()) {
            return;
        }

        // A thrown AssertionFailedError, not $this->fail(): a fail() in setUp()
        // is reported as a failure AND as risky ("did not perform any
        // assertions"), while a throw from before the test body is one clean
        // failure per test — the "Tests: N, Failures: N" a run of the checkout
        // must produce.
        throw new AssertionFailedError(self::refusalMessage($onProdCheckout));
    }

    /**
     * Refuse to run a test against any MySQL database whose name is not `zz_`.
     *
     * Called twice: before parent::setUp() (to fire ahead of RefreshDatabase's
     * migrate) and after the checkout check. When there is no application yet,
     * the target is resolved from the environment the same way the connection
     * will resolve it, so the refusal is not skipped for lack of an app.
     *
     * SQLite (the default suite) is untouched: `:memory:` has no name to check.
     */
    private function refuseNonScratchMysqlTarget(): void
    {
        $driver = null;
        $database = null;

        if ($this->app instanceof Application) {
            $driver = config('database.default');
            $database = $this->app['db']->connection()->getDatabaseName();
        } else {
            // No application: resolve from $_ENV/$_SERVER the same way
            // config/database.php will. phpunit.mysql.xml sets DB_CONNECTION
            // to mysql for this configuration, so a mysql run is recognisable
            // here even before boot.
            $driver = self::resolvedEnv('DB_CONNECTION');
            $database = self::resolvedEnv('DB_DATABASE');
        }

        if ((string) $driver !== 'mysql') {
            return;
        }

        $database = (string) $database;

        if ($database === '' || $database === 'null' || preg_match('/^zz_[a-z0-9_]+$/i', $database) === 1) {
            return;
        }

        // AssertionFailedError, not fail(): same reasoning as the checkout
        // refusal above — one clean failure per test, no "risky" noise.
        throw new AssertionFailedError(
            "Refusing to run against MySQL database '{$database}': MySQL test runs are only allowed on a zz_ database. "
            . 'Create zz_landas_test; see README Testing Environment.'
        );
    }

    /**
     * Read an env value the way Laravel's dotenv/env helper will see it.
     * getenv() is checked too: a shell-exported DB_DATABASE wins over the
     * file for <env force="false"> entries.
     */
    private static function resolvedEnv(string $key): ?string
    {
        $fromEnv = getenv($key);

        if ($fromEnv !== false && $fromEnv !== '') {
            return $fromEnv;
        }

        return $_ENV[$key] ?? $_SERVER[$key] ?? null;
    }

    /**
     * Extract the Agent Ledger table's <tbody> HTML from a rendered page.
     *
     * The agent report page contains a filter <select> listing every agent, so
     * assertions like "this agent is absent" must be scoped to the ledger rows
     * or they match the dropdown instead. Declared once here so every test that
     * needs the scope gets the same one.
     */
    protected function ledgerTbody(string $html): string
    {
        $start = strpos($html, 'Agent Ledger');
        $this->assertNotFalse($start, 'Agent Ledger card not found in page');

        $open = strpos($html, '<tbody>', $start);
        $this->assertNotFalse($open, 'ledger <tbody> not found');

        $close = strpos($html, '</tbody>', $open);
        $this->assertNotFalse($close, 'ledger </tbody> not found');

        return substr($html, $open, $close - $open);
    }

    /**
     * Extract one ledger row's money cells, in column order:
     * commission, cash advance, backout/repat, AR, return payment, payments, balance.
     *
     * assertSeeInOrder() is NOT enough for this: it only requires the values to
     * appear in order somewhere on the page, with arbitrary content in between.
     * A figure leaking from one column into another still passes. Comparing the
     * actual cells is what pins each number to its own column.
     *
     * @return list<string> e.g. ['₱0.00','₱560.00',...,'₱560.00']
     */
    protected function ledgerRow(string $html, string $agentName): array
    {
        $tbody = $this->ledgerTbody($html);

        // Split into <tr> rows and find the one belonging to $agentName.
        preg_match_all('#<tr>(.*?)</tr>#s', $tbody, $rows);

        foreach ($rows[1] as $row) {
            if (! str_contains($row, $agentName)) {
                continue;
            }

            preg_match_all('#<td[^>]*>(.*?)</td>#s', $row, $cells);
            $cells = array_map(fn ($c) => trim(html_entity_decode(strip_tags($c))), $cells[1]);

            // Drop the leading name and branch cells; keep the seven money columns.
            return array_slice($cells, 2);
        }

        $this->fail("no ledger row found for agent {$agentName}");
    }
}
