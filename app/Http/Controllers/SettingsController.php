<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Position;
use App\Models\StatusCode;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Show the per-agency 'Applicant Form Defaults' editor.
     * All option sets are SELECTED from existing reference data (no free text).
     */
    public function applicantFormDefaults()
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');

        $positions  = Position::orderBy('name')->get();
        $statuses   = StatusCode::orderBy('sort_order')->get();
        // Canonical sources ship with the app; agencies may add their own below.
        $sourceOpts    = app_source_options();
        $defaults      = app_applicant_form_defaults($agency);
        $customSources = [];
        foreach ((array) ($defaults['custom_sources'] ?? []) as $custom) {
            $custom = app_normalize_source_name(is_string($custom) ? $custom : '');
            if ($custom === '' || in_array($custom, $sourceOpts, true)) {
                continue;
            }
            $customSources[] = $custom;
        }

        return view('settings.applicant-form-defaults', compact(
            'agency', 'positions', 'statuses', 'sourceOpts', 'customSources', 'defaults'
        ));
    }

    /**
     * Persist the agency's selected applicant-form defaults (JSON on agencies.settings).
     *
     * Sources are the one option set an agency may extend: canonical sources are
     * picked from the built-in list, and "Add source" lets the agency type its own
     * (e.g. TikTok, Job Fair). Typed names are normalized and deduped so the list
     * never collects blanks, tags, or case-variant duplicates.
     */
    public function updateApplicantFormDefaults(Request $request)
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');

        // Only canonical + already-saved customs may be re-submitted as checked
        // boxes; anything else must come through the "add source" field.
        $selectable = app_agency_source_options($agency);

        $validated = $request->validate([
            'position_ids'      => ['sometimes', 'array'],
            'position_ids.*'    => ['integer', 'exists:positions,id'],
            'status_codes'      => ['sometimes', 'array'],
            'status_codes.*'    => ['integer', 'exists:status_codes,code'],
            'sources'           => ['sometimes', 'array'],
            'sources.*'         => ['string', \Illuminate\Validation\Rule::in($selectable)],
            'new_sources'       => ['nullable', 'string', 'max:500'],
            'enable_firstimer'  => ['nullable', 'in:1,0,on'],
        ]);

        $existingCustoms = array_values(array_filter(
            array_map([self::class, 'normalizeSource'], (array) ($this->agencyDefaults($agency)['custom_sources'] ?? []))
        ));

        $selected = array_values(array_intersect($selectable, (array) ($validated['sources'] ?? [])));

        // Keep customs the agency left ticked; drop the ones it unticked.
        $keptCustoms = array_values(array_filter(
            $existingCustoms,
            fn ($custom) => in_array($custom, $selected, true)
        ));

        // Parse "Add source" (comma / newline / semicolon separated). A typed
        // name that matches a known source (built-in or already-saved custom)
        // simply ticks that source instead of creating a near-duplicate.
        $added    = [];
        $selected = array_values(array_merge($selected, []));
        $raw      = (string) ($validated['new_sources'] ?? '');
        foreach (preg_split('/[,;\r\n]+/u', $raw) ?: [] as $piece) {
            $name = app_normalize_source_name($piece);
            if ($name === '') {
                continue;
            }
            $lower = mb_strtolower($name);

            $known = app_source_options();
            foreach ($known as $canonical) {
                if (mb_strtolower($canonical) === $lower) {
                    if (! in_array($canonical, $selected, true)) {
                        $selected[] = $canonical;
                    }
                    continue 2;
                }
            }

            foreach ($keptCustoms as $custom) {
                if (mb_strtolower($custom) === $lower) {
                    if (! in_array($custom, $selected, true)) {
                        $selected[] = $custom;
                    }
                    continue 2;
                }
            }

            if (in_array($lower, array_map('mb_strtolower', $added), true)) {
                continue;
            }

            $added[]    = $name;
            $selected[] = $name;
        }

        $customs = array_slice(array_values(array_unique(array_merge($keptCustoms, $added))), 0, 30);

        $settings = is_object($agency->settings) ? $agency->settings->toArray() : (array) ($agency->settings ?? []);
        $settings['applicant_form_defaults'] = [
            'position_ids'      => array_values(array_map('intval', $validated['position_ids'] ?? [])),
            'status_codes'      => array_values(array_map('intval', $validated['status_codes'] ?? [])),
            'sources'           => array_values(array_unique($selected)),
            'custom_sources'    => $customs,
            'enable_firstimer'  => $request->boolean('enable_firstimer'),
            'firstimer_options' => ['Firstimer', 'Ex-Abroad'],
        ];
        $agency->update(['settings' => $settings]);

        $message = 'Applicant form defaults saved.';
        if ($added) {
            $message = 'Applicant form defaults saved — added source(s): '.implode(', ', $added).'.';
        }

        return redirect()->route('settings.applicant-form-defaults')
            ->with('success', $message);
    }

    /** Current saved defaults for an agency (array, never a collection). */
    private function agencyDefaults(Agency $agency): array
    {
        $settings = is_object($agency->settings) ? $agency->settings->toArray() : (array) ($agency->settings ?? []);

        return (array) ($settings['applicant_form_defaults'] ?? []);
    }

    /** Normalize a stored custom source name (guards legacy/garbage values). */
    private static function normalizeSource($value): string
    {
        return is_string($value) ? app_normalize_source_name($value) : '';
    }

    /**
     * Show the per-agency Applicants table column picker.
     */
    public function applicantTableColumns()
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');

        $labels = app_applicant_table_column_labels();
        $selected = app_applicant_table_columns($agency);

        return view('settings.applicants-table-columns', compact('agency', 'labels', 'selected'));
    }

    /**
     * Persist the agency's chosen Applicants table columns (JSON on agencies.settings).
     * Only known column keys are accepted.
     */
    public function updateApplicantTableColumns(Request $request)
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');

        $labels = app_applicant_table_column_labels();

        $validated = $request->validate([
            'columns'   => ['nullable', 'array'],
            'columns.*' => ['string', \Illuminate\Validation\Rule::in(array_keys($labels))],
        ]);

        $settings = is_object($agency->settings) ? $agency->settings->toArray() : (array) ($agency->settings ?? []);
        $settings['applicants_table_columns'] = $validated['columns'] ?? [];
        $agency->update(['settings' => $settings]);

        return redirect()->route('settings.applicants-table-columns')
            ->with('success', 'Applicants table columns saved.');
    }

    /**
     * Show the per-agency site display (ALL CAPS + banner color) settings.
     */
    public function siteDisplay()
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');

        $allCaps = app_site_all_caps($agency);
        $bannerColor = app_site_banner_color($agency);

        return view('settings.site-display', compact('agency', 'allCaps', 'bannerColor'));
    }

    /**
     * Persist the agency's ALL CAPS + banner color preferences (JSON on agencies.settings).
     */
    public function updateSiteDisplay(Request $request)
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');

        $validated = $request->validate([
            'banner_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $settings = is_object($agency->settings) ? $agency->settings->toArray() : (array) ($agency->settings ?? []);
        $settings['all_caps'] = $request->boolean('all_caps');

        if ($request->boolean('custom_banner_color') && filled($validated['banner_color'] ?? null)) {
            $settings['banner_color'] = strtolower($validated['banner_color']);
        } else {
            unset($settings['banner_color']);
        }

        $agency->update(['settings' => $settings]);

        $messages = [];
        $messages[] = $settings['all_caps']
            ? 'ALL CAPS mode: ON.'
            : 'ALL CAPS mode: OFF.';
        $messages[] = isset($settings['banner_color'])
            ? 'Banner color saved — every gold banner on your site now uses your color.'
            : 'Banner color reset to the default gold.';

        return redirect()->route('settings.site-display')
            ->with('success', implode(' ', $messages));
    }

    /**
     * Show per-agency Agency Settings (sidebar visibility).
     */
    public function agencySettings()
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');

        $showCompanyProfile = app_show_company_profile($agency);

        return view('settings.agency', compact('agency', 'showCompanyProfile'));
    }

    /**
     * Persist the agency's sidebar visibility preferences (JSON on agencies.settings).
     */
    public function updateAgencySettings(Request $request)
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');

        $settings = is_object($agency->settings) ? $agency->settings->toArray() : (array) ($agency->settings ?? []);
        $settings['hide_company_profile'] = ! $request->boolean('show_company_profile');
        $agency->update(['settings' => $settings]);

        return redirect()->route('settings.agency')
            ->with('success', $settings['hide_company_profile']
                ? 'Company Profile hidden from the sidebar for this agency.'
                : 'Company Profile shown in the sidebar for this agency.');
    }

    /**
     * Settings -> QR Code (card 6af82438, FINAS/CSDI).
     *
     * "Generate a unique QR Code that if the applicant scans it, it will
     * automatically bring it to an Add Applicant page only. This will help
     * employees lessen their workload in encoding the applicant data."
     *
     * Each agency gets one stable token in its settings JSON. The QR encodes a
     * public URL carrying that token; scanning it opens the Add Applicant form
     * already bound to the right agency, with nothing else reachable.
     */
    public function qrCode()
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');
        abort_unless(\App\Http\Controllers\ApplicantQrController::enabled($agency), 403,
            'The QR feature is not available for this agency.');

        $token = \App\Http\Controllers\ApplicantQrController::tokenFor($agency);
        $url   = \App\Http\Controllers\ApplicantQrController::scanUrl($agency, $token);

        $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(320)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($url);

        return view('settings.qr-code', [
            'agency' => $agency,
            'token'  => $token,
            'url'    => $url,
            'svg'    => $svg,
        ]);
    }

    /** Rotate the scan token (old printed QRs stop working). */
    public function regenerateQrCode()
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');
        abort_unless(\App\Http\Controllers\ApplicantQrController::enabled($agency), 403,
            'The QR feature is not available for this agency.');

        \App\Http\Controllers\ApplicantQrController::rotateToken($agency);

        return redirect()->route('settings.qr-code')
            ->with('success', 'A new QR code was generated. Previously printed codes no longer work.');
    }

    /**
     * Show the per-agency Color Theme picker.
     */
    public function colorTheme()
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');

        $themes   = app_color_themes();
        $selected = app_color_theme($agency);

        return view('settings.color-theme', compact('agency', 'themes', 'selected'));
    }

    /**
     * Persist the agency's chosen Color Theme (JSON on agencies.settings).
     * Only known theme keys are accepted.
     */
    public function updateColorTheme(Request $request)
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');

        $validated = $request->validate([
            'color_theme' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(app_color_themes()))],
        ]);

        $settings = is_object($agency->settings) ? $agency->settings->toArray() : (array) ($agency->settings ?? []);
        $settings['color_theme'] = $validated['color_theme'];
        $agency->update(['settings' => $settings]);

        $label = app_color_themes()[$validated['color_theme']]['label'] ?? $validated['color_theme'];

        return redirect()->route('settings.color-theme')
            ->with('success', "Color theme set to {$label}.");
    }
}
