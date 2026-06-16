<?php

namespace Tests\Feature\CustomField;

use App\Models\Agency;
use App\Models\CustomFieldDefinition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomFieldDeleteTest extends TestCase
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
        $field = CustomFieldDefinition::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->delete(route('custom-fields.destroy', $field));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function delete_removes_field(): void
    {
        $field = CustomFieldDefinition::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('custom-fields.destroy', $field));

        $response->assertRedirect(route('custom-fields.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('custom_field_definitions', ['id' => $field->id]);
    }

    #[Test]
    public function delete_removes_associated_values(): void
    {
        $field = CustomFieldDefinition::factory()->create(['agency_id' => $this->agency->id]);
        $value = \App\Models\CustomFieldValue::factory()->create([
            'custom_field_definition_id' => $field->id,
        ]);

        $this->actingAs($this->user)
            ->delete(route('custom-fields.destroy', $field));

        $this->assertDatabaseMissing('custom_field_values', ['id' => $value->id]);
    }
}
