<?php

declare(strict_types=1);

namespace App\Http\Requests\GodMode;

use App\Services\GodMode\GodAgencyService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Card 6b07eb521243cfef572cd549 — creating an agency from the village modal.
 *
 * Deliberately SEPARATE from `AgencyRequest`. That one is shared by create and
 * edit on the legacy page and carries no commercial or admin-user fields;
 * bolting them on would make the edit form demand a billing plan and a new
 * password every time an operator fixed a typo in a name.
 *
 * Three sections, all required:
 *   A. tenant identity (name, subdomain, status, address, city, email,
 *      contact person, branch count)
 *   B. the commercial agreement (initial payment + terms, monthly amount +
 *      terms, starts_on, notes)
 *   C. the first operator account (name, email, password)
 *
 * A tenant nobody can log into looks broken to the client, which is why C is
 * required here and optional in the DB.
 */
class VillageAgencyRequest extends FormRequest
{
    /** Same normalisation as AgencyRequest, so `WWW` and `www` agree. */
    protected function prepareForValidation(): void
    {
        $service = app(GodAgencyService::class);

        $this->merge([
            'subdomain' => $service->normaliseSubdomain($this->input('subdomain')),
            'email'     => filled($this->input('email')) ? mb_strtolower(trim((string) $this->input('email'))) : null,
            'admin_email' => filled($this->input('admin_email')) ? mb_strtolower(trim((string) $this->input('admin_email'))) : null,
            'name'      => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'contact_person' => is_string($this->input('contact_person')) ? trim($this->input('contact_person')) : $this->input('contact_person'),
            'admin_name' => is_string($this->input('admin_name')) ? trim($this->input('admin_name')) : $this->input('admin_name'),
        ]);
    }

    public function authorize(): bool
    {
        // `godmode.only` is the sole gate (§6 of the agencies card).
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            // ── A. tenant identity ──
            'name'           => ['required', 'string', 'max:255'],
            'subdomain'      => ['required', 'string', 'max:63', 'alpha_dash', Rule::unique('agencies', 'subdomain')],
            'status'         => ['required', Rule::in(['active', 'pending'])],
            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:100'],
            'email'          => ['nullable', 'email', 'max:255', Rule::unique('agencies', 'email')],
            'contact_person' => ['required', 'string', 'max:255'],
            'num_branches'   => ['required', 'integer', 'min:0', 'max:200'],

            // ── B. commercial agreement ──
            'initial_payment' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'initial_terms'   => ['required', 'integer', 'min:1', 'max:120'],
            'monthly_amount'  => ['required', 'numeric', 'min:0', 'max:999999999'],
            'terms'           => ['required', 'integer', 'min:1', 'max:120'],
            'starts_on'       => ['required', 'date'],
            'notes'           => ['nullable', 'string', 'max:2000'],

            // ── C. first operator account ──
            'admin_name'     => ['required', 'string', 'max:255'],
            'admin_email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'admin_password' => ['required', 'string', 'min:8', 'max:255'],
        ];
    }

    /**
     * Reserved labels checked AFTER normalisation, so the message names the
     * label the operator ended up with rather than what they typed.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $service = app(GodAgencyService::class);
                $label = (string) $this->input('subdomain');

                if ($label !== '' && $service->isReserved($label)) {
                    $validator->errors()->add(
                        'subdomain',
                        'The subdomain "'.$label.'" is reserved. Choose another.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'subdomain.required'      => 'A subdomain is required: it is the agency\'s address.',
            'subdomain.alpha_dash'    => 'Use letters, numbers, dashes and underscores only.',
            'subdomain.unique'        => 'Another agency already uses that subdomain.',
            'email.unique'            => 'Another agency already uses that email address.',
            'admin_email.unique'      => 'That email is already used by an existing account.',
            'admin_password.min'      => 'Use at least 8 characters.',
            'num_branches.min'        => 'Branch count cannot be negative.',
            'initial_terms.min'       => 'The initial payment must clear in at least one payment.',
            'terms.min'               => 'There must be at least one monthly payment.',
            'initial_payment.min'     => 'The initial payment cannot be negative.',
            'monthly_amount.min'      => 'The monthly amount cannot be negative.',
        ];
    }

    /** Section A only — the columns `agencies` actually has. */
    public function identityPayload(): array
    {
        $d = $this->validated();

        return [
            'name'           => $d['name'],
            'subdomain'      => $d['subdomain'],
            'email'          => $d['email'] ?? null,
            'contact_person' => $d['contact_person'],
            'city'           => $d['city'] ?? null,
            'address'        => $d['address'] ?? null,
            'num_branches'   => (int) $d['num_branches'],
            'status'         => $d['status'],
        ];
    }

    /** Section B — the first `agency_billing_plans` row. */
    public function planPayload(): array
    {
        $d = $this->validated();

        return [
            'initial_payment' => number_format((float) $d['initial_payment'], 2, '.', ''),
            'initial_terms'   => (int) $d['initial_terms'],
            'monthly_amount'  => number_format((float) $d['monthly_amount'], 2, '.', ''),
            'terms'           => (int) $d['terms'],
            'currency'        => 'PHP',
            'starts_on'       => $d['starts_on'],
            'notes'           => $d['notes'] ?? null,
            'is_active'       => true,
        ];
    }

    /** Section C — the first admin `users` row. */
    public function adminPayload(): array
    {
        $d = $this->validated();

        return [
            'name'      => $d['admin_name'],
            'email'     => $d['admin_email'],
            'password'  => $d['admin_password'],
            'user_type' => 'admin',
            'status'    => 'active',
        ];
    }
}
