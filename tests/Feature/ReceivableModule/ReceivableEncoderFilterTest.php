<?php

namespace Tests\Feature\ReceivableModule;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits 2026-10-07 — Encoder filter on the Receivable index.
 *
 * "Encoder" is the user who created the row (receivables.user_id). The
 * index accepts an optional ?encoder=<userId> that narrows the list to that
 * user's rows. A blank/absent value shows every row the viewer may see.
 */
class ReceivableEncoderFilterTest extends TestCase
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

    private function makeReceivable(int $agencyId, int $userId): Receivable
    {
        return Receivable::factory()->create([
            'agency_id'    => $agencyId,
            'user_id'      => $userId,
            'applicant_id' => Applicant::factory()->create(['agency_id' => $agencyId])->id,
        ]);
    }

    #[Test]
    public function blank_encoder_shows_every_row(): void
    {
        $viewer = $this->makeViewer();
        $encA = $this->makeEncoder($viewer->agency_id, 'Encoder Alpha');
        $encB = $this->makeEncoder($viewer->agency_id, 'Encoder Bravo');

        $a = $this->makeReceivable($viewer->agency_id, $encA->id);
        $b = $this->makeReceivable($viewer->agency_id, $encB->id);

        $this->actingAs($viewer)
            ->get(route('receivable.index'))
            ->assertOk()
            ->assertSee($a->code)
            ->assertSee($b->code);
    }

    #[Test]
    public function filtering_by_encoder_shows_only_that_encoders_rows(): void
    {
        $viewer = $this->makeViewer();
        $encA = $this->makeEncoder($viewer->agency_id, 'Encoder Alpha');
        $encB = $this->makeEncoder($viewer->agency_id, 'Encoder Bravo');

        $a = $this->makeReceivable($viewer->agency_id, $encA->id);
        $b = $this->makeReceivable($viewer->agency_id, $encB->id);

        $this->actingAs($viewer)
            ->get(route('receivable.index', ['encoder' => $encA->id]))
            ->assertOk()
            ->assertSee($a->code)
            ->assertDontSee($b->code);
    }

    #[Test]
    public function encoder_dropdown_lists_encoders_of_visible_rows(): void
    {
        $viewer = $this->makeViewer();
        $encA = $this->makeEncoder($viewer->agency_id, 'Encoder Alpha');
        $this->makeReceivable($viewer->agency_id, $encA->id);

        $this->actingAs($viewer)
            ->get(route('receivable.index'))
            ->assertOk()
            ->assertSee('encoder-filter', false)
            ->assertSee('Encoder Alpha');
    }
}
