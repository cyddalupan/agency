<?php

namespace Tests\Feature\Fra;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Applicant;
use App\Models\Position;
use App\Models\StatusCode;
use App\Models\ApplicantPassport;
use App\Models\ApplicantWorkExperience;
use App\Models\Employer;
use App\Models\Agency;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class FraLineUpTabTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Employer $employer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency   = Agency::factory()->create();
        $this->employer = Employer::factory(['agency_id' => $this->agency->id])->create();

        // Seed the status_codes relevant to Line Up (0–6)
        $codes = [
            [0, 'PENDING',       'Pending',             '#6b7280'],
            [1, 'FOR INTERVIEW', 'Ready for interview', '#3498db'],
            [2, 'INTERVIEW',     'Interview',           '#9b59b6'],
            [3, 'FOR RESERVATION', 'For Reservation',   '#f39c12'],
            [4, 'RESERVED',      'Reserved',            '#2ecc71'],
            [5, 'FOR SELECTED',  'For Selected',        '#1abc9c'],
            [6, 'SELECTED',      'Selected',            '#27ae60'],
        ];

        foreach ($codes as [$code, $label, $desc, $color]) {
            StatusCode::create([
                'code'        => $code,
                'label'       => $label,
                'label_saudi' => $label,
                'description' => $desc,
                'color'       => $color,
            ]);
        }
    }

    protected function loginAsEmployer(): User
    {
        $user = User::factory(['agency_id' => $this->agency->id])
            ->create([
                'email'       => 'employer@test.com',
                'username'    => 'employer_user',
                'password'    => Hash::make('password-123'),
                'user_type'   => 'employer',
                'employer_id' => $this->employer->id,
                'status'      => 'active',
            ]);

        $this->post(route('fra.login.post'), [
            'login'    => 'employer_user',
            'password' => 'password-123',
        ]);

        return $user;
    }

    protected function createPosition(string $name): Position
    {
        return Position::create(['name' => $name]);
    }

    protected function createLineUpApplicant(array $overrides = []): Applicant
    {
        $attrs = array_merge([
            'agency_id'   => $this->agency->id,
            'first_name'  => 'Line',
            'last_name'   => 'UpTest',
            'position_id' => 1,
            'status_code' => 0,
            'employer_id' => null,
            'status'      => 'active',
        ], $overrides);

        $app = Applicant::create($attrs);

        // Ensure a dummy photo URL exists for testing
        if (empty($app->photo)) {
            $app->photo = 'https://ui-avatars.com/api/?name=' . urlencode($app->first_name . ' ' . $app->last_name) . '&background=29A1C4&color=fff&size=128';
            $app->save();
        }

        return $app;
    }

    // ─────────────────────────────────────────────────────
    // Page renders
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_page_renders(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.lineup'))
            ->assertOk()
            ->assertSee('Line Up');
    }

    // ─────────────────────────────────────────────────────
    // Summary / Badges
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_summary_shows_position_badges(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('Domestic Helper');
        $this->createLineUpApplicant([
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);

        $this->get(route('fra.lineup'))
            ->assertSee('DOMESTIC HELPER');
    }

    #[Test]
    public function lineup_summary_shows_count_per_position(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $this->createLineUpApplicant(['position_id' => $pos->id, 'status_code' => 0]);
        $this->createLineUpApplicant(['position_id' => $pos->id, 'status_code' => 1]);

        $this->get(route('fra.lineup'))
            ->assertSee('(2)');
    }

    // ─────────────────────────────────────────────────────
    // Filter by position
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_has_position_filter_buttons(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.lineup'))
            ->assertSee('All')
            ->assertSee('filter');
    }

    #[Test]
    public function lineup_filter_shows_only_position_matches(): void
    {
        $this->loginAsEmployer();
        $dh     = $this->createPosition('Domestic Helper');
        $driver = $this->createPosition('Driver');
        $this->createLineUpApplicant([
            'first_name' => 'HelperA', 'position_id' => $dh->id, 'status_code' => 0,
        ]);
        $this->createLineUpApplicant([
            'first_name' => 'DriverX', 'position_id' => $driver->id, 'status_code' => 0,
        ]);

        $response = $this->get(route('fra.lineup', ['position' => 'Domestic Helper']));
        $response->assertSee('HelperA');
        $response->assertDontSee('DriverX');
    }

    #[Test]
    public function lineup_all_filter_shows_everyone(): void
    {
        $this->loginAsEmployer();
        $dh     = $this->createPosition('DH');
        $driver = $this->createPosition('Driver');
        $this->createLineUpApplicant(['first_name' => 'A', 'position_id' => $dh->id, 'status_code' => 0]);
        $this->createLineUpApplicant(['first_name' => 'B', 'position_id' => $driver->id, 'status_code' => 0]);

        $response = $this->get(route('fra.lineup'));
        $response->assertSee('A');
        $response->assertSee('B');
    }

    // ─────────────────────────────────────────────────────
    // Export button
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_has_export_excel_button(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.lineup'))
            ->assertSee('Export')
            ->assertSee('Excel');
    }

    // ─────────────────────────────────────────────────────
    // Table layout
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_uses_table_layout(): void
    {
        $this->loginAsEmployer();
        $this->createLineUpApplicant(['position_id' => $this->createPosition('DH')->id]);
        $html = $this->get(route('fra.lineup'))->getContent();

        $this->assertStringContainsString('lc-table', $html);
        $this->assertStringContainsString('lc-table-wrap', $html);
    }

    #[Test]
    public function lineup_table_shows_applicant_info(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('Domestic Helper');
        $this->createLineUpApplicant([
            'first_name'  => 'Alice',
            'last_name'   => 'Wong',
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);

        $this->get(route('fra.lineup'))
            ->assertSee('Alice')
            ->assertSee('Wong')
            ->assertSee('Domestic Helper');
    }

    #[Test]
    public function lineup_shows_multiple_applicants(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');

        for ($i = 1; $i <= 3; $i++) {
            $this->createLineUpApplicant([
                'first_name'  => "Person{$i}",
                'last_name'   => 'Test',
                'position_id' => $pos->id,
                'status_code' => $i,
            ]);
        }

        $response = $this->get(route('fra.lineup'));
        $response->assertSee('Person1');
        $response->assertSee('Person2');
        $response->assertSee('Person3');
    }

    #[Test]
    public function lineup_filters_to_lineup_codes(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $this->createLineUpApplicant([
            'first_name'  => 'InLineUp',
            'last_name'   => 'Yes',
            'position_id' => $pos->id,
            'status_code' => 3,
        ]);
        Applicant::create([
            'agency_id'   => $this->agency->id,
            'first_name'  => 'Outside',
            'last_name'   => 'No',
            'position_id' => $pos->id,
            'status_code' => 7,
            'status'      => 'active',
        ]);

        $response = $this->get(route('fra.lineup'));
        $response->assertSee('InLineUp');
        $response->assertDontSee('Outside');
    }

    // ─────────────────────────────────────────────────────
    // Status badge
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_shows_status_code_label(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $this->createLineUpApplicant([
            'position_id' => $pos->id,
            'status_code' => 1,
        ]);

        $this->get(route('fra.lineup'))
            ->assertSee('Ready for interview');
    }

    #[Test]
    public function lineup_shows_status_color(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $this->createLineUpApplicant([
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);

        $this->get(route('fra.lineup'))
            ->assertSee('#6b7280');
    }

    // ─────────────────────────────────────────────────────
    // Firstimer / Exabroad badges
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_shows_firstimer_badge_when_no_experience(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $this->createLineUpApplicant([
            'first_name'  => 'Newbie',
            'last_name'   => 'User',
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);

        $this->get(route('fra.lineup'))
            ->assertSee(__('messages.firstimer'));
    }

    #[Test]
    public function lineup_shows_exabroad_badge_when_has_experience(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $app = $this->createLineUpApplicant([
            'first_name'  => 'Ex',
            'last_name'   => 'Abroad',
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);
        ApplicantWorkExperience::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $app->id,
            'position'     => 'DH',
            'date_from'    => '2020-01-01',
            'date_to'      => '2022-01-01',
        ]);

        $this->get(route('fra.lineup'))
            ->assertSee(__('messages.exabroad'));
    }

    // ─────────────────────────────────────────────────────
    // Passport on card
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_shows_passport_number(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $app = $this->createLineUpApplicant([
            'first_name'  => 'Passport',
            'last_name'   => 'Holder',
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);
        ApplicantPassport::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $app->id,
            'passport_no'  => 'AB987654',
        ]);

        $this->get(route('fra.lineup'))
            ->assertSee('AB987654');
    }

    // ─────────────────────────────────────────────────────
    // Action buttons
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_shows_action_buttons(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $this->createLineUpApplicant([
            'first_name'  => 'Action',
            'last_name'   => 'Item',
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);

        $this->get(route('fra.lineup'))
            ->assertSee('Select')
            ->assertSee('View');
    }

    #[Test]
    public function lineup_select_updates_status_to_reserved(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $app = $this->createLineUpApplicant([
            'first_name'  => 'PickMe',
            'last_name'   => 'Test',
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);

        $this->post(route('fra.lineup.select', $app))
            ->assertRedirect(route('fra.lineup'));

        $this->assertEquals(4, $app->fresh()->status_code,
            'Select should update status_code to 4 (RESERVED)');
    }

    #[Test]
    public function lineup_select_requires_authentication(): void
    {
        $pos = $this->createPosition('DH');
        $app = $this->createLineUpApplicant([
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);

        $this->post(route('fra.lineup.select', $app))
            ->assertRedirect(route('fra.login'));
    }

    #[Test]
    public function lineup_view_shows_applicant_details(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $app = $this->createLineUpApplicant([
            'first_name'  => 'ViewMe',
            'last_name'   => 'Test',
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);

        $this->get(route('fra.lineup.view', $app))
            ->assertOk()
            ->assertSee('ViewMe')
            ->assertSee('Test')
            ->assertSee('DH');
    }

    #[Test]
    public function lineup_view_requires_authentication(): void
    {
        $pos = $this->createPosition('DH');
        $app = $this->createLineUpApplicant([
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);

        $this->get(route('fra.lineup.view', $app))
            ->assertRedirect(route('fra.login'));
    }

    // ─────────────────────────────────────────────────────
    // Row field completeness
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_row_has_complete_fields(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $app = $this->createLineUpApplicant([
            'first_name'  => 'Full',
            'last_name'   => 'Details',
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);
        ApplicantPassport::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $app->id,
            'passport_no'  => 'AB987654',
        ]);

        $this->get(route('fra.lineup'))
            ->assertSee('Full')
            ->assertSee('Details')
            ->assertSee('AB987654')
            ->assertSee('DH')
            ->assertSee('Pending');
    }

    #[Test]
    public function lineup_uses_table_responsive_wrapper(): void
    {
        $this->loginAsEmployer();
        $html = $this->get(route('fra.lineup'))->getContent();

        $this->assertTrue(
            str_contains($html, 'table'),
            'Layout should wrap table in a responsive wrapper'
        );
    }

    // ─────────────────────────────────────────────────────
    // Applicant photo
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_table_photo_column_exists(): void
    {
        $this->loginAsEmployer();
        $this->createLineUpApplicant(['position_id' => $this->createPosition('DH')->id]);
        $html = $this->get(route('fra.lineup'))->getContent();

        $this->assertStringContainsString('photo', strtolower($html),
            'Table should have a photo column');
    }

    // ─────────────────────────────────────────────────────
    // Export (unchanged)
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_export_returns_csv(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('Domestic Helper');
        $app = $this->createLineUpApplicant([
            'first_name'  => 'Alice',
            'last_name'   => 'Wong',
            'position_id' => $pos->id,
            'status_code' => 0,
        ]);
        ApplicantPassport::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $app->id,
            'passport_no'  => 'P1234567',
        ]);

        $this->get(route('fra.lineup.export'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function lineup_export_has_csv_headers(): void
    {
        $this->loginAsEmployer();
        $response = $this->get(route('fra.lineup.export'));
        $content  = $response->getContent();
        $this->assertStringContainsString('Name', $content);
        $this->assertStringContainsString('Position', $content);
        $this->assertStringContainsString('Passport', $content);
        $this->assertStringContainsString('Experience', $content);
        $this->assertStringContainsString('Status', $content);
    }

    #[Test]
    public function lineup_export_filters_to_lineup_only(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $this->createLineUpApplicant([
            'first_name'  => 'InLineUp',
            'last_name'   => 'Yes',
            'position_id' => $pos->id,
            'status_code' => 3,
        ]);
        Applicant::create([
            'agency_id'   => $this->agency->id,
            'first_name'  => 'Outside',
            'last_name'   => 'No',
            'position_id' => $pos->id,
            'status_code' => 7,
            'status'      => 'active',
        ]);

        $response = $this->get(route('fra.lineup.export'));
        $this->assertStringContainsString('InLineUp', $response->getContent());
        $this->assertStringNotContainsString('Outside', $response->getContent());
    }
}
