<?php

namespace Tests\Feature\OfficialReceipt;

use App\Models\Agency;
use App\Models\OfficialReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OfficialReceiptShowTest extends TestCase
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
        $or = OfficialReceipt::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->get(route('official-receipts.show', $or));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_displays_official_receipt_details(): void
    {
        $or = OfficialReceipt::factory()->create([
            'agency_id' => $this->agency->id,
            'or_no' => 'OR-2026-0001',
            'amount' => 25000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('official-receipts.show', $or));

        $response->assertOk();
        $response->assertSee('OR-2026-0001');
        $response->assertSee(number_format(25000, 2));
        $response->assertSee($or->issued_to_name);
    }

    #[Test]
    public function show_shows_back_link(): void
    {
        $or = OfficialReceipt::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->get(route('official-receipts.show', $or));

        $response->assertOk();
        $response->assertSee('Back');
    }

    #[Test]
    public function show_has_pdf_download_link(): void
    {
        $or = OfficialReceipt::factory()->create([
            'agency_id' => $this->agency->id,
            'or_no' => 'OR-2026-0001',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('official-receipts.show', $or));

        $response->assertOk();
        $response->assertSee(route('reports.or', $or));
    }
}
