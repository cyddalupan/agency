<?php

namespace Tests\Feature\ModuleAccess;

use Tests\Support\LegacyModuleAccess;
use App\Models\Agency;
use App\Support\ModuleAccess;
use App\Support\ModuleGating\GatingTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Equivalence proof for card 6b008b8f3843d89fea14fa60.
 *
 * `legacy_baseline.json` is not written by hand: it is the real output of the
 * pre-refactor ModuleAccess::allows() (HEAD 20f4a401), captured by running the
 * same matrix below through that code over the same fixture database (see the
 * card comment for the capture command). This test asserts the new table-based
 * implementation returns that measured bool for all 16,016 cells.
 *
 * The matrix: every user_type in App\Models\User::ACCESS_PRESETS, the
 * users.user_type migration vocabulary and the controller role lists, plus the
 * out-of-vocabulary probes (mixed case, empty string); every module in
 * ModuleAccess::ROUTE_MODULES values; every agency id in the agencies table
 * (subdomains finas, csdi, gulf, landas, nbp, oyot, acme, zeta, beta, omega,
 * kappa, delta, plus a mixed-case "Finas"), with the QMS switch explicitly on,
 * off and unset across them; and null (no agency).
 *
 * The fixtures are re-created here in the same order and with the same ids as
 * the capture run so the agency ids in the recorded JSON line up.
 */
class ModuleAccessEquivalenceTest extends TestCase
{
    use RefreshDatabase;

    /** Agency fixtures: [subdomain, qms setting (null = column unset)]. */
    private const AGENCIES = [
        ['finas', null],   // 1  QMS on via QMS_DEFAULT_ENABLED_AGENCIES
        ['csdi', true],    // 2
        ['gulf', false],   // 3
        ['landas', null],  // 4
        ['nbp', true],     // 5
        ['oyot', false],   // 6
        ['acme', null],    // 7
        ['zeta', true],    // 8
        ['beta', false],   // 9
        ['omega', null],   // 10
        ['kappa', true],   // 11
        ['delta', false],  // 12
        ['Finas', null],   // 13 mixed-case subdomain: exercises isAnyAgency's strtolower
    ];

    /** Every user type the codebase can hand to allows(). */
    private const USER_TYPES = [
        // ModuleAccess role branches + feature-flag exclusion list
        'super_admin', 'admin', 'billing', 'encoder', 'dmw_user', 'recruitment',
        'branch', 'welfare', 'qms_manager', 'internal_auditor', 'employer', 'staff',
        // users.user_type migration vocabulary / UserController validation /
        // AiAssistantController::ALLOWED_ROLES
        'manager', 'coordinator', 'recruiter', 'processor', 'interviewer',
        'report_viewer', 'director', 'marketer',
        // other role literals found in app/, database/, tests/
        'paralegal', 'operation',
        // probes: unknown role, mixed case, empty
        'hacker_role', 'ADMIN', 'Billing', '',
    ];

    private function seedAgencies(): void
    {
        foreach (self::AGENCIES as [$subdomain, $qms]) {
            $attrs = ['subdomain' => $subdomain, 'name' => 'Agency '.$subdomain];

            if ($qms !== null) {
                $attrs['settings'] = [ModuleAccess::QMS_SETTINGS_KEY => $qms];
            }

            Agency::create($attrs);
        }
    }

    #[Test]
    public function fixtures_reproduce_the_capture_database(): void
    {
        $this->seedAgencies();

        // The recorded baseline is keyed by agency id; if the ids drift the
        // equivalence test would compare different agencies and still pass by
        // accident. Pin them.
        $this->assertSame(
            array_map(fn ($a) => $a[0], self::AGENCIES),
            Agency::orderBy('id')->pluck('subdomain')->all()
        );

        $this->assertTrue(ModuleAccess::qmsEnabled(1), 'finas defaults to QMS on');
        $this->assertFalse(ModuleAccess::qmsEnabled(3), 'gulf has QMS off');
        $this->assertTrue(ModuleAccess::qmsEnabled(2), 'csdi explicit on');
        $this->assertTrue(ModuleAccess::qmsEnabled(5), 'nbp explicit on');
        $this->assertFalse(ModuleAccess::qmsEnabled(9), 'beta explicit off');
    }

    #[Test]
    public function the_table_is_wired_in(): void
    {
        // Guards against the test passing because allows() still points at the
        // old ladder: the role rules must exist and drive the answer.
        $this->seedAgencies();

        $this->assertArrayHasKey('billing', GatingTable::ROLE_RULES);
        $this->assertSame('full', GatingTable::ROLE_RULES['admin']['rule']);

        // A cell the old ladder answered from the FINAS accounting list.
        $this->assertTrue(ModuleAccess::allows('billing', 'accounts', 1));
        // ...and the denylist.
        $this->assertFalse(ModuleAccess::allows('billing', 'users', 1));
        $this->assertFalse(ModuleAccess::allows('billing', 'api', 3));
    }

    #[Test]
    public function new_implementation_matches_the_measured_legacy_baseline_cell_by_cell(): void
    {
        $this->seedAgencies();

        $path = __DIR__.'/legacy_baseline.json';

        $this->assertFileExists($path, 'legacy baseline is missing; it is the measured old output');
        $baseline = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        $modules = array_values(array_unique(array_values(ModuleAccess::ROUTE_MODULES)));
        sort($modules);

        $agencyIds = Agency::orderBy('id')->pluck('id')->map(fn ($v) => (int) $v)->all();
        $agencyIds[] = null;

        $mismatches = [];
        $checked = 0;

        foreach ($agencyIds as $agencyId) {
            foreach (self::USER_TYPES as $userType) {
                foreach ($modules as $module) {
                    $key = (string) $agencyId.'.'.$userType.'.'.$module;

                    if (! array_key_exists($key, $baseline)) {
                        $mismatches[] = "{$key}: no recorded baseline value";

                        continue;
                    }

                    $actual = ModuleAccess::allows($userType, $module, $agencyId);
                    $checked++;

                    if ($actual !== $baseline[$key]) {
                        $mismatches[] = sprintf(
                            '%s: legacy %s, new %s (agency %s, type %s, module %s)',
                            $key,
                            var_export($baseline[$key], true),
                            var_export($actual, true),
                            var_export($agencyId, true),
                            var_export($userType, true),
                            var_export($module, true)
                        );
                    }
                }
            }
        }

        // The matrix size is pinned: 14 agency ids (13 agencies + null) x
        // 26 user types x 44 modules. A shrinking matrix must not pass.
        $this->assertSame(16016, $checked, 'matrix size changed — the baseline no longer covers it');

        $this->assertSame(
            [],
            $mismatches,
            count($mismatches)." of {$checked} cells disagree with the measured legacy implementation:\n"
                .implode("\n", array_slice($mismatches, 0, 25))
        );
    }

    /**
     * Reverse direction: the recorded baseline must be the legacy code's
     * output, not this test's. The legacy allows() ladder is preserved
     * verbatim in tests/Support/LegacyModuleAccess.php (as
     * LegacyModuleAccess::allows()), sharing the fixture constants; if it ever
     * disagrees with the baseline, the baseline has been edited and the
     * equivalence test above proves nothing.
     */
    #[Test]
    public function the_recorded_baseline_is_the_legacy_implementation_output(): void
    {
        $this->seedAgencies();

        $baseline = json_decode(
            (string) file_get_contents(__DIR__.'/legacy_baseline.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $modules = array_values(array_unique(array_values(ModuleAccess::ROUTE_MODULES)));
        sort($modules);

        $agencyIds = Agency::orderBy('id')->pluck('id')->map(fn ($v) => (int) $v)->all();
        $agencyIds[] = null;

        $mismatches = [];

        foreach ($agencyIds as $agencyId) {
            foreach (self::USER_TYPES as $userType) {
                foreach ($modules as $module) {
                    $key = (string) $agencyId.'.'.$userType.'.'.$module;
                    $legacy = LegacyModuleAccess::allows($userType, $module, $agencyId);

                    if ($legacy !== $baseline[$key]) {
                        $mismatches[] = "{$key}: baseline != legacy ".var_export($legacy, true);
                    }
                }
            }
        }

        $this->assertSame([], $mismatches, implode("\n", array_slice($mismatches, 0, 10)));
    }
}
