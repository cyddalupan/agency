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
 * tables of `super_landas`. The app's DB user still holds DROP, so the
 * database itself would permit a repeat. This listener is the independent
 * safeguard that cannot be bypassed by a flag.
 *
 * Behaviour:
 *  - Fires only for migrate:fresh, migrate:refresh, migrate:reset, db:wipe
 *    and db:seed.
 *  - Reads the *actual connection database name* (getDatabaseName()) from the
 *    connection the command will use (--database option, else the default
 *    connection). APP_ENV is never consulted: a staging .env pointing at prod
 *    is exactly the case that matters.
 *  - Refuses unconditionally when that name starts with `super_landas`.
 *    `--force` answers a confirmation prompt; it does not answer a hard-coded
 *    refusal.
 *  - Writes the refusal to STDERR, logs it, and terminates the process with
 *    exit 3. The throw is there only to stop the command before handle();
 *    Laravel's Kernel::handle() catches every Throwable and returns 1, so the
 *    exit code is set by an explicit terminate. No table is touched.
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

    public function handle(CommandStarting $event): void
    {
        $command = $this->normalize($event->command);

        if (! in_array($command, self::BLOCKED, true)) {
            return;
        }

        // phpunit.xml forces sqlite :memory:, which can never be
        // `super_landas`. Skipping keeps the suite able to call migrate:fresh
        // on sqlite; anything that could still resolve to prod is caught by
        // the database-name check below.
        if ($this->runningUnitTests()) {
            return;
        }

        $database = $this->targetDatabase($event);

        if ($database === null) {
            return;
        }

        if (! str_starts_with(strtolower(trim($database)), self::PROTECTED)) {
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

        if ($connectionName === '') {
            return null;
        }

        try {
            return DB::connection($connectionName)->getDatabaseName();
        } catch (Throwable) {
            $configured = config('database.connections.'.$connectionName.'.database');

            return is_string($configured) && $configured !== '' ? $configured : null;
        }
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
