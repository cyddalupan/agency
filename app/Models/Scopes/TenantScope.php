<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Check container-bound agency (from middleware or controller)
        $agency = app()->has('tenant_agency') ? app('tenant_agency') : null;

        if ($agency) {
            $builder->where($model->getTable() . '.agency_id', $agency->id);
            return;
        }

        // Fallback: session-bound agency (when logged in on main domain)
        $agencyId = session('tenant_agency_id');
        if ($agencyId) {
            $builder->where($model->getTable() . '.agency_id', $agencyId);
        }
    }
}
