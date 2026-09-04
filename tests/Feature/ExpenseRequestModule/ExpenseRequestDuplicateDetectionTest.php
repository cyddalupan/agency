<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits request 2026-08-16 (expense request duplicates):
 * 1. Save-time duplicate check: same amount + same applicant (or same amount
 *    with no applicant on both sides) counts as a duplicate.
 * 2. Index page highlights duplicate transactions with a background color.
 * 3. Save flow shows a "Checking for duplicate transaction…" loading message.
 */
class ExpenseRequestDuplicateDetectionTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function makeRequest(array $items = []): ExpenseRequest
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);

        $requestModel = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'reference_no' => 'REF-' . fake()->unique()->numberBetween(1000, 9999),
            'date'         => now()->toDateString(),
            'status'       => 'pending',
        ]);

        foreach ($items as $item) {
            ExpenseRequestItem::create(array_merge([
                'expense_request_id' => $requestModel->id,
                'charge'             => 'office',
                'agent_id'           => null,
                'applicant_id'       => null,
                'currency'           => 'PHP',
                'amount'             => 100.00,
                'account_id'         => $account->id,
                'particular'         => 'Test item',
            ], $item));
        }

        return $requestModel;
    }

    #[Test]
    public function check_duplicates_returns_duplicate_for_same_amount_and_applicant(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $this->makeRequest([
            ['applicant_id' => $applicant->id, 'amount' => 500.00],
        ]);

        $response = $this->actingAs($this->user)->postJson(route('expense_request.check_duplicates'), [
            'lines' => [
                ['applicant_id' => $applicant->id, 'amount' => 500.00],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['duplicate' => true]);
    }

    #[Test]
    public function check_duplicates_returns_duplicate_for_same_amount_without_applicant(): void
    {
        $this->makeRequest([
            ['applicant_id' => null, 'amount' => 250.00],
        ]);

        $response = $this->actingAs($this->user)->postJson(route('expense_request.check_duplicates'), [
            'lines' => [
                ['applicant_id' => null, 'amount' => 250.00],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['duplicate' => true]);
    }

    #[Test]
    public function check_duplicates_returns_none_for_unique_transactions(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $this->makeRequest([
            ['applicant_id' => $applicant->id, 'amount' => 100.00],
        ]);

        $response = $this->actingAs($this->user)->postJson(route('expense_request.check_duplicates'), [
            'lines' => [
                ['applicant_id' => $applicant->id, 'amount' => 999.00],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['duplicate' => false]);
    }

    #[Test]
    public function check_duplicates_ignores_same_amount_different_applicant(): void
    {
        $applicantA = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $applicantB = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $this->makeRequest([
            ['applicant_id' => $applicantA->id, 'amount' => 300.00],
        ]);

        $response = $this->actingAs($this->user)->postJson(route('expense_request.check_duplicates'), [
            'lines' => [
                ['applicant_id' => $applicantB->id, 'amount' => 300.00],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['duplicate' => false]);
    }

    #[Test]
    public function check_duplicates_is_scoped_to_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);
        $otherApplicant = Applicant::factory()->create(['agency_id' => $otherAgency->id]);

        $otherRequest = ExpenseRequest::create([
            'agency_id'    => $otherAgency->id,
            'user_id'      => $otherUser->id,
            'reference_no' => 'REF-OTHER',
            'date'         => now()->toDateString(),
            'status'       => 'pending',
        ]);
        $otherAccount = Account::factory()->create(['agency_id' => $otherAgency->id]);
        ExpenseRequestItem::create([
            'expense_request_id' => $otherRequest->id,
            'charge'             => 'office',
            'applicant_id'       => $otherApplicant->id,
            'currency'           => 'PHP',
            'amount'             => 750.00,
            'account_id'         => $otherAccount->id,
        ]);

        // Same amount + same applicant id, but in a different agency → not a duplicate.
        $response = $this->actingAs($this->user)->postJson(route('expense_request.check_duplicates'), [
            'lines' => [
                ['applicant_id' => $otherApplicant->id, 'amount' => 750.00],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['duplicate' => false]);
    }

    #[Test]
    public function index_highlights_duplicate_rows_with_background_and_badge(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $this->makeRequest([
            ['applicant_id' => $applicant->id, 'amount' => 400.00],
        ]);
        $this->makeRequest([
            ['applicant_id' => $applicant->id, 'amount' => 400.00],
        ]);
        // Unique row — must NOT be highlighted.
        $this->makeRequest([
            ['applicant_id' => $applicant->id, 'amount' => 401.00],
        ]);

        $response = $this->actingAs($this->user)->get(route('expense_request.index'));
        $response->assertOk();

        $html = $response->getContent();

        // The two duplicate rows carry the highlight class and a badge.
        $this->assertSame(2, substr_count($html, 'duplicate-row'));
        $this->assertSame(2, substr_count($html, 'Duplicate'));

        // The unique row renders once, without any highlight.
        $this->assertSame(1, substr_count($html, '401.00'));
        $this->assertStringNotContainsString('duplicate-row', preg_replace('/.*401\.00.*/s', '', $html));
    }

    #[Test]
    public function index_does_not_highlight_rows_with_same_amount_different_applicant(): void
    {
        $applicantA = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $applicantB = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $this->makeRequest([
            ['applicant_id' => $applicantA->id, 'amount' => 600.00],
        ]);
        $this->makeRequest([
            ['applicant_id' => $applicantB->id, 'amount' => 600.00],
        ]);

        $response = $this->actingAs($this->user)->get(route('expense_request.index'));
        $response->assertOk();

        $html = $response->getContent();

        $this->assertSame(0, substr_count($html, 'duplicate-row'));
        $this->assertSame(0, substr_count($html, 'Duplicate'));
    }
}
