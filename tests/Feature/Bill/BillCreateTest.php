<?php

namespace Tests\Feature\Bill;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BillCreateTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_create(): void
    {
        $response = $this->get(route('bills.create'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function create_form_displays(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('bills.create'));

        $response->assertOk();
        $response->assertSee('Create Bill');
    }

    #[Test]
    public function store_creates_bill(): void
    {
        $employer = Employer::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->post(route('bills.store'), [
                'employer_id' => $employer->id,
                'applicant_id' => $applicant->id,
                'employer_cost' => 50000,
                'applicant_cost' => 5000,
                'employer_deposit' => 10000,
                'applicant_deposit' => 2000,
                'notes' => 'Test bill',
            ]);

        $response->assertRedirect(route('bills.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bills', [
            'agency_id' => $this->agency->id,
            'employer_id' => $employer->id,
            'applicant_id' => $applicant->id,
            'employer_cost' => 50000,
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function store_requires_employer(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('bills.store'), [
                'applicant_id' => 1,
                'employer_cost' => 50000,
            ]);

        $response->assertSessionHasErrors('employer_id');
    }

    #[Test]
    public function store_auto_sets_agency_id(): void
    {
        $employer = Employer::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->user)
            ->post(route('bills.store'), [
                'employer_id' => $employer->id,
                'applicant_id' => $applicant->id,
                'employer_cost' => 75000,
                'applicant_cost' => 7500,
            ]);

        $this->assertDatabaseHas('bills', [
            'employer_id' => $employer->id,
            'agency_id' => $this->agency->id,
        ]);
    }
}
