<?php

namespace Tests\Feature\OfficialReceipt;

use App\Models\Agency;
use App\Models\OfficialReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OfficialReceiptDeleteTest extends TestCase
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
    public function unauthenticated_user_cannot_delete(): void
    {
        $or = OfficialReceipt::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->delete(route('official-receipts.destroy', $or));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function delete_removes_official_receipt(): void
    {
        $or = OfficialReceipt::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('official-receipts.destroy', $or));

        $response->assertRedirect(route('official-receipts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('official_receipts', ['id' => $or->id]);
    }
}
