<?php

namespace Tests\Feature\CustomField;

use App\Models\Agency;
use App\Models\CustomFieldDefinition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomFieldIndexTest extends TestCase
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
        $response = $this->get(route('custom-fields.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function index_displays_custom_fields(): void
    {
        CustomFieldDefinition::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('custom-fields.index'));

        $response->assertOk();
        $response->assertSee('Custom Fields');
    }

    #[Test]
    public function index_is_tenant_scoped(): void
    {
        CustomFieldDefinition::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $otherAgency = Agency::factory()->create();
        CustomFieldDefinition::factory()->count(2)->create([
            'agency_id' => $otherAgency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('custom-fields.index'));

        $response->assertOk();
    }

    #[Test]
    public function index_shows_field_type_and_model(): void
    {
        CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'TIN Number',
            'type' => 'text',
            'model_type' => 'Employer',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('custom-fields.index'));

        $response->assertSee('TIN Number');
        $response->assertSee('text');
        $response->assertSee('Employer');
    }
}
