<?php

namespace Tests\Feature\Accounting;

use App\Models\Agency;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression test for: "on /receivable the review button design is off —
 * the forward icon is on the next line and exceeds the tag design."
 *
 * Root cause: the Review anchor used a plain `btn btn-xs` so the "→"
 * glyph wrapped to a second line inside the tiny button.
 *
 * Contract: the Review link keeps the arrow on the same line
 * (whitespace-nowrap + inline-flex with a small gap).
 */
class ReceivableReviewButtonTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $billing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);
    }

    #[Test]
    public function review_link_keeps_arrow_on_the_same_line(): void
    {
        Receivable::create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->billing->id,
            'agent_id'  => \App\Models\Agent::factory()->create(['agency_id' => $this->agency->id])->id,
            'code'      => 'AR-1',
            'date'      => now()->toDateString(),
            'status'    => Receivable::STATUS_PENDING,
            'amount'    => 1000.00,
            'account'   => 'Test',
        ]);

        $response = $this->actingAs($this->billing)->get(route('receivable.index'));

        $response->assertOk()
            ->assertSee('btn btn-xs btn-ghost whitespace-nowrap inline-flex items-center gap-1', false)
            ->assertSee('>Review →</a>', false);
    }

    #[Test]
    public function review_button_still_links_to_the_show_page(): void
    {
        $receivable = Receivable::create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->billing->id,
            'agent_id'  => \App\Models\Agent::factory()->create(['agency_id' => $this->agency->id])->id,
            'code'      => 'AR-2',
            'date'      => now()->toDateString(),
            'status'    => Receivable::STATUS_RECEIVED,
            'amount'    => 2500.00,
            'account'   => 'Test',
        ]);

        $response = $this->actingAs($this->billing)->get(route('receivable.index'));

        $response->assertOk()
            ->assertSee(route('receivable.show', $receivable->id), false);
    }
}
