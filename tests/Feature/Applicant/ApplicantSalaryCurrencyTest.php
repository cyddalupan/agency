<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "LANDAS: Add Applicant 'Currency'" (Kchanz Josh, 2026-11-05):
 * "Add select currency to the salary".
 *
 * The Salary field on the applicant form now carries an ISO currency select so
 * an overseas salary (SAR 1,500) is no longer stored/printed as pesos.
 */
class ApplicantSalaryCurrencyTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create(['name' => 'Gulf']);
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name'      => 'Juan',
            'last_name'       => 'Dela Cruz',
            'applicant_type'  => Applicant::TYPE_SKILLED,
            'expected_salary' => 1500,
            'expected_salary_currency' => 'SAR',
        ], $overrides);
    }

    #[Test]
    public function create_form_offers_a_currency_select_next_to_salary(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="expected_salary_currency"', $html);
        $this->assertStringContainsString('name="expected_salary"', $html);

        foreach (['PHP', 'USD', 'SAR', 'AED', 'QAR'] as $code) {
            $this->assertMatchesRegularExpression(
                '/<option value="'.$code.'"[^>]*>/',
                $html,
                "Currency {$code} missing from the salary select."
            );
        }

        // PHP is the default so existing agencies keep their behaviour.
        $this->assertMatchesRegularExpression('/<option value="PHP"\s+selected/', $html);
    }

    #[Test]
    public function store_saves_the_selected_currency(): void
    {
        $this->actingAs($this->user)
            ->post(route('applicants.store'), $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('applicants', [
            'agency_id'   => $this->agency->id,
            'first_name'  => 'Juan',
            'last_name'   => 'Dela Cruz',
            'expected_salary' => 1500.00,
            'expected_salary_currency' => 'SAR',
        ]);
    }

    #[Test]
    public function store_defaults_to_php_when_no_currency_is_sent(): void
    {
        $payload = $this->payload();
        unset($payload['expected_salary_currency']);

        $this->actingAs($this->user)
            ->post(route('applicants.store'), $payload)
            ->assertRedirect();

        $applicant = Applicant::where('first_name', 'Juan')->firstOrFail();
        $this->assertSame('PHP', $applicant->expected_salary_currency);
    }

    #[Test]
    public function unsupported_currency_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->from(route('applicants.create'))
            ->post(route('applicants.store'), $this->payload(['expected_salary_currency' => 'XYZ']))
            ->assertSessionHasErrors('expected_salary_currency');

        $this->assertDatabaseCount('applicants', 0);
    }

    #[Test]
    public function edit_form_preselects_the_stored_currency(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'expected_salary' => 900,
            'expected_salary_currency' => 'AED',
        ]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.edit', $applicant))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/<option value="AED"\s+selected/', $html);
    }

    #[Test]
    public function update_changes_the_currency(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'first_name'  => 'Maria',
            'last_name'   => 'Santos',
            'expected_salary' => 500,
            'expected_salary_currency' => 'PHP',
        ]);

        $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), $this->payload([
                'first_name' => 'Maria',
                'last_name'  => 'Santos',
                'expected_salary_currency' => 'KWD',
                'expected_salary' => 220,
            ]))
            ->assertRedirect();

        $this->assertSame('KWD', $applicant->fresh()->expected_salary_currency);
    }
}
