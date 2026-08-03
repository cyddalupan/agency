<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Position;
use App\Models\StatusCode;
use Illuminate\Database\Seeder;

/**
 * Sets the per-agency 'applicant_form_defaults' (on agencies.settings) for any
 * agency that does not yet have one, using the LANDAS defaults:
 * Houseboy + Load and Unload Worker positions, the 8 new status codes,
 * all sources (incl. Branch), and Firstimer/Ex-Abroad enabled.
 *
 * Idempotent: only fills when the agency has no configured defaults.
 */
class ApplicantFormDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $statusLabelToCode = StatusCode::pluck('code', 'label');

        $defaultCodes = [
            'For OWWA Make-Up Class',
            'For Tesda',
            'For Biometric',
            'Fit To Work',
            'Unfit',
            'Backout',
            'For Passporting',
            'Passport for Buy-out',
        ];

        foreach (Agency::all() as $agency) {
            $settings = is_object($agency->settings) ? $agency->settings->toArray() : (array) ($agency->settings ?? []);
            if (isset($settings['applicant_form_defaults'])) {
                continue; // already configured
            }

            $positions = Position::whereIn('name', ['Houseboy', 'Load and Unload Worker'])->pluck('id')->all();
            $codes = [];
            foreach ($defaultCodes as $label) {
                if ($code = $statusLabelToCode->get($label)) {
                    $codes[] = $code;
                }
            }

            $settings['applicant_form_defaults'] = [
                'position_ids'      => array_values(array_map('intval', $positions)),
                'status_codes'      => array_values(array_map('intval', $codes)),
                'sources'           => ['Facebook', 'Referral', 'Walk-in', 'Website', 'Other', 'Branch'],
                'enable_firstimer'  => true,
                'firstimer_options' => ['Firstimer', 'Ex-Abroad'],
            ];

            $agency->update(['settings' => $settings]);
        }
    }
}
