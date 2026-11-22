<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a tenant-scoped model is queried from an HTTP request that has
 * no resolvable agency.
 *
 * Card 6b008b8dc9020955b2cc16e1: the tenant scope used to fail *open* — an
 * unrecognised subdomain or an unlisted custom domain fell through apply()
 * with no WHERE clause and returned every agency's rows. It now fails closed
 * on the web. Console/queue contexts are exempt (see TenantScope docblock).
 */
class UnboundTenantException extends RuntimeException
{
    public static function forHost(?string $host, string $table): self
    {
        return new self(sprintf(
            'Tenant scope could not be applied to [%s]: on host [%s] no agency could be resolved. '
            . 'Refusing to run an unfiltered (cross-agency) query outside console.',
            $table,
            $host ?: 'unknown'
        ));
    }
}
