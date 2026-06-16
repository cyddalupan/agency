<?php

namespace Tests\Feature\Bill;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Bill;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BillEditTest extends TestCase
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
    public function unauthenticated_user_cannot_edit(): void
    {
        $bill = Bill::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->get(route('bills.edit', $bill));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function edit_form_displays(): void
    {
        $bill = Bill::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->get(route('bills.edit', $bill));

        $response->assertOk();
        $response->assertSee('Edit Bill');
    }

    #[Test]
    public function update_saves_changes(): void
    {
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_cost' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('bills.update', $bill), [
                'employer_id' => $bill->employer_id,
                'applicant_id' => $bill->applicant_id,
                'employer_cost' => 75000,
                'applicant_cost' => $bill->applicant_cost,
            ]);

        $response->assertRedirect(route('bills.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'employer_cost' => 75000,
        ]);
    }

    #[Test]
    public function update_requires_employer(): void
    {
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('bills.update', $bill), [
                'employer_cost' => 75000,
            ]);

        $response->assertSessionHasErrors('employer_id');
    }
}
