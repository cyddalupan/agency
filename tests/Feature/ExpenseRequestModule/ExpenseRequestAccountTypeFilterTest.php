<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits request 2026-08-16 (Account Type dropdown filtering):
 * The Account Type (sub-account) dropdown must show ONE group depending on
 * the line's Charge + Applicant selection:
 *   - Charge = agent                    -> agent accounts only
 *   - Charge = office, no applicant     -> office accounts only
 *   - Charge = office + applicant       -> applicant accounts only
 * Non-matching options are removed from the list (not just disabled).
 *
 * The 3 groups come from settings/accounts: each account carries a Charge
 * Type of office | agent | applicant.
 */
class ExpenseRequestAccountTypeFilterTest extends TestCase
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

    private function makeMain(string $name, string $chargeType): Account
    {
        return Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => $name,
            'charge_type' => $chargeType,
        ]);
    }

    private function makeSub(Account $main, string $name, string $chargeType): Account
    {
        return Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $main->id,
            'name'        => $name,
            'charge_type' => $chargeType,
        ]);
    }

    #[Test]
    public function accounts_settings_offer_applicant_charge_type(): void
    {
        $response = $this->actingAs($this->user)->get(route('accounts.create'));

        $response->assertOk();
        $response->assertSee('value="applicant"', false);
    }

    #[Test]
    public function account_can_be_created_with_applicant_charge_type(): void
    {
        $main = $this->makeMain('APPLICANT', 'office');

        $response = $this->actingAs($this->user)->post(route('accounts.store'), [
            'name'        => 'Medical',
            'parent_id'   => $main->id,
            'type'        => 'expense',
            'charge_type' => 'applicant',
            'is_active'   => 1,
        ]);

        $response->assertRedirect(route('accounts.index'));
        $this->assertDatabaseHas('accounts', [
            'name'        => 'Medical',
            'charge_type' => 'applicant',
        ]);
    }

    #[Test]
    public function create_page_lists_account_groups_with_charge_type_offsets(): void
    {
        $agentMain = $this->makeMain('AGENT', 'agent');
        $this->makeSub($agentMain, 'Cash advance', 'agent');

        $applicantMain = $this->makeMain('APPLICANT', 'applicant');
        $this->makeSub($applicantMain, 'Medical', 'applicant');

        $officeMain = $this->makeMain('OFFICE', 'office');
        $this->makeSub($officeMain, 'Supplies', 'office');

        $response = $this->actingAs($this->user)->get(route('expense_request.create'));
        $response->assertOk();

        $html = $response->getContent();

        // Every group's sub-account carries its charge type as data-offset.
        $this->assertMatchesRegularExpression('/data-offset="agent"[\s\S]{0,200}Cash advance/', $html);
        $this->assertMatchesRegularExpression('/data-offset="applicant"[\s\S]{0,200}Medical/', $html);
        $this->assertMatchesRegularExpression('/data-offset="office"[\s\S]{0,200}Supplies/', $html);

        // The page contains the client-side filter that removes non-matching options.
        $this->assertStringContainsString('updateAccountType', $html);
    }

    #[Test]
    public function duplicate_check_preloader_message_is_present(): void
    {
        $response = $this->actingAs($this->user)->get(route('expense_request.create'));
        $response->assertOk();

        $this->assertStringContainsString('Checking for duplicate transaction', $response->getContent());
    }
}
