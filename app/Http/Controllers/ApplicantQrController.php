<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * QR intake for the Add Applicant form (card 6af82438, FINAS and CSDI only).
 *
 * The card: "Generate a unique QR Code that if the applicant scans it, it will
 * automatically bring it to an Add Applicant page only. This will help
 * employees lessen their workload in encoding the applicant data."
 *
 * Design notes:
 * - One stable token per agency, stored in agencies.settings so no migration
 *   and no new credential table is needed.
 * - The token carries only the agency. It is NOT a login: an applicant who
 *   scans it reaches the Add Applicant form and nothing else — every other
 *   route still requires a signed-in user.
 * - Rotating the token invalidates previously printed codes.
 */
class ApplicantQrController extends Controller
{
    /** Agencies that get the QR feature (card: "CSDI and Finas only"). */
    public const AGENCIES = ['finas', 'csdi'];

    public const SETTINGS_KEY = 'applicant_qr_token';

    public static function enabled(?Agency $agency): bool
    {
        if (! $agency) {
            return false;
        }

        return \App\Support\ModuleAccess::isAnyAgency($agency->id, self::AGENCIES);
    }

    /** Read the agency's token, creating one on first use. */
    public static function tokenFor(Agency $agency): string
    {
        $settings = self::settings($agency);
        $token = $settings[self::SETTINGS_KEY] ?? null;

        if (! is_string($token) || $token === '') {
            return self::rotateToken($agency);
        }

        return $token;
    }

    /** Issue a fresh token, invalidating any code printed before it. */
    public static function rotateToken(Agency $agency): string
    {
        $settings = self::settings($agency);
        $settings[self::SETTINGS_KEY] = Str::random(40);
        $agency->update(['settings' => $settings]);

        return $settings[self::SETTINGS_KEY];
    }

    /** Public URL encoded into the QR image. */
    public static function scanUrl(Agency $agency, string $token): string
    {
        return route('applicant-qr.entry', ['token' => $token]);
    }

    /**
     * Where a scan lands: the Add Applicant form for the scanned agency.
     * Invalid token -> 404, so a leaked/guessed URL reveals nothing.
     */
    public function entry(Request $request, string $token)
    {
        $agency = self::agencyForToken($token);

        if (! $agency) {
            abort(404);
        }

        return $this->renderForm($agency, $token);
    }

    /**
     * Submit from the QR form. Public by design: the applicant is not a staff
     * user. The record is created for the token's agency, in Pending status.
     */
    public function store(Request $request, string $token): RedirectResponse
    {
        $agency = self::agencyForToken($token);

        if (! $agency) {
            abort(404);
        }

        $validated = $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'middle_name'   => 'nullable|string|max:255',
            'suffix'        => 'nullable|string|max:255',
            'birthdate'     => 'nullable|date',
            'gender'        => 'nullable|string|max:20',
            'contact'       => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string',
            'position_id'   => 'nullable|integer|exists:positions,id',
        ]);

        // Scoped to the scanned agency regardless of any other context, and
        // forced to Pending so a scan can never skip the review pipeline.
        $validated['agency_id'] = $agency->id;
        $validated['status_code'] = 0;
        $validated['source'] = 'QR Code';

        if (! empty($validated['position_id'])) {
            $position = \App\Models\Position::find($validated['position_id']);
            $validated['applicant_type'] = match ($position?->category) {
                \App\Models\Position::CATEGORY_HOUSEHOLD => \App\Models\Applicant::TYPE_HOUSEHOLD,
                \App\Models\Position::CATEGORY_PROFESSIONAL => \App\Models\Applicant::TYPE_PROFESSIONAL,
                default => \App\Models\Applicant::TYPE_SKILLED,
            };
        }

        \App\Models\Applicant::create($validated);

        return redirect()->route('applicant-qr.entry', ['token' => $token])
            ->with('qr_success', 'Thank you — your details were submitted.');
    }

    private function renderForm(Agency $agency, string $token)
    {
        return view('applicants.qr-form', [
            'agencyName' => $agency->name,
            'token' => $token,
            'positions' => \App\Models\Position::orderBy('name')->get(['id', 'name', 'category']),
        ]);
    }

    /** Find the agency owning this token (linear scan over a tiny table). */
    public static function agencyForToken(string $token): ?Agency
    {
        if ($token === '') {
            return null;
        }

        foreach (Agency::all() as $agency) {
            $settings = self::settings($agency);
            $stored = $settings[self::SETTINGS_KEY] ?? null;

            if (is_string($stored) && $stored !== '' && hash_equals($stored, $token)) {
                return self::enabled($agency) ? $agency : null;
            }
        }

        return null;
    }

    private static function settings(Agency $agency): array
    {
        return is_object($agency->settings)
            ? $agency->settings->toArray()
            : (array) ($agency->settings ?? []);
    }
}
