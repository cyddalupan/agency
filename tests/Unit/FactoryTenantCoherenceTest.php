<?php

namespace Tests\Unit;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Bill;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use App\Models\Employer;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b0363676e4f389b5b3fd002 — factories must not mix agencies.
 *
 * A factory that builds its children from independent Agency::factory() calls
 * produces a row whose relations are unreachable under its own agency
 * (TenantScope filters them out on read). These tests state the rule the
 * factories now follow: children derive their agency from the parent, and an
 * explicit override is still obeyed.
 */
class FactoryTenantCoherenceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function bill_children_live_in_the_bills_agency(): void
    {
        $agency = Agency::factory()->create();

        $bill = Bill::factory()->create(['agency_id' => $agency->id]);

        $this->assertSame($agency->id, $bill->employer->agency_id);
        $this->assertSame($agency->id, $bill->applicant->agency_id);
    }

    #[Test]
    public function bill_children_follow_a_factory_created_agency(): void
    {
        // No explicit agency_id: the bill's own (factory-created) agency is the
        // one both children must inherit, whatever order Faker resolves them in.
        $bill = Bill::factory()->create();

        $this->assertSame($bill->agency_id, $bill->employer->agency_id);
        $this->assertSame($bill->agency_id, $bill->applicant->agency_id);
    }

    #[Test]
    public function explicit_employer_override_is_respected(): void
    {
        $agency = Agency::factory()->create();
        $foreign = Employer::factory()->create(['agency_id' => Agency::factory()->create()->id]);

        $bill = Bill::factory()->create([
            'agency_id' => $agency->id,
            'employer_id' => $foreign->id,
        ]);

        $this->assertSame($foreign->id, $bill->employer_id);
        $this->assertSame($agency->id, $bill->applicant->agency_id);
    }

    #[Test]
    public function explicit_applicant_override_is_respected(): void
    {
        $agency = Agency::factory()->create();
        $foreign = Applicant::factory()->create(['agency_id' => Agency::factory()->create()->id]);

        $bill = Bill::factory()->create([
            'agency_id' => $agency->id,
            'applicant_id' => $foreign->id,
        ]);

        $this->assertSame($foreign->id, $bill->applicant_id);
        $this->assertSame($agency->id, $bill->employer->agency_id);
    }

    #[Test]
    public function payment_agency_derives_from_its_bill(): void
    {
        $bill = Bill::factory()->create(['agency_id' => Agency::factory()->create()->id]);

        $payment = Payment::factory()->create(['bill_id' => $bill->id]);

        $this->assertSame($bill->agency_id, $payment->agency_id);
    }

    #[Test]
    public function custom_field_value_agency_derives_from_its_definition(): void
    {
        $agency = Agency::factory()->create();
        $definition = CustomFieldDefinition::factory()->create(['agency_id' => $agency->id]);

        $value = CustomFieldValue::factory()->create([
            'custom_field_definition_id' => $definition->id,
        ]);

        $this->assertSame($agency->id, $value->agency_id);
    }

    #[Test]
    public function custom_field_value_for_model_agrees_with_its_subject(): void
    {
        $agency = Agency::factory()->create();
        $employer = Employer::factory()->create(['agency_id' => $agency->id]);
        $definition = CustomFieldDefinition::factory()->create([
            'agency_id' => $agency->id,
            'model_type' => 'Employer',
        ]);

        $value = CustomFieldValue::factory()
            ->forModel($employer)
            ->create([
                'custom_field_definition_id' => $definition->id,
                'model_type' => 'Employer',
            ]);

        $this->assertSame($employer->id, $value->model_id);
        $this->assertSame($agency->id, $value->agency_id);
    }

    #[Test]
    public function payment_without_a_bill_still_gets_an_agency(): void
    {
        $payment = Payment::factory()->create(['bill_id' => null]);

        $this->assertNotNull($payment->agency_id);
    }
}
