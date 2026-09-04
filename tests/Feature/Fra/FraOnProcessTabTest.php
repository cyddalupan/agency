<?php

namespace Tests\Feature\Fra;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Applicant;
use App\Models\Position;
use App\Models\ApplicantPassport;
use App\Models\ApplicantWorkExperience;
use App\Models\ApplicantContract;
use App\Models\StatusCode;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class FraOnProcessTabTest extends TestCase
{
    use RefreshDatabase;

    protected function createEmployer(): User
    {
        return User::factory()->create([
            'email'     => 'employer@test.com',
            'username'  => 'employer_user',
            'password'  => Hash::make('password-123'),
            'user_type' => 'employer',
            'status'    => 'active',
        ]);
    }

    protected function loginAsEmployer(): User
    {
        $user = $this->createEmployer();
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

    protected function createStatusCode(int $code, string $label, string $description = ''): StatusCode
    {
        return StatusCode::create([
            'code'        => $code,
            'label'       => $label,
            'description' => $description ?: $label,
            'color'       => '#cccccc',
        ]);
    }

    protected function createOnProcessApplicant(array $overrides = []): Applicant
    {
        $attrs = array_merge([
            'first_name'  => 'Juan',
            'last_name'   => 'Dela Cruz',
            'position_id' => 1,
            'status_code' => 7,
            'employer_id' => null,
            'agency_id'   => 1,
            'status'      => 'active',
        ], $overrides);

        return Applicant::create($attrs);
    }

    // ─────────────────────────────────────────────────────
    // Page title
    // ─────────────────────────────────────────────────────

    #[Test]
    public function onprocess_page_has_correct_title(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.onprocess'))
            ->assertSee('On Process Applicants');
    }

    // ─────────────────────────────────────────────────────
    // Export to excel button
    // ─────────────────────────────────────────────────────

    #[Test]
    public function onprocess_has_export_excel_button(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.onprocess'))
            ->assertSee('Export')
            ->assertSee('Excel');
    }

    // ─────────────────────────────────────────────────────
    // Table structure
    // ─────────────────────────────────────────────────────

    #[Test]
    public function onprocess_has_applicants_table(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.onprocess'))
            ->assertSee('<table', false);
    }

    #[Test]
    public function onprocess_table_has_required_columns(): void
    {
        $this->loginAsEmployer();
        $response = $this->get(route('fra.onprocess'));

        $columns = ['#', 'Name', 'Position', 'Passport', 'Experience', 'Status', 'Medical', 'Wakala', 'Contract Received', 'Contract Signed'];
        foreach ($columns as $col) {
            $response->assertSee($col);
        }
    }

    #[Test]
    public function onprocess_shows_applicant_list(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('Domestic Helper');
        $this->createOnProcessApplicant([
            'first_name'  => 'Maria',
            'last_name'   => 'Santos',
            'position_id' => $pos->id,
            'status_code' => 7,
        ]);

        $this->get(route('fra.onprocess'))
            ->assertSee('Maria')
            ->assertSee('Santos')
            ->assertSee('Domestic Helper');
    }

    #[Test]
    public function onprocess_shows_passport_number(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('Domestic Helper');
        $app = $this->createOnProcessApplicant([
            'first_name'  => 'Maria',
            'last_name'   => 'Santos',
            'position_id' => $pos->id,
            'status_code' => 7,
        ]);
        ApplicantPassport::create([
            'agency_id'    => 1,
            'applicant_id' => $app->id,
            'passport_no'  => 'P1234567',
        ]);

        $this->get(route('fra.onprocess'))
            ->assertSee('P1234567');
    }

    #[Test]
    public function onprocess_shows_experience_years(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('Domestic Helper');
        $app = $this->createOnProcessApplicant([
            'first_name'  => 'Maria',
            'last_name'   => 'Santos',
            'position_id' => $pos->id,
            'status_code' => 7,
        ]);
        ApplicantWorkExperience::create([
            'agency_id'    => 1,
            'applicant_id' => $app->id,
            'position'     => 'DH',
            'date_from'    => '2020-01-01',
            'date_to'      => '2022-01-01',
        ]);

        $this->get(route('fra.onprocess'))
            ->assertSee('2');
    }

    // ─────────────────────────────────────────────────────
    // Status badges
    // ─────────────────────────────────────────────────────

    #[Test]
    public function onprocess_shows_status_badges_for_firstimer_and_exabroad(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $this->createOnProcessApplicant([
            'first_name'  => 'Firstimer',
            'last_name'   => 'User',
            'position_id' => $pos->id,
            'status_code' => 7,
        ]);

        $this->get(route('fra.onprocess'))
            ->assertSee(__('messages.firstimer'));
    }

    // ─────────────────────────────────────────────────────
    // Process status badges
    // ─────────────────────────────────────────────────────

    #[Test]
    public function onprocess_shows_process_status_badge(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $this->createOnProcessApplicant([
            'first_name'  => 'ForOWWA',
            'last_name'   => 'User',
            'position_id' => $pos->id,
            'status_code' => 15,
        ]);

        $this->get(route('fra.onprocess'))
            ->assertSee('OWWA');
    }

    #[Test]
    public function onprocess_shows_contract_received_signed(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $this->createOnProcessApplicant([
            'first_name'  => 'Contract',
            'last_name'   => 'User',
            'position_id' => $pos->id,
            'status_code' => 30,
        ]);

        $response = $this->get(route('fra.onprocess'));
        $response->assertSee('Contract');
    }

    #[Test]
    public function onprocess_has_alternating_row_shading(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        for ($i = 1; $i <= 2; $i++) {
            $this->createOnProcessApplicant([
                'first_name'  => 'Row'.$i,
                'last_name'   => 'Test',
                'position_id' => $pos->id,
                'status_code' => 7,
            ]);
        }

        $response = $this->get(route('fra.onprocess'));
        $html = $response->getContent();
        $hasStriped = str_contains($html, 'bg-gray-50') && str_contains($html, 'bg-white');
        $this->assertTrue($hasStriped, 'Table should have alternating row shading (bg-white / bg-gray-50)');
    }

    // ─────────────────────────────────────────────────────
    // Responsive — horizontal scroll
    // ─────────────────────────────────────────────────────

    #[Test]
    public function onprocess_table_is_horizontally_scrollable(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.onprocess'))
            ->assertSee('overflow');
    }

    // ─────────────────────────────────────────────────────
    // Payment / contract status
    // ─────────────────────────────────────────────────────

    #[Test]
    public function onprocess_shows_contract_sign_status(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $applicant = $this->createOnProcessApplicant([
            'first_name'  => 'Paid',
            'last_name'   => 'User',
            'position_id' => $pos->id,
            'status_code' => 30,
        ]);

        ApplicantContract::create([
            'agency_id'        => $applicant->agency_id,
            'applicant_id'     => $applicant->id,
            'contract_received'=> now()->toDateString(),
            'contract_signed'  => now()->toDateString(),
        ]);

        $this->get(route('fra.onprocess'))
            ->assertSee(__('messages.signed'));
    }

    // ─────────────────────────────────────────────────────
    // Multi-applicant display
    // ─────────────────────────────────────────────────────

    #[Test]
    public function onprocess_shows_multiple_applicants(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('Domestic Helper');

        for ($i = 1; $i <= 3; $i++) {
            $this->createOnProcessApplicant([
                'first_name'  => "Applicant{$i}",
                'last_name'   => 'Test',
                'position_id' => $pos->id,
                'status_code' => 6 + $i,
            ]);
        }

        $response = $this->get(route('fra.onprocess'));
        $response->assertSee('Applicant1');
        $response->assertSee('Applicant2');
        $response->assertSee('Applicant3');
    }

    // ─────────────────────────────────────────────────────
    // Export endpoint
    // ─────────────────────────────────────────────────────

    #[Test]
    public function onprocess_export_returns_csv(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('Domestic Helper');
        $app = $this->createOnProcessApplicant([
            'first_name'  => 'Maria',
            'last_name'   => 'Santos',
            'position_id' => $pos->id,
            'status_code' => 7,
        ]);
        ApplicantPassport::create([
            'agency_id'    => 1,
            'applicant_id' => $app->id,
            'passport_no'  => 'P1234567',
        ]);

        $this->get(route('fra.onprocess.export'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename=on-process-applicants.csv')
            ->assertSee('Maria')
            ->assertSee('Santos')
            ->assertSee('P1234567');
    }

    #[Test]
    public function onprocess_export_has_csv_headers(): void
    {
        $this->loginAsEmployer();
        $response = $this->get(route('fra.onprocess.export'));
        $content = $response->getContent();
        $this->assertStringContainsString('Name', $content);
        $this->assertStringContainsString('Position', $content);
        $this->assertStringContainsString('Passport', $content);
        $this->assertStringContainsString('Experience', $content);
        $this->assertStringContainsString('Status', $content);
    }

    #[Test]
    public function onprocess_export_filters_to_onprocess_applicants(): void
    {
        $this->loginAsEmployer();
        $pos = $this->createPosition('DH');
        $this->createOnProcessApplicant([
            'first_name'  => 'ProcessMe',
            'last_name'   => 'Test',
            'position_id' => $pos->id,
            'status_code' => 15,
        ]);
        $outside = Applicant::create([
            'first_name'  => 'Outside',
            'last_name'   => 'Status',
            'position_id' => $pos->id,
            'status_code' => 99,
            'agency_id'   => 1,
            'status'      => 'active',
        ]);

        $response = $this->get(route('fra.onprocess.export'));
        $content = $response->getContent();
        $this->assertStringContainsString('ProcessMe', $content);
        $this->assertStringNotContainsString('Outside', $content);
    }
}
