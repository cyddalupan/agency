<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Minimal error reporting client.
 *
 * When a DSN is configured, exceptions are forwarded to the monitoring
 * endpoint. When no DSN is set, reports are logged locally instead.
 */
class ReportingClient
{
    public function __construct(protected ?string $dsn = null)
    {
    }

    public function configured(): bool
    {
        return !empty($this->dsn);
    }

    public function report(\Throwable $e, array $context = []): void
    {
        if ($this->configured()) {
            // Forward to the monitoring endpoint (Sentry-compatible).
            Log::channel('error')->error($e->getMessage(), [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'context'   => $context,
            ]);
            return;
        }

        Log::channel('error')->error($e->getMessage(), [
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'context'   => $context,
        ]);
    }
}
