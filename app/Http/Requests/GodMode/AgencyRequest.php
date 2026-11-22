<?php

declare(strict_types=1);

namespace App\Http\Requests\GodMode;

use App\Models\Agency;
use App\Services\GodMode\GodAgencyService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Card 6b0344184136c21243a7af8b — the one validation rule set for create and
 * edit.
 *
 * The shape matches public registration (`AgencyRegistrationController`) so a
 * god-created tenant and a self-registered one cannot end up with different
 * field rules; what differs is deliberate:
 *
 *   - `subdomain` is REQUIRED. Registration has none; here the tenant URL is
 *     the point of the console, and six live rows with NULL cannot be reached.
 *   - `status` is limited to active/inactive. `pending` is registration's to
 *     grant, and `suspended` does not exist (§1).
 *   - reserved labels are refused, `godmode` included.
 *
 * Authorisation is NOT here: the `godmode.only` middleware is the sole gate
 * (§6), and a FormRequest `authorize()` would be a second, weaker copy of it.
 */
class AgencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise BEFORE validation so `WWW`, `https://Foo.../` and `Foo` all
     * validate as what they mean. Merging into request input means the
     * validator's `unique` rule and the form's old input see the same value
     * the controller will write.
     */
    protected function prepareForValidation(): void
    {
        $service = app(GodAgencyService::class);

        $this->merge([
            'subdomain' => $service->normaliseSubdomain($this->input('subdomain')),
            'email'     => filled($this->input('email')) ? mb_strtolower(trim((string) $this->input('email'))) : null,
            'name'      => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'contact_person' => is_string($this->input('contact_person')) ? trim($this->input('contact_person')) : $this->input('contact_person'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $agencyId = $this->route('agency')?->id;

        $uniqueSubdomain = Rule::unique('agencies', 'subdomain');
        $uniqueEmail     = Rule::unique('agencies', 'email');

        if ($agencyId) {
            $uniqueSubdomain->ignore($agencyId);
            $uniqueEmail->ignore($agencyId);
        }

        return [
            'name'           => ['required', 'string', 'max:255'],
            'subdomain'      => ['required', 'string', 'max:63', 'alpha_dash', $uniqueSubdomain],
            'email'          => ['nullable', 'email', 'max:255', $uniqueEmail],
            'contact_person' => ['required', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:100'],
            'address'        => ['nullable', 'string', 'max:255'],
            'num_branches'   => ['required', 'integer', 'min:1', 'max:200'],
            'status'         => ['required', Rule::in(GodAgencyService::SELECTABLE_STATUSES)],
        ];
    }

    /**
     * Reserved labels are checked AFTER normalisation, so `WWW` is refused by
     * the same rule as `www` and the message names the label the operator
     * ended up with. Done here rather than with `not_in` because `not_in`
     * would report the merged value as untransformed input ("www is
     * reserved") and the form would look like it ignored the field.
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
            'subdomain.required'    => 'A subdomain is required: it is the agency\'s address.',
            'subdomain.alpha_dash'  => 'Use letters, numbers, dashes and underscores only.',
            'subdomain.unique'      => 'Another agency already uses that subdomain.',
            'email.unique'          => 'Another agency already uses that email address.',
            'num_branches.max'      => 'Branch count looks wrong for one agency (max 200).',
        ];
    }

    /** The attributes this console writes — never `settings`, `logo` or the profile. */
    public function identityPayload(): array
    {
        $data = $this->validated();

        return [
            'name'           => $data['name'],
            'subdomain'      => $data['subdomain'],
            'email'          => $data['email'] ?? null,
            'contact_person' => $data['contact_person'],
            'city'           => $data['city'] ?? null,
            'address'        => $data['address'] ?? null,
            'num_branches'   => (int) $data['num_branches'],
            'status'         => $data['status'],
        ];
    }

    /** The route model for an edit, if this request has one. */
    public function agency(): ?Agency
    {
        return $this->route('agency');
    }
}
