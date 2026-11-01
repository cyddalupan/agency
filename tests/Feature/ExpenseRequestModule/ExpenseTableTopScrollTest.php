<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-11-01: wide tables keep their horizontal scrollbar at the very
 * bottom, so you had to scroll past every row before you could slide sideways.
 * The shared layout now renders a synced scrollbar above each overflowing table
 * wrapper (resources/views/partials/top-scroll.blade.php).
 */
class ExpenseTableTopScrollTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function expense_index_renders_the_top_scroll_helper(): void
    {
        $this->actingAs($this->admin)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee('top-scroll-inner', false)
            ->assertSee("document.querySelectorAll(SELECTOR)", false);
    }

    #[Test]
    public function receivable_index_renders_the_top_scroll_helper(): void
    {
        $this->actingAs($this->admin)
            ->get(route('receivable.index'))
            ->assertOk()
            ->assertSee('top-scroll-inner', false);
    }
}
