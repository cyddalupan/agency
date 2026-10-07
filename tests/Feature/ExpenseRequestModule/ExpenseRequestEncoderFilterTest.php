<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Agency;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits 2026-10-07 — Encoder filter on the Expenses & Payments index.
 *
 * "Encoder" is the user who created the request (expense_requests.user_id).
 * The index accepts an optional ?encoder=<userId> that narrows the list and
 * composes with the existing ?status= tab.
 */
class ExpenseRequestEncoderFilterTest extends TestCase
{
    use RefreshDatabase;

    private function makeViewer(): User
    {
        $agency = Agency::factory()->create();

        return User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function makeEncoder(int $agencyId, string $name): User
    {
        return User::factory()->create([
            'agency_id' => $agencyId,
            'user_type' => 'staff',
            'name'      => $name,
        ]);
    }

    private function makeRequest(int $agencyId, int $userId, string $status = 'pending'): ExpenseRequest
    {
        return ExpenseRequest::create([
            'agency_id'    => $agencyId,
            'user_id'      => $userId,
            'reference_no' => 'EXP-' . fake()->unique()->numerify('#####'),
            'date'         => now()->toDateString(),
            'status'       => $status,
        ]);
    }

    #[Test]
    public function blank_encoder_shows_every_row(): void
    {
        $viewer = $this->makeViewer();
        $encA = $this->makeEncoder($viewer->agency_id, 'Encoder Alpha');
        $encB = $this->makeEncoder($viewer->agency_id, 'Encoder Bravo');

        $a = $this->makeRequest($viewer->agency_id, $encA->id);
        $b = $this->makeRequest($viewer->agency_id, $encB->id);

        $this->actingAs($viewer)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee($a->reference_no)
            ->assertSee($b->reference_no);
    }

    #[Test]
    public function filtering_by_encoder_shows_only_that_encoders_rows(): void
    {
        $viewer = $this->makeViewer();
        $encA = $this->makeEncoder($viewer->agency_id, 'Encoder Alpha');
        $encB = $this->makeEncoder($viewer->agency_id, 'Encoder Bravo');

        $a = $this->makeRequest($viewer->agency_id, $encA->id);
        $b = $this->makeRequest($viewer->agency_id, $encB->id);

        $this->actingAs($viewer)
            ->get(route('expense_request.index', ['encoder' => $encA->id]))
            ->assertOk()
            ->assertSee($a->reference_no)
            ->assertDontSee($b->reference_no);
    }

    #[Test]
    public function encoder_filter_composes_with_status_tab(): void
    {
        $viewer = $this->makeViewer();
        $encA = $this->makeEncoder($viewer->agency_id, 'Encoder Alpha');

        $pending = $this->makeRequest($viewer->agency_id, $encA->id, 'pending');
        $released = $this->makeRequest($viewer->agency_id, $encA->id, 'released');

        $this->actingAs($viewer)
            ->get(route('expense_request.index', ['encoder' => $encA->id, 'status' => 'released']))
            ->assertOk()
            ->assertSee($released->reference_no)
            ->assertDontSee($pending->reference_no);
    }

    #[Test]
    public function encoder_dropdown_preserves_the_active_status_tab(): void
    {
        $viewer = $this->makeViewer();
        $encA = $this->makeEncoder($viewer->agency_id, 'Encoder Alpha');
        $this->makeRequest($viewer->agency_id, $encA->id, 'released');

        $this->actingAs($viewer)
            ->get(route('expense_request.index', ['status' => 'released']))
            ->assertOk()
            ->assertSee('encoder-filter', false)
            ->assertSee('Encoder Alpha')
            // Hidden status field keeps the tab applied when the encoder changes.
            ->assertSee('name="status" value="released"', false);
    }
}
