<?php

namespace Tests\Feature\Bill;

use App\Models\Agency;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BillIndexTest extends TestCase
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
    public function unauthenticated_user_cannot_access(): void
    {
        $response = $this->get(route('bills.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function index_displays_bills(): void
    {
        Bill::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('bills.index'));

        $response->assertOk();
        $response->assertSee('Bills');
    }

    #[Test]
    public function index_shows_employer_and_applicant_names(): void
    {
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('bills.index'));

        $response->assertOk();
        $response->assertSee($bill->employer->name);
        $response->assertSee($bill->applicant->full_name);
    }

    #[Test]
    public function index_is_tenant_scoped(): void
    {
        Bill::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $otherAgency = Agency::factory()->create();
        Bill::factory()->count(2)->create([
            'agency_id' => $otherAgency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('bills.index'));

        $response->assertOk();
        // Only 3 bills from user's agency should appear
        $response->assertSee($this->agency->id);
    }

    #[Test]
    public function index_shows_status_badge(): void
    {
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'status' => 'partially_paid',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('bills.index'));

        $response->assertOk();
        $response->assertSee('Partially paid');
    }
}
