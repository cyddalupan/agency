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
        $sourceOpts = app_source_options();
        $defaults   = app_applicant_form_defaults($agency);

        return view('settings.applicant-form-defaults', compact(
            'agency', 'positions', 'statuses', 'sourceOpts', 'defaults'
        ));
    }

    /**
     * Persist the agency's selected applicant-form defaults (JSON on agencies.settings).
     * Validation ensures only known reference values / sources are saved — no typos.
     */
    public function updateApplicantFormDefaults(Request $request)
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');

        $validated = $request->validate([
            'position_ids'      => ['sometimes', 'array'],
            'position_ids.*'    => ['integer', 'exists:positions,id'],
            'status_codes'      => ['sometimes', 'array'],
            'status_codes.*'    => ['integer', 'exists:status_codes,code'],
            'sources'           => ['sometimes', 'array'],
            'sources.*'         => ['string', \Illuminate\Validation\Rule::in(app_source_options())],
            'enable_firstimer'  => ['nullable', 'in:1,0,on'],
        ]);

        $settings = is_object($agency->settings) ? $agency->settings->toArray() : (array) ($agency->settings ?? []);
        $settings['applicant_form_defaults'] = [
            'position_ids'      => array_values(array_map('intval', $validated['position_ids'] ?? [])),
            'status_codes'      => array_values(array_map('intval', $validated['status_codes'] ?? [])),
            'sources'           => $validated['sources'] ?? [],
            'enable_firstimer'  => $request->boolean('enable_firstimer'),
            'firstimer_options' => ['Firstimer', 'Ex-Abroad'],
        ];
        $agency->update(['settings' => $settings]);

        return redirect()->route('settings.applicant-form-defaults')
            ->with('success', 'Applicant form defaults saved.');
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
}
