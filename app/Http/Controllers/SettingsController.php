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
        $fraOpts    = app_fra_options();
        $defaults   = app_applicant_form_defaults($agency);

        return view('settings.applicant-form-defaults', compact(
            'agency', 'positions', 'statuses', 'sourceOpts', 'fraOpts', 'defaults'
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
            'fra_options'       => ['sometimes', 'array'],
            'fra_options.*'     => ['string', \Illuminate\Validation\Rule::in(array_keys(app_fra_options()))],
        ]);

        $settings = is_object($agency->settings) ? $agency->settings->toArray() : (array) ($agency->settings ?? []);
        $settings['applicant_form_defaults'] = [
            'position_ids'      => array_values(array_map('intval', $validated['position_ids'] ?? [])),
            'status_codes'      => array_values(array_map('intval', $validated['status_codes'] ?? [])),
            'sources'           => $validated['sources'] ?? [],
            'enable_firstimer'  => $request->boolean('enable_firstimer'),
            'firstimer_options' => ['Firstimer', 'Ex-Abroad'],
            'fra_options'       => array_values($validated['fra_options'] ?? []),
        ];
        $agency->update(['settings' => $settings]);

        return redirect()->route('settings.applicant-form-defaults')
            ->with('success', 'Applicant form defaults saved.');
    }
}
