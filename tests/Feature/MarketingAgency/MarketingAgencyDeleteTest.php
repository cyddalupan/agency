<?php

namespace Tests\Feature\MarketingAgency;

use App\Models\Agency;
use App\Models\MarketingAgency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarketingAgencyDeleteTest extends TestCase
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
        $marketingAgency = MarketingAgency::factory()->create();
        $response = $this->delete(route('marketing-agencies.destroy', $marketingAgency));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function destroy_deletes_agency(): void
    {
        $marketingAgency = MarketingAgency::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('marketing-agencies.destroy', $marketingAgency));

        $response->assertRedirect(route('marketing-agencies.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('marketing_agencies', ['id' => $marketingAgency->id]);
    }

    #[Test]
    public function destroy_returns_success_message(): void
    {
        $marketingAgency = MarketingAgency::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Delete Me',
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('marketing-agencies.destroy', $marketingAgency));

        $response->assertSessionHas('success', 'Marketing agency deleted successfully.');
    }
}
