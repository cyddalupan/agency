<?php

namespace Tests\Feature\MarketingAgency;

use App\Models\Agency;
use App\Models\MarketingAgency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarketingAgencyEditTest extends TestCase
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
        $marketingAgency = MarketingAgency::factory()->create();
        $response = $this->get(route('marketing-agencies.edit', $marketingAgency));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function edit_form_displays(): void
    {
        $marketingAgency = MarketingAgency::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Edit Me Agency',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.edit', $marketingAgency));

        $response->assertOk();
        $response->assertSee('Edit Marketing Agency');
        $response->assertSee('Edit Me Agency');
    }

    #[Test]
    public function edit_form_has_loaded_values(): void
    {
        $marketingAgency = MarketingAgency::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Update Corp',
            'commission_rate' => 20.5,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.edit', $marketingAgency));

        $response->assertSee('Update Corp');
        $response->assertSee('20.5');
    }

    #[Test]
    public function update_saves_changes(): void
    {
        $marketingAgency = MarketingAgency::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Old Name',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('marketing-agencies.update', $marketingAgency), [
                'name' => 'New Name',
                'commission_rate' => 25,
            ]);

        $response->assertRedirect(route('marketing-agencies.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('marketing_agencies', [
            'id' => $marketingAgency->id,
            'name' => 'New Name',
            'commission_rate' => 25.00,
        ]);
    }

    #[Test]
    public function update_validates_required_fields(): void
    {
        $marketingAgency = MarketingAgency::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('marketing-agencies.update', $marketingAgency), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }
}
