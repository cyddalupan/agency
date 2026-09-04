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
 * Toybits 2026-08-31 — Batch status update on the receivable index:
 *
 * 1. Admin can select multiple receivables (checkboxes) and mark them Received
 *    in a single submit; each changed receivable gets its own history entry.
 * 2. Receivables already in the target status are skipped, and only the
 *    caller's own agency's receivables are ever touched (isolation).
 * 3. Non-admin (staff/billing) cannot use the batch endpoint.
 * 4. The index page exposes the checkboxes + bulk toolbar to admin only.
 */
class ReceivableBulkStatusTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private User $billing;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);
        $this->staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
    }

    private function createReceivable(string $status = 'pending'): Receivable
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);

        return Receivable::factory()->create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'agent_id'     => $agent->id,
            'applicant_id' => $applicant->id,
            'status'       => $status,
        ]);
    }

    #[Test]
    public function admin_can_batch_mark_multiple_receivables_received_with_history_per_row(): void
    {
        $first = $this->createReceivable();
        $second = $this->createReceivable();

        $this->actingAs($this->admin)
            ->post(route('receivable.bulk_status'), [
                'ids'    => [$first->id, $second->id],
                'status' => 'received',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('received', $first->fresh()->status);
        $this->assertSame('received', $second->fresh()->status);

        foreach ([$first, $second] as $receivable) {
            $this->assertDatabaseHas('receivable_histories', [
                'receivable_id' => $receivable->id,
                'user_id'       => $this->admin->id,
                'from_status'   => 'pending',
                'to_status'     => 'received',
            ]);
        }
    }

    #[Test]
    public function receivables_already_in_target_status_are_skipped(): void
    {
        $first = $this->createReceivable('received');
        $second = $this->createReceivable();

        $this->actingAs($this->admin)
            ->post(route('receivable.bulk_status'), [
                'ids'    => [$first->id, $second->id],
                'status' => 'received',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'Updated 1 receivable(s)'));

        $this->assertSame('received', $first->fresh()->status);
        $this->assertSame('received', $second->fresh()->status);

        // Only the second receivable got a new history entry.
        $this->assertDatabaseCount('receivable_histories', 1);
        $this->assertDatabaseHas('receivable_histories', [
            'receivable_id' => $second->id,
            'to_status'     => 'received',
        ]);
    }

    #[Test]
    public function other_agencies_receivables_are_never_touched(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAgent = Agent::factory()->create(['agency_id' => $otherAgency->id]);
        $otherApplicant = Applicant::factory()->create(['agency_id' => $otherAgency->id, 'agent_id' => $otherAgent->id]);
        $otherReceivable = Receivable::factory()->create([
            'agency_id'    => $otherAgency->id,
            'agent_id'     => $otherAgent->id,
            'applicant_id' => $otherApplicant->id,
            'status'       => 'pending',
        ]);

        $mine = $this->createReceivable();

        $this->actingAs($this->admin)
            ->post(route('receivable.bulk_status'), [
                'ids'    => [$mine->id, $otherReceivable->id],
                'status' => 'received',
            ])
            ->assertRedirect();

        $this->assertSame('received', $mine->fresh()->status);
        $this->assertSame('pending', $otherReceivable->fresh()->status);
    }

    #[Test]
    public function non_admin_cannot_use_batch_status_endpoint(): void
    {
        $receivable = $this->createReceivable();

        foreach ([$this->staff, $this->billing] as $user) {
            $this->actingAs($user)
                ->post(route('receivable.bulk_status'), [
                    'ids'    => [$receivable->id],
                    'status' => 'received',
                ])
                ->assertForbidden();
        }

        $this->assertSame('pending', $receivable->fresh()->status);
    }

    #[Test]
    public function index_page_shows_bulk_toolbar_to_admin_but_not_billing(): void
    {
        $this->createReceivable();

        $adminHtml = $this->actingAs($this->admin)
            ->get(route('receivable.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('bulk-status-form', $adminHtml);
        $this->assertStringContainsString('request-checkbox', $adminHtml);

        $billingHtml = $this->actingAs($this->billing)
            ->get(route('receivable.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('bulk-status-form', $billingHtml);
        $this->assertStringNotContainsString('request-checkbox', $billingHtml);
    }

    #[Test]
    public function batch_status_validates_status_and_ids(): void
    {
        $receivable = $this->createReceivable();

        $this->actingAs($this->admin)
            ->post(route('receivable.bulk_status'), [
                'ids'    => [$receivable->id],
                'status' => 'not_a_status',
            ])
            ->assertSessionHasErrors('status');

        $this->actingAs($this->admin)
            ->post(route('receivable.bulk_status'), [
                'ids'    => [],
                'status' => 'received',
            ])
            ->assertSessionHasErrors('ids');
    }
}
