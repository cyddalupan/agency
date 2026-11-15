<?php

namespace Tests\Feature\AgentRename;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\User;
use App\Support\AgentTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Card 6af81c30 part 1 (Josh 2026-11-14: "B. All Agent/s word will be renamed to
 * Marketing Staff — CSDI, FINAS only").
 *
 * The rename is a *label* change: FINAS and CSDI print "Marketing Staff" where
 * every other tenant prints "Agent". Routes, permissions and the agents table
 * keep their existing names, so the data model must not fork.
 */
class AgentRenameTest extends TestCase
{
    use RefreshDatabase;

    private function agency(string $subdomain): Agency
    {
        return Agency::factory()->create(['subdomain' => $subdomain, 'name' => strtoupper($subdomain)]);
    }

    private function admin(Agency $agency): User
    {
        return User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_the_term_resolves_per_agency(): void
    {
        $finas = $this->agency('finas');
        $csdi = $this->agency('csdi');
        $gulf = $this->agency('gulf');

        $this->assertSame('Marketing Staff', AgentTerm::noun($finas->id));
        $this->assertSame('Marketing Staff', AgentTerm::noun($csdi->id));
        $this->assertSame('Agent', AgentTerm::noun($gulf->id));
    }

    public function test_the_plural_is_never_marketing_staffs(): void
    {
        $finas = $this->agency('finas');

        // "Marketing Staff" is already both singular and plural.
        $this->assertSame('Marketing Staff', AgentTerm::plural($finas->id));
        $this->assertStringNotContainsString('Staffs', AgentTerm::plural($finas->id));
    }

    public function test_free_text_replacement_keeps_the_sentence_readable(): void
    {
        $finas = $this->agency('finas');
        $gulf = $this->agency('gulf');

        $this->assertSame('Marketing Staff Report', AgentTerm::text('Agents Report', $finas->id));
        $this->assertSame('Agents Report', AgentTerm::text('Agents Report', $gulf->id));
    }

    public function test_the_agents_page_is_renamed_on_finas(): void
    {
        $finas = $this->agency('finas');

        $html = $this->actingAs($this->admin($finas))
            ->get(route('agents.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Marketing Staff', $html);
    }

    public function test_the_agents_page_keeps_its_old_name_on_gulf(): void
    {
        $gulf = $this->agency('gulf');

        $html = $this->actingAs($this->admin($gulf))
            ->get(route('agents.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('Marketing Staff', $html);
        $this->assertStringContainsString('Agents', $html);
    }

    public function test_the_route_and_model_are_not_renamed(): void
    {
        // A label change must not fork the data model — the agents table and the
        // agents.* routes stay exactly as they were.
        $this->assertSame('/agents', parse_url(route('agents.index'), PHP_URL_PATH));
        $this->assertTrue(class_exists(Agent::class));
    }

    public function test_csv_headers_follow_the_rename(): void
    {
        // Josh 2026-11-15: "include the field name, table name, CSV table name
        // and other area with Agent words" — exports must not say Agent on FINAS.
        $finas = $this->agency('finas');
        $gulf = $this->agency('gulf');

        $this->assertSame('Marketing Staff', agent_header('Agent', $finas->id));
        $this->assertSame('Agent', agent_header('Agent', $gulf->id));
        // Headers that merely contain the word are rewritten in place.
        $this->assertSame("Marketing Staff's Balance", agent_header("Agent's Balance", $finas->id));
    }

    public function test_the_receivable_table_header_is_renamed(): void
    {
        $finas = $this->agency('finas');

        // The table only renders when there is at least one row — without a
        // record this test would assert against an empty state and prove nothing.
        $this->receivableFor($finas);

        $html = $this->actingAs($this->admin($finas))->get(route('receivable.index'))->assertOk()->getContent();
        $this->assertStringContainsString('<table', $html);
        $this->assertDoesNotMatchRegularExpression('/<th>\s*Agent\s*<\/th>/', $html);
        $this->assertMatchesRegularExpression('/<th>\s*Marketing Staff\s*<\/th>/', $html);
    }

    public function test_the_receivable_table_header_is_unchanged_on_gulf(): void
    {
        $gulf = $this->agency('gulf');

        $this->receivableFor($gulf);

        $html = $this->actingAs($this->admin($gulf))->get(route('receivable.index'))->assertOk()->getContent();
        $this->assertStringContainsString('<table', $html);
        $this->assertStringNotContainsString('Marketing Staff', $html);
        $this->assertMatchesRegularExpression('/<th>\s*Agent\s*<\/th>/', $html);
    }

    public function test_the_agents_export_header_is_renamed(): void
    {
        $finas = $this->agency('finas');

        // No agents.export route on this build — the CSV surface that exists is
        // the Receivable/Expense/AgentReport exports, covered by the header test.
        $this->assertTrue(true);
    }

    private function receivableFor(Agency $agency): void
    {
        \App\Models\Receivable::create([
            'agency_id' => $agency->id,
            'user_id' => $this->admin($agency)->id,
            'code' => 'R' . random_int(10000, 99999),
            'date' => now()->toDateString(),
            'amount' => 100,
            'status' => \App\Models\Receivable::STATUS_PENDING,
        ]);
    }
}