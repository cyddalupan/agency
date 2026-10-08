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

if (!function_exists('app_show_company_profile')) {
    /**
     * Whether the Company Profile link should appear in the sidebar for this agency.
     * Default is visible; an agency can hide it via Settings → Agency Settings.
     */
    function app_show_company_profile(?\App\Models\Agency $agency = null): bool
    {
        $agency = $agency ?? resolve_agency();
        if (! $agency) {
            return false;
        }

        $settings = $agency->settings;
        $settings = is_object($settings) ? $settings->toArray() : (array) ($settings ?? []);

        return empty($settings['hide_company_profile']);
    }
}

if (!function_exists('app_site_all_caps')) {
    function app_site_all_caps(?\App\Models\Agency $agency = null): bool
    {
        $agency = $agency ?? resolve_agency();
        if (! $agency) {
            return false;
        }

        $settings = $agency->settings;
        $settings = is_object($settings) ? $settings->toArray() : (array) ($settings ?? []);

        return ! empty($settings['all_caps']);
    }
}

if (!function_exists('app_site_banner_color')) {
    /**
     * The agency's single banner color (hex), or null when the default gold is used.
     */
    function app_site_banner_color(?\App\Models\Agency $agency = null): ?string
    {
        $agency = $agency ?? resolve_agency();
        if (! $agency) {
            return null;
        }

        $settings = $agency->settings;
        $settings = is_object($settings) ? $settings->toArray() : (array) ($settings ?? []);
        $hex = $settings['banner_color'] ?? null;

        return (is_string($hex) && preg_match('/^#[0-9a-fA-F]{6}$/', $hex)) ? $hex : null;
    }
}

if (!function_exists('app_site_banner_style')) {
    /**
     * CSS custom properties used to recolor every gold gradient on the site
     * when the agency picks a banner color. One color in — the gradient end
     * and the text color are auto-derived.
     */
    function app_site_banner_style(?\App\Models\Agency $agency = null): string
    {
        $hex = app_site_banner_color($agency);
        if (! $hex) {
            return '';
        }

        $c1 = $hex;
        $c2 = app_hex_shade($hex, -0.28);
        $content = app_hex_is_light($hex) ? '#1a2744' : '#ffffff';

        return "--banner-c1:{$c1};--banner-c2:{$c2};--banner-content:{$content};";
    }
}

if (!function_exists('app_hex_is_light')) {
    /**
     * True when a #rrggbb color reads as light (WCAG-ish relative luminance).
     */
    function app_hex_is_light(string $hex): bool
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $lin = fn ($c) => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        $l = 0.2126 * $lin($r) + 0.7152 * $lin($g) + 0.0722 * $lin($b);

        return $l > 0.5;
    }
}

if (!function_exists('app_hex_shade')) {
    /**
     * Lighten (positive) or darken (negative) a #rrggbb hex color.
     */
    function app_hex_shade(string $hex, float $pct): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $t = $pct < 0 ? 0 : 255;
        $p = abs($pct);

        $r = (int) round(($t - $r) * $p) + $r;
        $g = (int) round(($t - $g) * $p) + $g;
        $b = (int) round(($t - $b) * $p) + $b;

        return sprintf('#%02x%02x%02x', $r, $g, $b);
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

if (!function_exists('app_sanitize_page_html')) {
    /**
     * Server-side HTML sanitizer with an allowlist (DOMDocument).
     * Used for WYSIWYG page content that may be rendered publicly later.
     */
    function app_sanitize_page_html(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // Wrap in a known container so DOMDocument tolerates fragment content.
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><div id="sanitize-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $allowedTags = [
            'p', 'br', 'div', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
            'blockquote', 'pre', 'code', 'hr', 'table', 'thead', 'tbody', 'tfoot',
            'tr', 'th', 'td', 'a', 'img', 'figure', 'figcaption',
        ];

        // Grab the wrapper BEFORE any cleanup — the allowlist below strips its
        // id attribute, which would otherwise make a later getElementById/XPath
        // lookup fail. A DOMNode reference survives attribute removal.
        $xpath = new DOMXPath($doc);
        $foundRoot = $xpath->query('//div[@id="sanitize-root"]');
        $root = ($foundRoot && $foundRoot->length > 0) ? $foundRoot->item(0) : null;

        $allowedAttrs = [
            'href', 'title', 'target', 'rel', 'src', 'alt', 'width', 'height',
            'align', 'class', 'style', 'colspan', 'rowspan', 'start', 'type',
        ];

        // URL schemes permitted for links/images.
        $safeSchemes = ['http', 'https', 'mailto', 'tel'];
        $disallowedCss = ['expression', 'javascript:', 'url(', 'behavior', '-moz-binding'];

        $xpath = new DOMXPath($doc);
        foreach (iterator_to_array($xpath->query('//*')) as $node) {
            if (!($node instanceof DOMElement)) {
                continue;
            }

            $tag = strtolower($node->tagName);

            if (!in_array($tag, $allowedTags, true)) {
                $node->parentNode->removeChild($node);
                continue;
            }

            // Remove non-allowlisted attributes.
            foreach (iterator_to_array($node->attributes) as $attr) {
                $name = strtolower($attr->nodeName);

                if (!in_array($name, $allowedAttrs, true)) {
                    $node->removeAttribute($attr->nodeName);
                    continue;
                }

                $value = trim($attr->nodeValue);

                if ($name === 'href' || $name === 'src') {
                    // Allow data:image/* (base64) ONLY on <img> — Quill pastes/embeds
                    // images that way. SVG is rejected (script-bearing).
                    if ($name === 'src' && $tag === 'img' && preg_match('#^data:image/(png|jpe?g|gif|webp);base64,#i', $value)) {
                        continue;
                    }

                    $scheme = parse_url($value, PHP_URL_SCHEME);
                    if ($scheme !== null && !in_array(strtolower((string) $scheme), $safeSchemes, true)) {
                        $node->removeAttribute($attr->nodeName);
                        continue;
                    }
                }

                if ($name === 'target' && strtolower($value) !== '_blank') {
                    $node->removeAttribute($attr->nodeName);
                    continue;
                }

                if ($name === 'style') {
                    $lower = strtolower($value);
                    foreach ($disallowedCss as $needle) {
                        if (str_contains($lower, $needle)) {
                            $node->removeAttribute($attr->nodeName);
                            break;
                        }
                    }
                }
            }

            // Links must not point to unsafe schemes via javascript: etc. (already covered).
            // Images must have http(s) src or a data: blocked by scheme check above.
        }

        // Remove comments & leftover script/style nodes entirely.
        foreach (iterator_to_array($xpath->query('//comment()')) as $node) {
            $node->parentNode->removeChild($node);
        }
        foreach (iterator_to_array($xpath->query('//script | //style | //iframe | //object | //embed | //form | //input | //button')) as $node) {
            $node->parentNode->removeChild($node);
        }

        // Serialize the wrapper's children (root reference captured before cleanup).
        $inner = '';
        if ($root instanceof DOMElement) {
            foreach ($root->childNodes as $child) {
                $inner .= $doc->saveHTML($child);
            }
        }

        return trim($inner);
    }
}

if (!function_exists('resume_locale_for_country')) {
    /**
     * Resolve the resume label locale from a destination country code.
     *
     * The resume shows field labels in English plus the language of the
     * destination country (i.e. the country of the FRA). Returns null when
     * the country has no mapped/needed translation so callers can fall back
     * to the application locale.
     *
     * (Mjolnir "LANDAS: Resume" 2026-10-08)
     */
    function resume_locale_for_country(?string $countryCode): ?string
    {
        $code = strtoupper(trim((string) $countryCode));
        if ($code === '') {
            return null;
        }

        $map = (array) config('resume.locale_by_country', []);

        return $map[$code] ?? null;
    }
}
