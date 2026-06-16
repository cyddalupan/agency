<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantCreateTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_create_form(): void
    {
        $response = $this->get(route('applicants.create'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function create_form_displays_correctly(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertOk();
        $response->assertSee('Add Applicant');
        $response->assertSee('first_name');
        $response->assertSee('last_name');
        $response->assertSee('email');
    }

    #[Test]
    public function store_requires_basic_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), []);

        $response->assertSessionHasErrors(['first_name', 'last_name']);
    }

    #[Test]
    public function store_creates_applicant(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name' => 'Maria',
                'last_name'  => 'Santos',
                'email'      => 'maria@example.com',
                'contact'    => '09171234567',
                'gender'     => 'female',
                'birthdate'  => '1995-06-15',
            ]);

        $response->assertRedirect(route('applicants.index'));
        $this->assertDatabaseHas('applicants', [
            'agency_id'  => $this->agency->id,
            'first_name' => 'Maria',
            'last_name'  => 'Santos',
        ]);
    }

    #[Test]
    public function store_creates_applicant_with_default_pending_status(): void
    {
        $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name' => 'Juan',
                'last_name'  => 'Dela Cruz',
            ]);

        $this->assertDatabaseHas('applicants', [
            'first_name'  => 'Juan',
            'status_code' => 0,
        ]);
    }

    #[Test]
    public function store_accepts_optional_fields(): void
    {
        $data = [
            'first_name'   => 'Juan',
            'last_name'    => 'Dela Cruz',
            'middle_name'  => 'Santos',
            'suffix'       => 'Jr.',
            'email'        => 'jdc@example.com',
            'contact'      => '09990000001',
            'gender'       => 'male',
            'birthdate'    => '1990-01-01',
            'address'      => '123 Rizal St, Manila',
            'remarks'      => 'Test applicant',
            'source'       => 'Facebook',
        ];

        $this->actingAs($this->user)
            ->post(route('applicants.store'), $data);

        $this->assertDatabaseHas('applicants', [
            'agency_id'   => $this->agency->id,
            'first_name'  => 'Juan',
            'middle_name' => 'Santos',
            'suffix'      => 'Jr.',
            'source'      => 'Facebook',
        ]);
    }
}
