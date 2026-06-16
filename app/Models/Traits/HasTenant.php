<?php

namespace App\Models\Traits;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasTenant
{
    protected static function bootHasTenant(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Agency::class);
    }
}
