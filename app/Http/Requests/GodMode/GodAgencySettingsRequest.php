<?php

declare(strict_types=1);

namespace App\Http\Requests\GodMode;

use App\Services\GodMode\GodAgencySettingsService;
use App\Support\ModuleAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Card 6b03439e6043673862503f2b — validation for the per-agency settings form.
 *
 * Authorisation is NOT here: `godmode.only` is the sole gate (§3.2), exactly
 * as `AgencyRequest` documents. A policy call would be a second, weaker copy —
 * `AgencyPolicy::before()` passes `super_admin` and would look right.
 *
 * Bounds are wider than any real value and narrower than any absurd one. Zero
 * is REJECTED, not coerced: `Complaint::slaFor()` treats 0 as "not configured",
 * so turning 0 into 2 would hide an operator mistake.
 *
 * Booleans are normalised in `prepareForValidation()` so an unchecked box
 * (absent from the payload) is an explicit `false` rather than "not submitted"
 * — the form always owns those keys.
 */
class GodAgencySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Card 6b07f36226255ccf628c992d AC6: validation runs BEFORE the controller,
     * so the default failure response sends the operator back to the referring
     * URL — which, from the modal, is the village WITHOUT the ?open= marker.
     * The modal then cannot reopen and the error is invisible. Route the failure
     * back to the village with the agency marked, so the same building reopens
     * with the message in it.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        if ($this->input('from') !== 'village') {
            parent::failedValidation($validator);
            return;
        }

        $agency = $this->route('agency');
        $id = is_object($agency) ? $agency->id : $agency;

        throw (new \Illuminate\Validation\ValidationException($validator))
            ->redirectTo(route('godmode.dashboard', ['open' => $id]));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'hide_type_tab_counts' => $this->boolean('hide_type_tab_counts'),
            'enable_firstimer'     => $this->boolean('enable_firstimer'),
            'all_caps'             => $this->boolean('all_caps'),
            'show_company_profile' => $this->boolean('show_company_profile'),
            'banner_color'         => filled($this->input('banner_color'))
                ? trim((string) $this->input('banner_color'))
                : null,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'qms_enabled' => [
                'required',
                Rule::in([GodAgencySettingsService::QMS_ON, GodAgencySettingsService::QMS_OFF, GodAgencySettingsService::QMS_DEFAULT]),
            ],
            'hide_type_tab_counts' => ['boolean'],
            'complaint_sla' => ['required', 'array'],
            'complaint_sla.ack_days' => ['required', 'integer', 'min:1', 'max:30'],
            'complaint_sla.resolve_days' => ['required', 'integer', 'min:1', 'max:90'],
            'enable_firstimer' => ['boolean'],
            'color_theme' => ['required', 'string', Rule::in(array_keys(app_color_themes()))],
            'all_caps' => ['boolean'],
            'banner_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'show_company_profile' => ['boolean'],
            'settings_version' => ['required', 'string'],
        ];
    }

    /**
     * Cross-field: the resolution window must not be shorter than the
     * acknowledgement window. Compared against the EFFECTIVE ack (submitted
     * value when present, otherwise the stored/default value), so a form that
     * only moves one number cannot slip a 1-day resolution under a stored
     * 30-day acknowledgement (§4.3).
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $submitted = $this->input('complaint_sla', []);
                $submitted = is_array($submitted) ? $submitted : [];

                $agency = $this->route('agency');
                $stored = $agency instanceof \App\Models\Agency
                    ? app(GodAgencySettingsService::class)->sla($agency)
                    : ['ack_days' => GodAgencySettingsService::SLA_ACK_DEFAULT];

                $ack = (int) ($submitted['ack_days'] ?? $stored['ack_days']);
                $resolve = (int) ($submitted['resolve_days'] ?? $stored['resolve_days'] ?? GodAgencySettingsService::SLA_RESOLVE_DEFAULT);

                if ($resolve < $ack) {
                    $validator->errors()->add(
                        'complaint_sla.resolve_days',
                        'Resolution window must be at least the acknowledgement window.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'qms_enabled.required' => 'Choose On, Off, or Default for QMS.',
            'qms_enabled.in'       => 'Choose On, Off, or Default for QMS.',
            'complaint_sla.ack_days.integer' => 'Acknowledgement window must be 1 to 30 days.',
            'complaint_sla.ack_days.min'     => 'Acknowledgement window must be 1 to 30 days.',
            'complaint_sla.ack_days.max'     => 'Acknowledgement window must be 1 to 30 days.',
            'complaint_sla.resolve_days.integer' => 'Resolution window must be 1 to 90 days.',
            'complaint_sla.resolve_days.min'     => 'Resolution window must be 1 to 90 days.',
            'complaint_sla.resolve_days.max'     => 'Resolution window must be 1 to 90 days.',
            'color_theme.in'   => 'Unknown theme.',
            'banner_color.regex' => 'Banner colour must be a hex value like #220dbf.',
            'settings_version.required' => 'Reload the page and try again.',
        ];
    }

    /**
     * Only the keys this form owns. `settings_version` is the concurrency
     * token, never a document key. `complaint_sla` is passed through whole —
     * `merge()` ignores parts it does not own.
     */
    public function settingsInput(): array
    {
        $data = $this->validated();

        unset($data['settings_version']);

        return $data;
    }

    public function settingsVersion(): string
    {
        return (string) $this->input('settings_version');
    }
}
