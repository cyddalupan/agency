<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-10-23: Payments and Deductions are separate tabs, and deductions
 * support bulk CSV upload.
 */
class AgentDeductionBulkUploadTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branch;
    private Agent $agent;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        // Only privileged accounts (MAE/EVELYN/ANGEL) may manage deductions.
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'branch_id' => $this->branch->id,
            'name'      => 'Mae',
        ]);
        $this->agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'name' => 'Juan Dela Cruz',
        ]);
        $this->applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $this->agent->id,
            'first_name' => 'Maria',
            'last_name'  => 'Dela Cruz',
        ]);
    }

    private function csv(array $lines, string $header = 'Date,Agent,Applicant,Amount,Particular'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('deductions.csv', $header . "\n" . implode("\n", $lines) . "\n");
    }

    #[Test]
    public function deductions_tab_lists_only_deduction_rows(): void
    {
        AgentDeduction::create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'agent_id'  => $this->agent->id,
            'date'      => now()->toDateString(),
            'account'   => AgentDeduction::ACCOUNT_DEDUCTION,
            'amount'    => 1200.00,
            'particular'=> 'Uniform deduction',
        ]);
        AgentDeduction::create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'agent_id'  => $this->agent->id,
            'date'      => now()->toDateString(),
            'account'   => AgentDeduction::ACCOUNT_PAID,
            'amount'    => 9999.00,
            'particular'=> 'approval generated paid row',
        ]);

        $response = $this->actingAs($this->user)->get(route('agent_report.index', ['tab' => 'deductions']));

        $response->assertOk()
            ->assertSee('Uniform deduction')
            ->assertDontSee('approval generated paid row');
    }

    #[Test]
    public function bulk_upload_page_and_template_are_available(): void
    {
        $this->actingAs($this->user)->get(route('agent_report.deduction.bulk'))
            ->assertOk()
            ->assertSee('Bulk Upload Deductions');

        $template = $this->actingAs($this->user)->get(route('agent_report.deduction.bulk.template'));
        $template->assertOk();
        $this->assertStringContainsString('Date,Agent,Applicant,Amount,Particular/Description', $template->streamedContent());
    }

    #[Test]
    public function bulk_import_creates_deduction_rows(): void
    {
        $file = $this->csv([
            now()->toDateString() . ',Juan Dela Cruz,"Dela Cruz, Maria",1500.00,Cash advance balance',
            now()->toDateString() . ',Juan Dela Cruz,,250.00,Uniform',
        ]);

        $response = $this->actingAs($this->user)->post(route('agent_report.deduction.bulk.import'), ['csv_file' => $file]);

        $response->assertRedirect(route('agent_report.index', ['tab' => 'deductions']));
        $response->assertSessionHas('success');

        $this->assertSame(2, AgentDeduction::where('account', AgentDeduction::ACCOUNT_DEDUCTION)->count());
        $this->assertDatabaseHas('agent_deductions', [
            'agency_id'    => $this->agency->id,
            'agent_id'     => $this->agent->id,
            'applicant_id' => $this->applicant->id,
            'amount'       => 1500.00,
            'particular'   => 'Cash advance balance',
            'account'      => AgentDeduction::ACCOUNT_DEDUCTION,
        ]);
    }

    #[Test]
    public function bulk_import_is_all_or_nothing_when_a_row_is_invalid(): void
    {
        $file = $this->csv([
            now()->toDateString() . ',Juan Dela Cruz,,1500.00,Valid row',
            now()->toDateString() . ',Nobody Here,,250.00,Unknown agent',
        ]);

        $response = $this->actingAs($this->user)->post(route('agent_report.deduction.bulk.import'), ['csv_file' => $file]);

        $response->assertSessionHasErrors('csv_file');
        $this->assertSame(0, AgentDeduction::count());
    }

    #[Test]
    public function bulk_import_rejects_an_applicant_that_belongs_to_another_agent(): void
    {
        $otherAgent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'name'      => 'Pedro Santos',
        ]);
        $otherApplicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $otherAgent->id,
            'first_name' => 'Ana',
            'last_name'  => 'Santos',
        ]);

        $file = $this->csv([
            now()->toDateString() . ',Juan Dela Cruz,"Santos, Ana",500.00,Wrong agent',
        ]);

        $response = $this->actingAs($this->user)->post(route('agent_report.deduction.bulk.import'), ['csv_file' => $file]);

        $response->assertSessionHasErrors('csv_file');
        $this->assertSame(0, AgentDeduction::count());
    }

    #[Test]
    public function re_uploading_the_same_sheet_does_not_create_duplicates(): void
    {
        $row = now()->toDateString() . ',Juan Dela Cruz,,1500.00,Cash advance balance';

        $this->actingAs($this->user)
            ->post(route('agent_report.deduction.bulk.import'), ['csv_file' => $this->csv([$row])])
            ->assertSessionHas('success');

        $this->actingAs($this->user)
            ->post(route('agent_report.deduction.bulk.import'), ['csv_file' => $this->csv([$row])])
            ->assertSessionHasErrors('csv_file');

        $this->assertSame(1, AgentDeduction::count());
    }
}
