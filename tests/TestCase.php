<?php

namespace Tests;

use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private static bool $productionCheckoutChecked = false;

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
     * Checked once per process (setUp() runs 4,000+ times in a full suite and
     * the answer cannot change mid-run). Both conditions are checked because
     * either one alone is defeatable: APP_ENV can be overridden per-run, and a
     * symlink or another copy of the tree defeats the path check. When both
     * are false the environment is not remotely production.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! self::$productionCheckoutChecked) {
            self::$productionCheckoutChecked = true;
            $this->refuseProductionCheckout();
        }
    }

    private function refuseProductionCheckout(): void
    {
        // After parent::setUp() on purpose. PHPUnit 12's createApplication()
        // installs a null Container (no basePath, no environment()) and only
        // boots the real app afterwards; a check placed before it sees the
        // null container, returns early, and never fires — that is how my
        // first version "passed" on the production checkout.
        $app = Container::getInstance();
        if (! $app instanceof Application) {
            // Cannot tell where we are. A suite that cannot prove it is not in
            // production must not run: deleting the app is cheaper than a
            // production write, and this is the branch that only exists because
            // the check silently passed once.
            $this->fail(
                'Refusing to run: tests/TestCase.php could not resolve the application after setUp(), '
                . 'so it cannot prove this is not the production checkout. '
                . 'Fix the test bootstrap; do not comment this guard out.'
            );
        }

        $onProdCheckout = realpath($app->basePath()) === realpath(self::PRODUCTION_CHECKOUT);
        $asProduction = $app->environment('production');

        if (! $onProdCheckout && ! $asProduction) {
            return;
        }

        // Opt out only explicitly, and only for a deliberate read-only probe.
        // getenv(), not env(): phpunit.xml's <env> entries are read once into
        // $_ENV and a shell ALLOW_PROD_TEST_RUN=1 takes effect through getenv().
        $override = getenv('ALLOW_PROD_TEST_RUN');
        if ($override !== false && $override !== '' && $override !== '0') {
            return;
        }

        $why = $onProdCheckout
            ? 'this checkout is the production one (' . self::PRODUCTION_CHECKOUT . ')'
            : 'APP_ENV is production';

        $this->fail(
            'The test suite must not run against production: ' . $why . '. '
            . 'It runs with production .env, production storage/app and production storage/logs, '
            . 'so one uncommitted file with a bug writes to live data or the live log. '
            . 'Run tests from the clone: ' . self::CLONE_TO_USE . ' '
            . '(copy .env.example, DB_CONNECTION=sqlite, DB_DATABASE=:memory:, LOG_CHANNEL=null, php artisan key:generate). '
            . 'For a deliberate read-only probe in place, re-run with ALLOW_PROD_TEST_RUN=1.'
        );
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
