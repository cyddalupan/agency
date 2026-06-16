<?php

namespace App\Services;

use App\Models\StatusCode;

class StatusCodeService
{
    public static function exists(int $code): bool
    {
        return StatusCode::where('code', $code)->exists();
    }

    public static function label(int $code): string
    {
        $status = StatusCode::find($code);
        return $status ? $status->label : "Unknown ({$code})";
    }

    public static function all(): array
    {
        return StatusCode::orderBy('sort_order')->pluck('code')->toArray();
    }
}
