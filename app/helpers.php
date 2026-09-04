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

if (!function_exists('app_applicant_table_column_labels')) {
    /**
     * All columns available on the Applicants table (key => header label).
     * The action column is always rendered and is not part of this list.
     */
    function app_applicant_table_column_labels(): array
    {
        return [
            'name'             => 'Name',
            'contact'          => 'Contact#',
            'gender'           => 'Gender',
            'age'              => 'Age',
            'branch'           => 'Branch',
            'agent'            => 'Agent',
            'position'         => 'Position',
            'country'          => 'Country',
            'fra'              => 'FRA',
            'status'           => 'Status',
            'date_applied'     => 'Date Applied',
            'contract_signed'  => 'Contract Signed Date',
            'contract_received'=> 'Contract Received',
            'encoder'          => 'Encoder',
        ];
    }
}

if (!function_exists('app_applicant_table_columns')) {
    /**
     * Resolve the ordered list of applicant table columns for an agency.
     *
     * Reads agencies.settings['applicants_table_columns']; falls back to the
     * default column set when nothing is configured. The action column is
     * always rendered regardless of this list.
     */
    function app_applicant_table_columns(?\App\Models\Agency $agency = null): array
    {
        $all = app_applicant_table_column_labels();

        // Default column set: the legacy always-on Browse Applicants columns
        // (BROWSE APPLICANT spec) so unconfigured agencies keep the classic
        // layout. Agencies can opt into a different set via Settings →
        // Applicants Table Columns.
        $defaults = [
            'date_applied', 'name', 'status', 'age', 'contact', 'position',
            'branch', 'agent', 'contract_signed', 'contract_received', 'encoder',
        ];

        $agency = $agency ?? resolve_agency();
        if (! $agency) {
            return $defaults;
        }

        $settings   = is_object($agency->settings) ? $agency->settings->toArray() : (array) ($agency->settings ?? []);
        $configured = $settings['applicants_table_columns'] ?? null;

        if (! is_array($configured) || empty($configured)) {
            return $defaults;
        }

        // Keep only known keys, preserve the agency's chosen order, and make
        // sure core columns (name first, status last before action) can never
        // be dropped or misplaced.
        $columns = array_values(array_filter($configured, fn ($c) => isset($all[$c])));

        // Name always first.
        if (($pos = array_search('name', $columns)) !== false) {
            unset($columns[$pos]);
        }
        array_unshift($columns, 'name');

        // Status always last (immediately before the always-on Action column).
        $columns = array_values(array_filter($columns, fn ($c) => $c !== 'status'));
        $columns[] = 'status';

        return $columns;
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
