<?php

namespace Tests\Feature\Position;

use App\Models\Agency;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Card 6af82438, part 2: "Add new Category for Professional on the Position
 * module in settings".
 */
class ProfessionalPositionCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        $agency = Agency::factory()->create(['subdomain' => 'finas', 'name' => 'FINAS']);

        return User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_the_position_form_offers_the_professional_category(): void
    {
        $html = $this->actingAs($this->user())
            ->get(route('positions.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Professional', $html);
        $this->assertStringContainsString('value="professional"', $html);
    }

    public function test_a_position_can_be_saved_as_professional(): void
    {
        $this->actingAs($this->user())
            ->post(route('positions.store'), [
                'name' => 'Registered Nurse',
                'category' => Position::CATEGORY_PROFESSIONAL,
                'description' => 'Hospital nursing',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('positions', [
            'name' => 'Registered Nurse',
            'category' => Position::CATEGORY_PROFESSIONAL,
        ]);
    }

    public function test_the_professional_scope_selects_only_that_category(): void
    {
        Position::create(['name' => 'Nurse', 'category' => Position::CATEGORY_PROFESSIONAL]);
        Position::create(['name' => 'Maid', 'category' => Position::CATEGORY_HOUSEHOLD]);
        Position::create(['name' => 'Welder', 'category' => Position::CATEGORY_SKILLED]);

        $names = Position::professional()->pluck('name')->all();

        $this->assertSame(['Nurse'], $names);
    }

    public function test_the_positions_list_shows_a_professional_badge(): void
    {
        Position::create(['name' => 'Pharmacist', 'category' => Position::CATEGORY_PROFESSIONAL]);

        $html = $this->actingAs($this->user())
            ->get(route('positions.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Professional', $html);
    }
}
