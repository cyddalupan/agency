<?php

if (!function_exists('tenant_agency')) {
    function tenant_agency(): ?\App\Models\Agency
    {
        return app()->has('tenant_agency') ? app('tenant_agency') : null;
    }
}

if (!function_exists('is_tenant_request')) {
    function is_tenant_request(): bool
    {
        return tenant_agency() !== null;
    }
}
