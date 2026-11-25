<?php

namespace App\Console;

/**
 * Card 6b03ee17b53899c76e313ab0 (Part A) — refusal type for the production
 * schema guard (App\Listeners\RefuseDestructiveCommandsOnProduction).
 *
 * getCode() is 3 and is load-bearing.
 *
 * Why the exception is not simply allowed to bubble: Laravel's Kernel::handle()
 * wraps the whole Symfony console run in `try { ... } catch (Throwable) {
 * return 1; }`. A thrown exception of ANY class therefore reaches the shell as
 * exit 1, which would make "the guard fired" indistinguishable from "the
 * command broke". So the listener writes its output, logs the refusal, and then
 * terminates the process with this code (see
 * RefuseDestructiveCommandsOnProduction::handle()). The throw exists only to
 * stop the command before its handle() runs; it never reaches the shell.
 */
final class RefusedCommandException extends \RuntimeException
{
    public const EXIT_CODE = 3;

    public function __construct(string $message)
    {
        parent::__construct($message, self::EXIT_CODE);
    }
}
