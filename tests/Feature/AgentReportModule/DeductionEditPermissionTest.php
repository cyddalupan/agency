<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6af170f5 — 'LANDAS: Deductions "Missing Particular"'.
 *
 * Josh (2026-11-11): "place review on the transaction for Admin and Accounting
 * Access only so they can edit."
 *
 * The bulk upload previously stored particular = NULL when it did not recognise
 * the description column. Those rows can now be corrected by Admin / Accounting
 * without deleting and re-encoding the transaction.
 */
class DeductionEditPermissionTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $branch;
    private Agent $agent;
    private AgentDeduction $deduction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->branch = Branch::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Manila',
        ]);
        $this->agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->deduction = AgentDeduction::create([
            'agency_id'  => $this->agency->id,
            'agent_id'   => $this->agent->id,
            'date'       => now()->toDateString(),
            'account'    => AgentDeduction::ACCOUNT_DEDUCTION,
            'amount'     => 3500.00,
            'particular' => null,
        ]);

        Cache::put('fx_usd_to_php', 56.0, 3600);
    }

    private function userOfType(string $type): User
    {
        return User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => $type,
        ]);
    }

    #[Test]
    public function admin_can_open_the_edit_form(): void
    {
        $this->actingAs($this->userOfType('admin'))
            ->get(route('agent_report.deduction.edit', $this->deduction))
            ->assertOk()
            ->assertSee('Edit Deduction');
    }

    #[Test]
    public function accounting_can_open_the_edit_form(): void
    {
        $this->actingAs($this->userOfType('billing'))
            ->get(route('agent_report.deduction.edit', $this->deduction))
            ->assertOk();
    }

    #[Test]
    public function super_admin_can_open_the_edit_form(): void
    {
        $this->actingAs($this->userOfType('super_admin'))
            ->get(route('agent_report.deduction.edit', $this->deduction))
            ->assertOk();
    }

    #[Test]
    public function a_plain_staff_user_cannot_open_the_edit_form(): void
    {
        $this->actingAs($this->userOfType('staff'))
            ->get(route('agent_report.deduction.edit', $this->deduction))
            ->assertForbidden();
    }

    #[Test]
    public function an_encoder_cannot_open_the_edit_form(): void
    {
        $this->actingAs($this->userOfType('encoder'))
            ->get(route('agent_report.deduction.edit', $this->deduction))
            ->assertForbidden();
    }

    #[Test]
    public function accounting_can_fill_in_a_missing_particular(): void
    {
        $this->assertNull($this->deduction->particular);

        $this->actingAs($this->userOfType('billing'))
            ->put(route('agent_report.deduction.update', $this->deduction), [
                'date'       => $this->deduction->date->toDateString(),
                'amount'     => 3500.00,
                'particular' => 'MARJORIE VILLANUEVA BACKOUT - AR#20791',
            ])
            ->assertRedirect(route('agent_report.index', ['tab' => 'deductions']));

        $this->assertSame(
            'MARJORIE VILLANUEVA BACKOUT - AR#20791',
            $this->deduction->fresh()->particular
        );
    }

    #[Test]
    public function a_plain_staff_user_cannot_update(): void
    {
        $this->actingAs($this->userOfType('staff'))
            ->put(route('agent_report.deduction.update', $this->deduction), [
                'date'       => $this->deduction->date->toDateString(),
                'amount'     => 999999.00,
                'particular' => 'tampered',
            ])
            ->assertForbidden();

        // Nothing was written.
        $this->assertNull($this->deduction->fresh()->particular);
        $this->assertEquals(3500.00, (float) $this->deduction->fresh()->amount);
    }

    #[Test]
    public function editing_does_not_move_the_deduction_to_another_agent(): void
    {
        $otherAgent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->actingAs($this->userOfType('admin'))
            ->put(route('agent_report.deduction.update', $this->deduction), [
                'date'       => $this->deduction->date->toDateString(),
                'amount'     => 3500.00,
                'particular' => 'corrected',
                // Attempt to hijack ownership — must be ignored, not applied.
                'agent_id'   => $otherAgent->id,
                'account'    => AgentDeduction::ACCOUNT_PAID,
            ])
            ->assertRedirect();

        $fresh = $this->deduction->fresh();
        $this->assertSame($this->agent->id, $fresh->agent_id);
        $this->assertSame(AgentDeduction::ACCOUNT_DEDUCTION, $fresh->account);
        $this->assertSame('corrected', $fresh->particular);
    }

    #[Test]
    public function a_user_from_another_agency_cannot_edit_the_deduction(): void
    {
        $otherAgency = Agency::factory()->create(['subdomain' => 'omnis']);
        $outsider = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);

        $this->actingAs($outsider)
            ->put(route('agent_report.deduction.update', $this->deduction), [
                'date'       => $this->deduction->date->toDateString(),
                'amount'     => 1.00,
                'particular' => 'hijacked',
            ])
            // Deliberate 404, not 403: authorizeAgency() refuses to confirm the
            // existence of a cross-tenant record. Do not "fix" this back to 403.
            // Same-agency role denials elsewhere in this file stay 403.
            ->assertNotFound();

        // Blocked before any write — inside or outside the tenant.
        $this->assertNull($this->deduction->fresh()->particular);
    }

    #[Test]
    public function the_edit_button_is_shown_to_admin_and_accounting_but_not_staff(): void
    {
        $admin = $this->userOfType('admin');
        $this->actingAs($admin)
            ->get(route('agent_report.deduction.show', $this->deduction))
            ->assertOk()
            ->assertSee('Edit');

        // A staff user is stopped before the page renders at all, so the Edit
        // action is unreachable for them either way.
        $staff = $this->userOfType('staff');
        $this->actingAs($staff)
            ->get(route('agent_report.deduction.show', $this->deduction))
            ->assertForbidden();
    }

    #[Test]
    public function validation_rejects_a_zero_amount(): void
    {
        $this->actingAs($this->userOfType('billing'))
            ->put(route('agent_report.deduction.update', $this->deduction), [
                'date'       => $this->deduction->date->toDateString(),
                'amount'     => 0,
                'particular' => 'x',
            ])
            ->assertSessionHasErrors('amount');

        $this->assertEquals(3500.00, (float) $this->deduction->fresh()->amount);
    }
}
