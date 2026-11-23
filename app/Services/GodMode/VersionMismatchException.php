<?php

declare(strict_types=1);

namespace App\Services\GodMode;

use RuntimeException;

/**
 * Card 6b03439e6043673862503f2b — the stored settings document moved while an
 * operator had the form open. Thrown inside the save transaction so nothing
 * is written; the request turns it into a 422 with this message.
 */
class VersionMismatchException extends RuntimeException
{
}
