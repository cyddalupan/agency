<?php

namespace Tests\Feature\CustomField;

use App\Models\Agency;
use App\Models\CustomFieldDefinition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomFieldEditTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private CustomFieldDefinition $field;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->field = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'TIN',
            'type' => 'text',
            'model_type' => 'Employer',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_edit(): void
    {
        $response = $this->get(route('custom-fields.edit', $this->field));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function edit_form_displays(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('custom-fields.edit', $this->field));

        $response->assertOk();
        $response->assertSee('Edit Custom Field');
    }

    #[Test]
    public function update_saves_changes(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('custom-fields.update', $this->field), [
                'model_type' => 'Employer',
                'name' => 'TIN Number Updated',
                'type' => 'text',
            ]);

        $response->assertRedirect(route('custom-fields.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('custom_field_definitions', [
            'id' => $this->field->id,
            'name' => 'TIN Number Updated',
            'key' => $this->field->key, // unchanged
        ]);
    }

    #[Test]
    public function update_requires_name(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('custom-fields.update', $this->field), []);

        $response->assertSessionHasErrors(['name']);
    }
}
