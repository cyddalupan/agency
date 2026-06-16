<?php

namespace Tests\Feature\Bill;

use App\Models\Agency;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BillShowTest extends TestCase
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
    public function unauthenticated_user_cannot_view(): void
    {
        $bill = Bill::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->get(route('bills.show', $bill));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_displays_bill_details(): void
    {
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_cost' => 100000,
            'applicant_cost' => 10000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('bills.show', $bill));

        $response->assertOk();
        $response->assertSee(number_format(100000, 2));
        $response->assertSee($bill->employer->name);
        $response->assertSee($bill->applicant->full_name);
    }

    #[Test]
    public function show_shows_edit_button(): void
    {
        $bill = Bill::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->get(route('bills.show', $bill));

        $response->assertOk();
        $response->assertSee('Edit');
    }

    #[Test]
    public function show_shows_back_link(): void
    {
        $bill = Bill::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->get(route('bills.show', $bill));

        $response->assertOk();
        $response->assertSee('Back');
    }
}
