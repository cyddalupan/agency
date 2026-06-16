<?php

namespace Tests\Feature\CustomField;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomFieldCreateTest extends TestCase
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
    public function unauthenticated_user_cannot_access_create(): void
    {
        $response = $this->get(route('custom-fields.create'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function create_form_displays(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('custom-fields.create'));

        $response->assertOk();
        $response->assertSee('New Custom Field');
    }

    #[Test]
    public function store_creates_field(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('custom-fields.store'), [
                'model_type' => 'Employer',
                'name' => 'TIN Number',
                'type' => 'text',
            ]);

        $response->assertRedirect(route('custom-fields.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('custom_field_definitions', [
            'agency_id' => $this->agency->id,
            'model_type' => 'Employer',
            'name' => 'TIN Number',
            'key' => 'tin-number',
            'type' => 'text',
        ]);
    }

    #[Test]
    public function store_requires_name_and_type(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('custom-fields.store'), []);

        $response->assertSessionHasErrors(['name', 'type']);
    }

    #[Test]
    public function store_auto_sets_agency_id(): void
    {
        $this->actingAs($this->user)
            ->post(route('custom-fields.store'), [
                'model_type' => 'Employer',
                'name' => 'Business Type',
                'type' => 'select',
                'options' => "Retail\nService\nManufacturing",
            ]);

        $this->assertDatabaseHas('custom_field_definitions', [
            'name' => 'Business Type',
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function store_with_select_options(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('custom-fields.store'), [
                'model_type' => 'Employer',
                'name' => 'Category',
                'type' => 'select',
                'options' => "Premium\nStandard\nBasic",
            ]);

        $response->assertSessionHasNoErrors();

        $field = \App\Models\CustomFieldDefinition::where('name', 'Category')->first();
        $this->assertNotNull($field);
        $this->assertEquals(['Premium', 'Standard', 'Basic'], $field->options);
    }

    #[Test]
    public function store_with_required(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('custom-fields.store'), [
                'model_type' => 'Employer',
                'name' => 'Registration No',
                'type' => 'text',
                'required' => '1',
            ]);

        $this->assertDatabaseHas('custom_field_definitions', [
            'name' => 'Registration No',
            'required' => 1,
        ]);
    }
}
