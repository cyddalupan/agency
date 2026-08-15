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

if (!function_exists('resolve_agency')) {
    function resolve_agency(): ?\App\Models\Agency
    {
        // Prefer the authenticated user's agency (agency-scoped dashboard).
        $user = auth()->user();
        if ($user && $user->agency_id) {
            return \App\Models\Agency::find($user->agency_id);
        }

        // Fall back to the tenanted (subdomain) agency.
        return tenant_agency();
    }
}

if (!function_exists('app_brand_name')) {
    function app_brand_name(): string
    {
        $agency = resolve_agency();
        if ($agency && $agency->name) {
            return $agency->name;
        }

        return config('app.universe', 1) == 2 ? 'LANDAS' : 'Agency Super';
    }
}

if (!function_exists('app_brand_icon')) {
    function app_brand_icon(): string
    {
        return config('app.universe', 1) == 2 ? '⛩️' : '⚡';
    }
}

if (!function_exists('app_brand_logo')) {
    function app_brand_logo(): ?string
    {
        $agency = resolve_agency();
        if ($agency && $agency->logo) {
            return \Illuminate\Support\Facades\Storage::url($agency->logo);
        }
        return null;
    }
}

if (!function_exists('app_brand_has_logo')) {
    function app_brand_has_logo(): bool
    {
        $agency = resolve_agency();
        return $agency && !empty($agency->logo);
    }
}

if (!function_exists('app_brand_show_icon')) {
    function app_brand_show_icon(): bool
    {
        return !app_brand_has_logo();
    }
}

if (!function_exists('app_brand_logo_url')) {
    function app_brand_logo_url(): ?string
    {
        return app_brand_logo();
    }
}

if (!function_exists('app_applicant_form_defaults')) {
    /**
     * Resolve the per-agency applicant-form defaults (no hardcoded lists).
     *
     * Reads agencies.settings['applicant_form_defaults'] for the given agency
     * (or the resolved authenticated/tenant agency). Falls back to safe defaults
     * when none configured. Keys: position_ids[], status_codes[], sources[],
     * enable_firstimer(bool), firstimer_options[].
     */
    function app_applicant_form_defaults(?\App\Models\Agency $agency = null): array
    {
        $agency = $agency ?? resolve_agency();

        $defaults = [
            'position_ids'     => [],
            'status_codes'     => [],
            'sources'          => ['Facebook', 'Referral', 'Walk-in', 'Website', 'Other', 'Branch'],
            'enable_firstimer' => true,
            'firstimer_options'=> ['Firstimer', 'Ex-Abroad'],
        ];

        if (! $agency) {
            return $defaults;
        }

        $settings   = is_object($agency->settings) ? $agency->settings->toArray() : (array) ($agency->settings ?? []);
        $configured = $settings['applicant_form_defaults'] ?? [];

        return array_merge($defaults, (array) $configured);
    }
}

if (!function_exists('app_source_options')) {
    /**
     * Known, canonical source options (used by the settings selector UI).
     * Agencies enable a subset; unknown/typo values are never rendered.
     */
    function app_source_options(): array
    {
        return ['Facebook', 'Referral', 'Walk-in', 'Website', 'Other', 'Branch'];
    }
}

if (!function_exists('app_fra_options')) {
    /**
     * Known, canonical FRA options (value => label). Per-agency FRA dropdowns
     * (Status tab) render only the subset an agency enables via
     * applicant_form_defaults.fra_options; unknown/typo values are never shown.
     */
    function app_fra_options(): array
    {
        return [
            'none'          => 'No FRA',
            'for_fra'       => 'For FRA',
            'fra_completed' => 'FRA Completed',
        ];
    }
}

if (!function_exists('app_brand_favicon_emoji')) {
    function app_brand_favicon_emoji(): string
    {
        return config('app.universe', 1) == 2 ? '⛩️' : '⚡';
    }
}

if (!function_exists('resolve_agency_id')) {
    function resolve_agency_id(): ?int
    {
        $user = auth()->user();
        if ($user && $user->agency_id) {
            return $user->agency_id;
        }
        $tenanted = tenant_agency();
        return $tenanted?->id;
    }
}

if (!function_exists('resize_and_save_photo')) {
    function resize_and_save_photo($file, string $directory = 'applicant-photos', int $maxDim = 600): string
    {
        $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$image) {
            // Fallback: just store original
            return $file->store($directory, 'public');
        }

        $origW = imagesx($image);
        $origH = imagesy($image);

        // Only resize if larger than max dim
        if ($origW <= $maxDim && $origH <= $maxDim) {
            imagedestroy($image);
            return $file->store($directory, 'public');
        }

        $ratio = min($maxDim / $origW, $maxDim / $origH);
        $newW = (int) round($origW * $ratio);
        $newH = (int) round($origH * $ratio);

        $resized = imagecreatetruecolor($newW, $newH);

        // Preserve transparency for PNG
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($image);

        // Save as JPEG for smaller size
        $filename = uniqid() . '_' . time() . '.jpg';
        $dir = storage_path('app/public/' . $directory);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $directory . '/' . $filename;
        imagejpeg($resized, storage_path('app/public/' . $path), 85);
        imagedestroy($resized);

        return $path;
    }
}
