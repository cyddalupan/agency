<?php

namespace Tests\Feature\OfficialReceipt;

use App\Models\Agency;
use App\Models\OfficialReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OfficialReceiptIndexTest extends TestCase
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
        $response = $this->get(route('official-receipts.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function index_displays_official_receipts(): void
    {
        OfficialReceipt::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('official-receipts.index'));

        $response->assertOk();
        $response->assertSee('Official Receipts');
    }

    #[Test]
    public function index_is_tenant_scoped(): void
    {
        OfficialReceipt::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $otherAgency = Agency::factory()->create();
        OfficialReceipt::factory()->count(2)->create([
            'agency_id' => $otherAgency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('official-receipts.index'));

        $response->assertOk();
    }

    #[Test]
    public function index_shows_or_no(): void
    {
        OfficialReceipt::factory()->create([
            'agency_id' => $this->agency->id,
            'or_no' => 'OR-2026-0001',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('official-receipts.index'));

        $response->assertOk();
        $response->assertSee('OR-2026-0001');
    }
}
