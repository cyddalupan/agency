<?php

namespace Tests\Feature\RequestEditModule;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "Upload and Edit" (Cyd 2026-11-09):
 *
 *   "All Requestor should be able to edit their request as long as the status
 *    is Pending. Only the user can edit their request and admin and accounting.
 *    Should be able to upload 2-5 files. if a requestor edits the transaction,
 *    they should be able to upload another file. as long as the status is
 *    Pending."
 *
 * Note on "2-5": 5 is enforced as a MAXIMUM. A minimum of 2 is deliberately not
 * enforced, because a request with a single receipt is legitimate.
 */
class RequestEditPermissionTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $branch;
    private User $owner;
    private User $otherUser;
    private User $admin;
    private User $accounting;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'BACOLOD']);

        $this->owner      = $this->makeUser('staff', 'OWNER');
        $this->otherUser  = $this->makeUser('staff', 'SOMEONE ELSE');
        $this->admin      = $this->makeUser('admin', 'ADMIN');
        $this->accounting = $this->makeUser('billing', 'ACCOUNTING');
    }

    private function makeUser(string $type, string $name): User
    {
        return User::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'user_type' => $type,
            'name'      => $name,
        ]);
    }

    private function receivable(User $owner, string $status): Receivable
    {
        return Receivable::create([
            'agency_id' => $this->agency->id,
            'user_id'   => $owner->id,
            'code'      => 'AR-'.uniqid(),
            'date'      => now()->toDateString(),
            'amount'    => 1000,
            'status'    => $status,
        ]);
    }

    private function expenseRequest(User $owner, string $status): ExpenseRequest
    {
        return ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $owner->id,
            'branch_id'    => $this->branch->id,
            'reference_no' => (string) random_int(100000, 999999),
            'date'         => now()->toDateString(),
            'status'       => $status,
            'notes'        => 'QA',
        ]);
    }

    // ---------------- permission matrix ----------------

    #[Test]
    public function owner_can_edit_their_own_pending_receivable(): void
    {
        $r = $this->receivable($this->owner, Receivable::STATUS_PENDING);

        $this->actingAs($this->owner)->get(route('receivable.edit', $r))->assertOk();
    }

    #[Test]
    public function owner_cannot_edit_their_own_non_pending_receivable(): void
    {
        $r = $this->receivable($this->owner, Receivable::STATUS_RECEIVED);

        $this->actingAs($this->owner)->get(route('receivable.edit', $r))->assertForbidden();
    }

    #[Test]
    public function owner_cannot_edit_someone_elses_pending_receivable(): void
    {
        $r = $this->receivable($this->owner, Receivable::STATUS_PENDING);

        $this->actingAs($this->otherUser)->get(route('receivable.edit', $r))->assertForbidden();
    }

    #[Test]
    public function admin_and_accounting_can_edit_any_receivable(): void
    {
        $received = $this->receivable($this->owner, Receivable::STATUS_RECEIVED);

        $this->actingAs($this->admin)->get(route('receivable.edit', $received))->assertOk();
        $this->actingAs($this->accounting)->get(route('receivable.edit', $received))->assertOk();
    }

    #[Test]
    public function owner_can_edit_their_own_pending_expense_request(): void
    {
        $e = $this->expenseRequest($this->owner, ExpenseRequest::STATUS_PENDING);

        $this->actingAs($this->owner)->get(route('expense_request.edit', $e))->assertOk();
    }

    #[Test]
    public function owner_cannot_edit_their_own_approved_expense_request(): void
    {
        $e = $this->expenseRequest($this->owner, ExpenseRequest::STATUS_APPROVED);

        $this->actingAs($this->owner)->get(route('expense_request.edit', $e))->assertForbidden();
    }

    #[Test]
    public function owner_cannot_edit_someone_elses_pending_expense_request(): void
    {
        $e = $this->expenseRequest($this->owner, ExpenseRequest::STATUS_PENDING);

        $this->actingAs($this->otherUser)->get(route('expense_request.edit', $e))->assertForbidden();
    }

    #[Test]
    public function admin_and_accounting_can_edit_any_expense_request(): void
    {
        $released = $this->expenseRequest($this->owner, ExpenseRequest::STATUS_RELEASED);

        $this->actingAs($this->admin)->get(route('expense_request.edit', $released))->assertOk();
        $this->actingAs($this->accounting)->get(route('expense_request.edit', $released))->assertOk();
    }

    // ---------------- attachments ----------------

    #[Test]
    public function three_files_can_be_uploaded_to_a_receivable(): void
    {
        $this->actingAs($this->owner)->post(route('receivable.store'), [
            'date'       => now()->toDateString(),
            'agent_id'   => \App\Models\Agent::factory()->create([
                'agency_id' => $this->agency->id,
                'branch_id' => $this->branch->id,
            ])->id,
            'amount'     => 500,
            'attachments' => [
                UploadedFile::fake()->create('a.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('b.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('c.pdf', 100, 'application/pdf'),
            ],
        ]);

        $receivable = Receivable::where('user_id', $this->owner->id)->firstOrFail();

        $this->assertEquals(3, $receivable->attachments()->count());
    }

    #[Test]
    public function more_than_five_files_is_rejected(): void
    {
        $r = $this->receivable($this->owner, Receivable::STATUS_PENDING);

        $files = [];
        for ($i = 1; $i <= 6; $i++) {
            $files[] = UploadedFile::fake()->create("f{$i}.pdf", 10, 'application/pdf');
        }

        $response = $this->actingAs($this->owner)->put(route('receivable.update', $r), [
            'date'        => now()->toDateString(),
            'agent_id'    => \App\Models\Agent::factory()->create([
                'agency_id' => $this->agency->id,
                'branch_id' => $this->branch->id,
            ])->id,
            'amount'      => 1000,
            'attachments' => $files,
        ]);

        $response->assertSessionHasErrors('attachments');
        $this->assertEquals(0, $r->attachments()->count());
    }

    #[Test]
    public function editing_appends_files_up_to_the_cap(): void
    {
        $r = $this->receivable($this->owner, Receivable::STATUS_PENDING);

        foreach (['one.pdf', 'two.pdf'] as $name) {
            $r->attachments()->create([
                'path'          => "receivable-attachments/{$name}",
                'original_name' => $name,
                'uploaded_by'   => $this->owner->id,
            ]);
        }

        $this->assertEquals(2, $r->attachments()->count());

        // Adding two more fits (2 + 2 = 4 <= 5).
        $this->actingAs($this->owner)->put(route('receivable.update', $r), [
            'date'        => now()->toDateString(),
            'agent_id'    => \App\Models\Agent::factory()->create([
                'agency_id' => $this->agency->id,
                'branch_id' => $this->branch->id,
            ])->id,
            'amount'      => 1000,
            'attachments' => [
                UploadedFile::fake()->create('three.pdf', 10, 'application/pdf'),
                UploadedFile::fake()->create('four.pdf', 10, 'application/pdf'),
            ],
        ]);

        $this->assertEquals(4, $r->fresh()->attachments()->count());
    }
}
