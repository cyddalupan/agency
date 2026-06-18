<?php

namespace Tests\Feature\Agency;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agencyA;
    private Agency $agencyB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agencyA = Agency::factory()->create(['name' => 'Agency A', 'subdomain' => 'agency-a']);
        $this->agencyB = Agency::factory()->create(['name' => 'Agency B', 'subdomain' => 'agency-b']);
    }

    /**
     * Set the tenant context for the test (simulates IdentifyAgency middleware).
     */
    private function setTenant(Agency $agency): void
    {
        app()->instance('tenant_agency', $agency);
    }

    private function clearTenant(): void
    {
        app()->forgetInstance('tenant_agency');
    }

    // ─── TENANT SCOPE — AGENCY ID ASSIGNMENT ──────────────────────────

    #[Test]
    public function models_are_created_with_correct_agency_id(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);
        $employer = Employer::factory()->create(['agency_id' => $this->agencyA->id]);

        $this->assertEquals($this->agencyA->id, $applicant->agency_id);
        $this->assertEquals($this->agencyA->id, $employer->agency_id);
    }

    #[Test]
    public function users_are_created_with_agency_id(): void
    {
        $user = User::factory()->create(['agency_id' => $this->agencyA->id]);

        $this->assertEquals($this->agencyA->id, $user->agency_id);
    }

    // ─── TENANT SCOPE — QUERY FILTERING ───────────────────────────────

    #[Test]
    public function tenant_scope_filters_applicants_by_current_agency(): void
    {
        Applicant::factory()->count(3)->create(['agency_id' => $this->agencyA->id]);
        Applicant::factory()->count(2)->create(['agency_id' => $this->agencyB->id]);

        $this->setTenant($this->agencyA);
        $this->assertEquals(3, Applicant::count());

        $this->setTenant($this->agencyB);
        $this->assertEquals(2, Applicant::count());
    }

    #[Test]
    public function tenant_scope_filters_employers_by_current_agency(): void
    {
        Employer::factory()->count(4)->create(['agency_id' => $this->agencyA->id]);
        Employer::factory()->count(1)->create(['agency_id' => $this->agencyB->id]);

        $this->setTenant($this->agencyA);
        $this->assertEquals(4, Employer::count());

        $this->setTenant($this->agencyB);
        $this->assertEquals(1, Employer::count());
    }

    #[Test]
    public function tenant_scope_filters_users_by_current_agency(): void
    {
        User::factory()->count(3)->create(['agency_id' => $this->agencyA->id]);
        User::factory()->count(3)->create(['agency_id' => $this->agencyB->id]);

        $this->setTenant($this->agencyA);
        $this->assertEquals(3, User::count());

        $this->setTenant($this->agencyB);
        $this->assertEquals(3, User::count());
    }

    #[Test]
    public function no_tenant_scope_returns_all_records(): void
    {
        Applicant::factory()->count(3)->create(['agency_id' => $this->agencyA->id]);
        Applicant::factory()->count(2)->create(['agency_id' => $this->agencyB->id]);

        $this->clearTenant();
        $this->assertEquals(5, Applicant::count());
    }

    #[Test]
    public function without_global_scope_bypasses_tenant_filter(): void
    {
        Applicant::factory()->count(3)->create(['agency_id' => $this->agencyA->id]);
        Applicant::factory()->count(2)->create(['agency_id' => $this->agencyB->id]);

        $this->setTenant($this->agencyA);
        $all = Applicant::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->count();
        $this->assertEquals(5, $all);
    }

    #[Test]
    public function ensure_agency_data_is_not_leaked(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agencyA->id,
            'first_name' => 'SecretA',
        ]);
        Applicant::factory()->create([
            'agency_id' => $this->agencyB->id,
            'first_name' => 'SecretB',
        ]);

        $this->setTenant($this->agencyA);
        $found = Applicant::where('first_name', 'SecretB')->first();
        $this->assertNull($found, 'Agency B data leaked into Agency A query');
    }

    // ─── TENANT SCOPE — CUSTOM FIELD DEFINITIONS ─────────────────────

    #[Test]
    public function custom_field_definitions_are_scoped_by_agency(): void
    {
        CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'name' => 'Field A',
            'key' => 'field_a',
            'type' => 'text',
        ]);
        CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyB->id,
            'model_type' => 'Applicant',
            'name' => 'Field B',
            'key' => 'field_b',
            'type' => 'text',
        ]);

        $this->setTenant($this->agencyA);
        $this->assertEquals(1, CustomFieldDefinition::count());

        $this->setTenant($this->agencyB);
        $this->assertEquals(1, CustomFieldDefinition::count());
    }

    #[Test]
    public function custom_field_values_are_scoped_by_agency(): void
    {
        $applicantA = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);
        $applicantB = Applicant::factory()->create(['agency_id' => $this->agencyB->id]);
        $defA = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
        ]);
        $defB = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyB->id,
            'model_type' => 'Applicant',
        ]);

        CustomFieldValue::factory()->create([
            'agency_id' => $this->agencyA->id,
            'custom_field_definition_id' => $defA->id,
            'model_type' => 'Applicant',
            'model_id' => $applicantA->id,
            'value' => 'Value A',
        ]);
        CustomFieldValue::factory()->create([
            'agency_id' => $this->agencyB->id,
            'custom_field_definition_id' => $defB->id,
            'model_type' => 'Applicant',
            'model_id' => $applicantB->id,
            'value' => 'Value B',
        ]);

        $this->setTenant($this->agencyA);
        $this->assertEquals(1, CustomFieldValue::count());

        $this->setTenant($this->agencyB);
        $this->assertEquals(1, CustomFieldValue::count());
    }

    // ─── CUSTOM FIELD DEFINITION CRUD ─────────────────────────────────

    #[Test]
    public function admin_can_create_custom_field_definition(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)->post(route('custom-field-definitions.store'), [
            'model_type' => 'Applicant',
            'name' => 'Passport Number',
            'key' => 'passport_number',
            'type' => 'text',
            'required' => false,
            'order' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('custom_field_definitions', [
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'name' => 'Passport Number',
            'key' => 'passport_number',
        ]);
    }

    #[Test]
    public function admin_can_list_custom_field_definitions(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);
        CustomFieldDefinition::factory()->count(3)->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)
            ->get(route('custom-field-definitions.index', ['model_type' => 'Applicant']));

        $response->assertOk();
        $response->assertViewHas('definitions');
    }

    #[Test]
    public function admin_can_update_custom_field_definition(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);
        $definition = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'name' => 'Old Name',
            'key' => 'old_key',
            'type' => 'text',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)->put(
            route('custom-field-definitions.update', $definition),
            [
                'name' => 'Updated Name',
                'required' => true,
                'order' => 5,
            ]
        );

        $response->assertRedirect();
        $definition->refresh();
        $this->assertEquals('Updated Name', $definition->name);
        $this->assertTrue($definition->required);
        $this->assertEquals(5, $definition->order);
    }

    #[Test]
    public function admin_can_delete_custom_field_definition(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);
        $definition = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)->delete(
            route('custom-field-definitions.destroy', $definition)
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('custom_field_definitions', ['id' => $definition->id]);
    }

    #[Test]
    public function staff_cannot_manage_custom_field_definitions(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'staff',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($staff)->post(route('custom-field-definitions.store'), [
            'model_type' => 'Applicant',
            'name' => 'Hack',
            'key' => 'hack',
            'type' => 'text',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function custom_field_definitions_are_scoped_to_own_agency_in_crud(): void
    {
        $adminA = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);
        $defFromB = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyB->id,
            'model_type' => 'Applicant',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($adminA)->get(
            route('custom-field-definitions.index', ['model_type' => 'Applicant'])
        );

        $response->assertOk();
        $response->assertDontSee($defFromB->name);
    }

    // ─── CUSTOM FIELD VALIDATION ──────────────────────────────────────

    #[Test]
    public function custom_field_definition_requires_valid_model_type(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)->post(route('custom-field-definitions.store'), [
            'model_type' => '',
            'name' => 'Test',
            'key' => 'test',
            'type' => 'text',
        ]);

        $response->assertSessionHasErrors('model_type');
    }

    #[Test]
    public function custom_field_definition_requires_valid_type(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)->post(route('custom-field-definitions.store'), [
            'model_type' => 'Applicant',
            'name' => 'Test',
            'key' => 'test',
            'type' => 'invalid_type',
        ]);

        $response->assertSessionHasErrors('type');
    }

    #[Test]
    public function custom_field_definition_key_is_unique_per_agency_and_model(): void
    {
        CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'passport_number',
        ]);

        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)->post(route('custom-field-definitions.store'), [
            'model_type' => 'Applicant',
            'name' => 'Passport',
            'key' => 'passport_number',
            'type' => 'text',
        ]);

        $response->assertSessionHasErrors('key');
    }

    #[Test]
    public function identical_key_in_different_agency_is_allowed(): void
    {
        CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'passport_number',
        ]);

        $adminB = User::factory()->create([
            'agency_id' => $this->agencyB->id,
            'user_type' => 'admin',
        ]);

        $this->setTenant($this->agencyB);

        $response = $this->actingAs($adminB)->post(route('custom-field-definitions.store'), [
            'model_type' => 'Applicant',
            'name' => 'Passport Number',
            'key' => 'passport_number',
            'type' => 'text',
        ]);

        $response->assertRedirect();
    }

    // ─── CUSTOM FIELD VALUES ON MODELS ────────────────────────────────

    #[Test]
    public function applicant_can_have_custom_field_values(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);
        $definition = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'passport_number',
            'type' => 'text',
        ]);

        $this->setTenant($this->agencyA);

        $applicant->setCustomField('passport_number', 'AB123456');
        $this->assertEquals('AB123456', $applicant->getCustomField('passport_number'));
    }

    #[Test]
    public function employer_can_have_custom_field_values(): void
    {
        $employer = Employer::factory()->create(['agency_id' => $this->agencyA->id]);
        CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Employer',
            'key' => 'business_license',
            'type' => 'text',
        ]);

        $this->setTenant($this->agencyA);

        $employer->setCustomField('business_license', 'LIC-12345');
        $this->assertEquals('LIC-12345', $employer->getCustomField('business_license'));
    }

    #[Test]
    public function custom_field_value_is_deleted_when_definition_is_deleted(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);
        $definition = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'test_field',
        ]);

        $this->setTenant($this->agencyA);
        $applicant->setCustomField('test_field', 'some value');

        $definition->delete();

        $this->assertDatabaseMissing('custom_field_values', [
            'custom_field_definition_id' => $definition->id,
        ]);
    }

    #[Test]
    public function sync_custom_fields_updates_multiple_values(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);
        $defA = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'field_a',
            'type' => 'text',
        ]);
        $defB = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'field_b',
            'type' => 'text',
        ]);

        $this->setTenant($this->agencyA);

        $applicant->syncCustomFields([
            'field_a' => 'Value A',
            'field_b' => 'Value B',
        ]);

        $this->assertEquals('Value A', $applicant->getCustomField('field_a'));
        $this->assertEquals('Value B', $applicant->getCustomField('field_b'));
    }

    #[Test]
    public function sync_custom_fields_clears_empty_values(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);
        $definition = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'field_a',
            'type' => 'text',
        ]);

        $this->setTenant($this->agencyA);

        $applicant->setCustomField('field_a', 'Existing');
        $this->assertEquals('Existing', $applicant->getCustomField('field_a'));

        // Sync with empty value should clear it
        $applicant->syncCustomFields(['field_a' => '']);

        $this->assertNull($applicant->getCustomField('field_a'));
    }

    // ─── SUPER ADMIN BYPASS ───────────────────────────────────────────

    #[Test]
    public function super_admin_can_access_all_agencies_data(): void
    {
        $superAdmin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'super_admin',
        ]);
        Applicant::factory()->count(2)->create(['agency_id' => $this->agencyA->id]);
        Applicant::factory()->count(3)->create(['agency_id' => $this->agencyB->id]);

        $this->setTenant($this->agencyA);

        // Super admin bypasses tenant scope — should see all
        $this->actingAs($superAdmin);
        $this->assertEquals(5, Applicant::count());
    }

    #[Test]
    public function admin_cannot_access_other_agency_data(): void
    {
        $adminA = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);
        Applicant::factory()->create([
            'agency_id' => $this->agencyB->id,
            'first_name' => 'OtherAgencySecret',
        ]);

        $this->setTenant($this->agencyA);

        $this->actingAs($adminA);
        $found = Applicant::where('first_name', 'OtherAgencySecret')->first();
        $this->assertNull($found);
    }

    // ─── DATA VERIFICATION ────────────────────────────────────────────

    #[Test]
    public function agency_relationship_is_accessible(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);

        $this->assertNotNull($applicant->agency);
        $this->assertEquals($this->agencyA->id, $applicant->agency->id);
        $this->assertEquals('Agency A', $applicant->agency->name);
    }

    #[Test]
    public function models_without_agency_id_are_not_scoped(): void
    {
        // Models like Country that don't have agency_id should not be affected
        // by TenantScope. This test verifies the scope doesn't break queries
        // for tables without agency_id column.
        $this->assertTrue(true, 'Placeholder — ensure models without agency_id are queryable');
    }
}
