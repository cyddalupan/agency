<?php

namespace Tests\Feature\OfficialReceipt;

use App\Models\Agency;
use App\Models\OfficialReceipt;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OfficialReceiptEditTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private OfficialReceipt $or;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->or = OfficialReceipt::factory()->create([
            'agency_id' => $this->agency->id,
            'or_no' => 'OR-ORIGINAL',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_edit(): void
    {
        $response = $this->get(route('official-receipts.edit', $this->or));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function edit_form_displays(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('official-receipts.edit', $this->or));

        $response->assertOk();
        $response->assertSee('OR-ORIGINAL');
    }

    #[Test]
    public function update_saves_changes(): void
    {
        $payment = Payment::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->put(route('official-receipts.update', $this->or), [
                'payment_id' => $payment->id,
                'or_no' => 'OR-UPDATED',
                'amount' => 30000,
                'issue_date' => '2026-06-20',
                'issued_to' => 'applicant',
                'issued_to_name' => 'Updated Name',
            ]);

        $response->assertRedirect(route('official-receipts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('official_receipts', [
            'id' => $this->or->id,
            'or_no' => 'OR-UPDATED',
            'amount' => 30000,
        ]);
    }

    #[Test]
    public function update_requires_or_no_and_amount(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('official-receipts.update', $this->or), []);

        $response->assertSessionHasErrors(['or_no', 'amount']);
    }
}
