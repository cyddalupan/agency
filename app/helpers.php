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

if (!function_exists('app_brand_landas_logo')) {
    /**
     * The "Powered by Landas" footer wordmark.
     *
     * Resolution order:
     *   1. A platform-level override chosen in God Mode — stored in the
     *      `settings` table under key `brand.landas_footer_logo`. That key may
     *      hold either a storage path (uploaded file, resolved through
     *      Storage::url) or an absolute/relative URL (pasted address).
     *   2. The shipped default asset at public/images/landas-logo.png.
     *
     * The override is read through a try/catch and a schema existence check
     * because this helper renders on EVERY page, including the God Mode login
     * and any error page — it must never be the reason a page 500s. If the
     * settings table does not exist yet (fresh install, mid-migration) the
     * default asset is used.
     */
    function app_brand_landas_logo(): string
    {
        $default = asset('images/landas-logo.png');

        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return $default;
            }

            $value = \Illuminate\Support\Facades\DB::table('settings')
                ->where('key', 'brand.landas_footer_logo')
                ->value('value');

            if (! is_string($value) || trim($value) === '') {
                return $default;
            }

            $value = trim($value);

            // Absolute URL (http/https) or a root-relative path: use verbatim.
            if (preg_match('#^(https?:)?//#i', $value) === 1 || str_starts_with($value, '/')) {
                return $value;
            }

            // Otherwise treat it as a storage path on the public disk.
            return \Illuminate\Support\Facades\Storage::disk('public')->url($value);
        } catch (\Throwable $e) {
            return $default;
        }
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
            'custom_sources'   => [],
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
     * Canonical (built-in) source options shipped with the app.
     * Agencies may additionally add their own sources in Settings — see
     * app_agency_source_options().
     */
    function app_source_options(): array
    {
        return ['Facebook', 'Referral', 'Walk-in', 'Website', 'Other', 'Branch'];
    }
}

if (!function_exists('app_normalize_source_name')) {
    /**
     * Clean a user-typed source name: strip tags, collapse whitespace, trim,
     * and cap the length. Returns '' when nothing usable is left.
     */
    function app_normalize_source_name(?string $value): string
    {
        $value = strip_tags((string) $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';
        $value = trim($value);

        return \Illuminate\Support\Str::limit($value, 40, '');
    }
}

if (!function_exists('app_agency_source_options')) {
    /**
     * All source options available to an agency: the canonical list plus any
     * custom sources the agency added in Settings (deduped case-insensitively,
     * canonical order first, then customs in the order they were added).
     */
    function app_agency_source_options(?\App\Models\Agency $agency = null): array
    {
        $agency = $agency ?? resolve_agency();
        $options = app_source_options();

        if (! $agency) {
            return $options;
        }

        $defaults = app_applicant_form_defaults($agency);
        foreach ((array) ($defaults['custom_sources'] ?? []) as $custom) {
            $custom = app_normalize_source_name(is_string($custom) ? $custom : '');
            if ($custom === '') {
                continue;
            }
            if (in_array(mb_strtolower($custom), array_map('mb_strtolower', $options), true)) {
                continue;
            }
            $options[] = $custom;
        }

        return $options;
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

if (!function_exists('app_color_themes')) {
    /**
     * All selectable Color Themes (from config/color_themes.php).
     */
    function app_color_themes(): array
    {
        return config('color_themes.themes', []);
    }
}

if (!function_exists('app_color_theme')) {
    /**
     * The agency's selected Color Theme key, always resolvable to a known theme
     * ('default' when unset or unknown).
     */
    function app_color_theme(?\App\Models\Agency $agency = null): string
    {
        $agency = $agency ?? resolve_agency();
        $key = 'default';

        if ($agency) {
            $settings = $agency->settings;
            $settings = is_object($settings) ? $settings->toArray() : (array) ($settings ?? []);
            $key = (string) ($settings['color_theme'] ?? 'default');
        }

        return array_key_exists($key, app_color_themes()) ? $key : 'default';
    }
}

if (!function_exists('app_color_theme_style')) {
    /**
     * CSS custom properties that recolour the DaisyUI primary/secondary/accent
     * tokens for the agency's chosen Color Theme. Empty string for 'default'
     * (no override — the system keeps its built-in palette).
     */
    function app_color_theme_style(?\App\Models\Agency $agency = null): string
    {
        $theme  = app_color_theme($agency);
        $colors = app_color_themes()[$theme]['colors'] ?? [];

        if (! $colors) {
            return '';
        }

        $css = '';
        foreach (['primary', 'secondary', 'accent'] as $token) {
            $hex = $colors[$token] ?? null;
            if (! is_string($hex) || ! preg_match('/^#[0-9a-fA-F]{6}$/', $hex)) {
                continue;
            }

            $content = app_hex_is_light($hex) ? '#1a2744' : '#ffffff';
            $css .= "--color-{$token}:{$hex};--color-{$token}-content:{$content};";
        }

        return $css;
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

        // Normalize before parsing: editors love to emit &nbsp; (0xC2 0xA0) and
        // stray control bytes inside tag markup, and libxml answers that with
        // "error parsing attribute name" — silently dropping every attribute on
        // the tag (that is how images lost their src on save).
        $html = str_replace(["\xC2\xA0", "\xC2"], [' ', ''], $html);
        $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $html);
        if (! mb_check_encoding($html, 'UTF-8')) {
            $html = iconv('UTF-8', 'UTF-8//IGNORE', $html) ?: '';
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

if (!function_exists('print_mode')) {
    /**
     * Print mode (Mjolnir card "LANDAS: Print Page 'Missing Data'", 2026-11-05).
     *
     * Module index pages are paginated for the screen. When the global Print
     * button (or `?print=1`) is used we must render EVERY filtered row so the
     * browser's print/PDF output contains every data entry, not just page 1.
     */
    function print_mode(): bool
    {
        return request()->boolean('print');
    }
}

if (!function_exists('print_per_page')) {
    /** Pagination size for the current request — effectively "all rows" in print mode. */
    function print_per_page(int $default): int
    {
        return print_mode() ? 100000 : $default;
    }
}

if (!function_exists('print_header_filters')) {
    /**
     * Human-readable summary of the query string for the print header
     * (ignores paging / print flags).
     */
    function print_header_filters(): string
    {
        $skip = ['page', 'print'];
        $parts = [];

        foreach (request()->query() as $key => $value) {
            if (in_array($key, $skip, true) || is_array($value) || $value === '' || $value === null) {
                continue;
            }
            $parts[] = ucfirst(str_replace('_', ' ', (string) $key)) . ': ' . $value;
        }

        return implode(' · ', $parts);
    }
}

if (!function_exists('hide_type_tab_count')) {
    /**
     * Whether the applicant "type" tab counter should be hidden for the
     * current agency (Mjolnir card "LANDAS: Applicant module 'Tabs'",
     * 2026-11-05).
     *
     * Gulf hides the HOUSEHOLD / SKILLED counters; FINAS and other agencies
     * keep them. The ALL TYPES counter is never hidden. Per-agency override:
     * agencies.settings.applicants.hide_type_tab_counts.
     */
    function hide_type_tab_count(string $type): bool
    {
        if ($type === 'all') {
            return false;
        }

        $agency = resolve_agency();
        if (! $agency) {
            return false;
        }

        $settings = is_array($agency->settings) ? $agency->settings : [];
        $flag = $settings['applicants']['hide_type_tab_counts'] ?? null;
        if ($flag !== null) {
            return (bool) $flag;
        }

        return str_contains(strtoupper((string) $agency->name), 'GULF');
    }
}

if (!function_exists('agent_term')) {
    /**
     * Card 6af81c30: FINAS and CSDI print "Marketing Staff" where the rest of
     * the platform says "Agent". Returns the singular noun, e.g. in
     * "Select {{ agent_term() }}…".
     */
    function agent_term(?int $agencyId = null): string
    {
        return \App\Support\AgentTerm::noun($agencyId);
    }
}

if (!function_exists('agent_terms')) {
    /** Plural form of agent_term(). */
    function agent_terms(?int $agencyId = null): string
    {
        return \App\Support\AgentTerm::plural($agencyId);
    }
}

if (!function_exists('agent_header')) {
    /**
     * Card 6af81c30 (Josh 2026-11-15): CSV/PDF column headers must follow the
     * same rename as the screens, so a FINAS export does not come back saying
     * "Agent" when the app says "Marketing Staff".
     */
    function agent_header(string $header, ?int $agencyId = null): string
    {
        return \App\Support\AgentTerm::text($header, $agencyId);
    }
}

if (!function_exists('peso')) {
    /**
     * Card 6b0729b8 (2026-11-26): one money formatter for the whole app.
     *
     * Negative amounts used to render as "₱-14,497.00" because the symbol was
     * concatenated in Blade *before* number_format(), so the minus landed
     * inside the formatted string. Correct is "-₱14,497.00": the sign belongs
     * to the number, so it goes outside the currency symbol.
     *
     * Display only. Every computed value is untouched; this changes the string
     * form and nothing else. Decimals and thousands separators are the
     * number_format() defaults everywhere, exactly as before.
     *
     * @param  mixed  $value  number, numeric string, or null
     * @param  bool   $signed false suppresses the sign entirely (callers that
     *                        print an already-known-negative as an absolute)
     * @param  int    $decimals must match the number_format() call it replaces;
     *                        the sweep preserves whatever each site already used
     */
    function peso($value, bool $signed = true, int $decimals = 2): string
    {
        // null is zero: an unset balance must not print an error or a blank.
        if ($value === null || $value === '') {
            $value = 0;
        }
        $number = (float) $value;
        $formatted = number_format(abs($number), $decimals);
        // A value that ROUNDS to zero carries no sign. -0.004 must not print
        // "-₱0.00": the sign would imply a debt the user does not have.
        if ($signed && $number < 0 && (float) $formatted !== 0.0) {
            return '-₱' . $formatted;
        }
        return '₱' . $formatted;
    }
}
