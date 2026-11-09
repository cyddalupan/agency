<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6af170f5 — "LANDAS: Deductions 'Missing Particular'".
 *
 * The bulk uploader only required an Agent column and an Amount column, so a
 * sheet whose description column was named anything it did not know (Remarks,
 * Notes, Deduction Memo, ...) imported cleanly and stored every row with a
 * NULL particular. On Gulf that silently blanked 1000 of 1002 deductions.
 *
 * Fixed two ways: the common description headers are now aliases, and an
 * unrecognised description column stops the upload instead of dropping text.
 */
class AgentDeductionParticularHeaderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $branch = Branch::factory()->create(['agency_id' => $agency->id]);

        $this->admin = User::factory()->create([
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'user_type' => 'admin',
            'status'    => 'active',
            'name'      => 'Mae',
        ]);

        $this->agent = Agent::factory()->create([
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'name'      => 'ANA MARIA ARSENIO',
        ]);
    }

    private function upload(string $csv): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->admin)->post('/agents-report/deductions/bulk/import', [
            'csv_file' => UploadedFile::fake()->createWithContent('deductions.csv', $csv),
        ]);
    }

    #[Test]
    public function the_template_header_stores_the_particular(): void
    {
        $this->upload(
            "Date,Agent,Applicant,Amount,Particular/Description\n"
            . "2026-11-09,{$this->agent->name},,1500.00,Cash advance balance deduction\n"
        )->assertRedirect();

        $this->assertSame(
            'Cash advance balance deduction',
            AgentDeduction::latest('id')->first()->particular
        );
    }

    #[Test]
    #[DataProvider('descriptionHeaderAliases')]
    public function common_description_headers_store_the_particular(string $header): void
    {
        $this->upload(
            "Date,Agent,Applicant,Amount,{$header}\n"
            . "2026-11-09,{$this->agent->name},,1500.00,Uniform deduction\n"
        )->assertRedirect();

        $this->assertSame('Uniform deduction', AgentDeduction::latest('id')->first()->particular);
    }

    public static function descriptionHeaderAliases(): array
    {
        return [
            'description' => ['Description'],
            'particular'  => ['Particular'],
            'particulars' => ['Particulars'],
            'remarks'     => ['Remarks'],
            'notes'       => ['Notes'],
            'reason'      => ['Reason'],
            'details'     => ['Details'],
        ];
    }

    #[Test]
    public function an_unrecognised_description_column_blocks_the_upload_instead_of_blanking_it(): void
    {
        $response = $this->upload(
            "Date,Agent,Applicant,Amount,Deduction Memo\n"
            . "2026-11-09,{$this->agent->name},,1500.00,Uniform deduction\n"
        );

        $this->assertSame(0, AgentDeduction::count(), 'No row may be imported when the description column would be dropped.');

                $response->assertSessionHasErrors('csv_file');
        $bag = $response->getSession()->get('errors');
        $message = is_array($bag) ? implode(' ', (array) ($bag['csv_file'] ?? [])) : (string) $bag->first('csv_file');
        $this->assertStringContainsString('Deduction Memo', $message);
    }

    #[Test]
    public function a_sheet_with_no_description_column_still_imports(): void
    {
        $this->upload(
            "Date,Agent,Applicant,Amount\n"
            . "2026-11-09,{$this->agent->name},,1500.00\n"
        )->assertRedirect();

        $this->assertSame(1, AgentDeduction::count());
        $this->assertNull(AgentDeduction::latest('id')->first()->particular);
    }
}
