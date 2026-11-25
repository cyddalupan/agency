<?php

namespace App\Listeners;

use App\Console\RefusedCommandException;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Throwable;

/**
 * Card 6b03ee17b53899c76e313ab0 (Part A) — production schema guard.
 *
 * Background: on 2026-11-23 01:21:19 a single DROP TABLE destroyed all 91
 * tables of `super_landas`.
 *
 * ⚠️  KNOWN LIMITATION — this listener is NOT sufficient on its own, and on
 * 2026-11-25 a wipe got past it twice. Laravel 13's
 * Foundation\Console\Kernel registers its Symfony command-event bridge inside
 * `$this->app->booted(fn () => ... if (! $this->app->runningUnitTests()) ...)`.
 * When `APP_ENV=testing`, `runningUnitTests()` is true, so the bridge is never
 * registered, `CommandStarting` is NEVER DISPATCHED, and no listener of any
 * kind runs. `APP_ENV=testing php artisan migrate:fresh --force` therefore
 * walked straight through this class and emptied production.
 *
 * THE LOAD-BEARING GUARD IS THE DATABASE GRANT, not this listener:
 *   REVOKE DROP ON `super_landas`.* FROM 'super_landas'@'localhost';
 * That is enforced by MySQL for every client and cannot be bypassed by any
 * environment variable, artisan flag, or framework code path. Verified
 * 2026-11-25: migrate:fresh / migrate:refresh / migrate:reset / db:wipe all
 * fail with "1142 DROP command denied" and 95 tables survive.
 *
 * This listener remains as a first line for the paths where the event DOES
 * fire (APP_ENV=local, production, staging), where it produces a clear refusal
 * message and exit code instead of a raw SQL error. Do not remove it, and do
 * not treat it as the guarantee.
 *
 * Behaviour:
 *  - Fires only for migrate:fresh, migrate:refresh, migrate:reset, db:wipe
 *    and db:seed.
 *  - Reads the *actual connection database name* (getDatabaseName()) from the
 *    connection the command will use (--database option, else the default
 *    connection). APP_ENV is never consulted to *decide* a refusal: a staging
 *    .env pointing at prod is exactly the case that matters.
 *  - Refuses when that name starts with `super_landas`.
 *    `--force` answers a confirmation prompt; it does not answer a hard-coded
 *    refusal.
 *  - Writes the refusal to STDERR and logs it. The throw stops the command;
 *    Laravel's Kernel::handle() catches every Throwable and returns 1, so the
 *    exit code is best-effort, not reliable.
 *
 * Registered in AppServiceProvider::boot() on CommandStarting.
 */
class RefuseDestructiveCommandsOnProduction
{
    /** Commands that destroy or reseed the schema. */
    public const BLOCKED = [
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'db:wipe',
        'db:seed',
    ];

    /** Protected database name prefix (compared lower-cased). */
    public const PROTECTED = 'super_landas';

    /** Exit code when the guard fires (distinct from 1 = failed command). */
    public const EXIT_CODE = RefusedCommandException::EXIT_CODE;

    /**
     * Is this target a scratch database we own, for test-run exemption purposes?
     *
     * "Harmless" here means "a name we created for this purpose": sqlite
     * (phpunit.xml uses :memory:) or a `zz_` database on the `mysql` connection.
     * The `zz_` case is gated on the connection name, not on the prefix alone:
     * `super_landas_rehearsal` and `landas_ci_local` are NOT scratch by this
     * rule. Only a `zz_` prefix on the `mysql` connection is.
     */
    private function isHarmlessTestTarget(?string $connectionName, string $database): bool
    {
        return $connectionName === 'sqlite'
            || $database === ':memory:'
            || str_ends_with($database, '.sqlite')
            || str_starts_with($database, '/tmp/')
            || ($connectionName === 'mysql' && str_starts_with($database, 'zz_'));
    }

    public function handle(CommandStarting $event): void
    {
        $command = $this->normalize($event->command);

        if (! in_array($command, self::BLOCKED, true)) {
            return;
        }

        // Resolve the ACTUAL target database first, unconditionally. This must
        // never be skipped: `APP_ENV=testing` makes runningUnitTests() true for
        // a hand-run artisan command against MySQL just as it does inside the
        // test suite, and on 2026-11-25 that ordering let
        // `APP_ENV=testing php artisan migrate:fresh --force` wipe production.
        $database = $this->targetDatabase($event);

        if ($database === null) {
            return;
        }

        $database = strtolower(trim($database));

        // Only now may tests be exempted, and only for a database that cannot
        // possibly be production: phpunit.xml forces sqlite :memory:, and
        // phpunit.mysql.xml targets a `zz_` scratch database. A test run that
        // somehow resolves to a real database is still guarded.
        if ($this->runningUnitTests() && $this->isHarmlessTestTarget($this->connectionName($event), $database)) {
            return;
        }

        if (! str_starts_with($database, self::PROTECTED)) {
            return;
        }

        $message = sprintf(
            'REFUSED: %s would destroy the schema of database "%s" (production).'."\n".
            'This command is blocked regardless of --force. If you truly need a rebuild,'."\n".
            'do it on /var/www/landas-test or a zz_ scratch database, never on production.',
            $command,
            $database
        );

        $this->writeError($event, $message);

        if (defined('STDERR')) {
            fwrite(STDERR, $message."\n");
        }

        $this->logRefusal($command, $database);

        // Exit 3, explicitly. Laravel's Kernel::handle() catches every
        // Throwable and returns 1 — so a throw alone cannot carry the code to
        // the shell, and "the guard fired" would look like "the command
        // broke". The throw is a belt-and-braces stop for in-process callers
        // (Artisan::call inside a worker/tinker); the exit is what an operator
        // sees.
        if ($this->runningInConsoleProcess()) {
            exit(self::EXIT_CODE);
        }

        throw new RefusedCommandException($message);
    }

    /**
     * Strip any path/vendor prefix and trailing arguments from the name.
     */
    private function normalize(?string $command): string
    {
        $command = trim((string) $command);

        if (str_contains($command, ' ')) {
            $command = explode(' ', $command)[0];
        }

        return ltrim($command, ':');
    }

    /**
     * The database the command would actually act on.
     *
     * Order: --database option, else config('database.default'); then the
     * live connection's getDatabaseName(). If the connection cannot be opened
     * we fall back to the configured database name so a misconfigured
     * connection still trips the guard instead of silently passing.
     */
    private function targetDatabase(CommandStarting $event): ?string
    {
        $connectionName = $this->connectionName($event);

        if ($connectionName === null) {
            return null;
        }

        try {
            return DB::connection($connectionName)->getDatabaseName();
        } catch (Throwable) {
            $configured = config('database.connections.'.$connectionName.'.database');

            return is_string($configured) && $configured !== '' ? $configured : null;
        }
    }

    /**
     * The connection name the command would act on: --database option, else
     * config('database.default').
     */
    private function connectionName(CommandStarting $event): ?string
    {
        $connectionName = null;

        try {
            $input = $event->input;

            if ($input->hasOption('database') && $input->getOption('database')) {
                $connectionName = (string) $input->getOption('database');
            }
        } catch (Throwable) {
            // Definition not bound yet — fall through to the default.
        }

        $connectionName = $connectionName ?: (string) config('database.default');

        return $connectionName === '' ? null : $connectionName;
    }

    private function writeError(CommandStarting $event, string $message): void
    {
        try {
            $output = $event->output;

            if ($output instanceof ConsoleOutputInterface) {
                $output->getErrorOutput()->writeln('<error>'.$message.'</error>');
            } else {
                $output->writeln('<error>'.$message.'</error>');
            }
        } catch (Throwable) {
            // Never let formatting failures hide the refusal; the STDERR write
            // in handle() is authoritative.
        }
    }

    private function runningUnitTests(): bool
    {
        try {
            return app()->runningUnitTests();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Exit only when this really is an artisan process. Tests and web
     * requests must get the exception, not a dead worker.
     */
    private function runningInConsoleProcess(): bool
    {
        return PHP_SAPI === 'cli' && ! $this->runningUnitTests();
    }

    /**
     * Record the refusal in the log, one line, no stack trace.
     */
    private function logRefusal(string $command, string $database): void
    {
        try {
            logger()->error('Production schema guard refused command', [
                'command' => $command,
                'database' => $database,
                'exit_code' => self::EXIT_CODE,
            ]);
        } catch (Throwable) {
            //
        }
    }
}
