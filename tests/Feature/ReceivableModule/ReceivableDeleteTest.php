<?php

namespace Tests\Feature\ReceivableModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-10-24 ("Transactions: Expense and Receivable 'Delete'"):
 * the Receivable module already had an admin-only soft delete — this card adds
 * the Delete button in the Transactions table, so the guards are pinned here.
 */
class ReceivableDeleteTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private User $billing;
    private Receivable $receivable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'admin']);
        $this->billing = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'billing']);

        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);

        $this->receivable = Receivable::factory()->create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'agent_id'     => $agent->id,
            'applicant_id' => $applicant->id,
            'status'       => 'pending',
        ]);
    }

    #[Test]
    public function admin_can_delete_a_receivable_with_a_reason(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('receivable.destroy', $this->receivable), ['reason' => 'Wrong applicant'])
            ->assertRedirect(route('receivable.index'));

        $this->assertSoftDeleted('receivables', ['id' => $this->receivable->id]);
        $this->assertDatabaseHas('receivable_histories', [
            'receivable_id' => $this->receivable->id,
            'to_status'     => 'deleted',
            'note'          => 'Wrong applicant',
        ]);
    }

    #[Test]
    public function accounting_cannot_delete_a_receivable(): void
    {
        $this->actingAs($this->billing)
            ->delete(route('receivable.destroy', $this->receivable), ['reason' => 'nope'])
            ->assertForbidden();

        $this->assertNotSoftDeleted('receivables', ['id' => $this->receivable->id]);
    }

    #[Test]
    public function only_admins_see_the_receivable_delete_button(): void
    {
        $this->actingAs($this->admin)->get(route('receivable.index'))
            ->assertOk()
            ->assertSee('delete-receivable-' . $this->receivable->id);

        $this->actingAs($this->billing)->get(route('receivable.index'))
            ->assertOk()
            ->assertDontSee('delete-receivable-' . $this->receivable->id);
    }
}
