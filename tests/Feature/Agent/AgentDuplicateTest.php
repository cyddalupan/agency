<?php

namespace Tests\Feature\Agent;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\User;
use App\Support\AgentDuplicateChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-11-08 (LANDAS card 6af02dd56cff0b42b7c91ee3): "Add Agent — same
 * notification for the same/almost-same agent (JUAN CRUZ vs JUAN CRUZ STAFF);
 * pop-up displays Name, Branch, Email, Contact, Created By."
 */
class AgentDuplicateTest extends TestCase
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

    private function existing(array $attrs = []): Agent
    {
        return Agent::factory()->create(array_merge([
            'agency_id' => $this->agency->id,
            'name' => 'JUAN CRUZ',
        ], $attrs));
    }

    #[Test]
    public function exact_name_warns_and_confirm_duplicate_creates(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila Branch']);
        $existing = $this->existing([
            'branch_id' => $branch->id,
            'email' => 'juan@example.com',
            'contact' => '09171112222',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('agents.store'), [
            'name' => 'juan cruz',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['duplicate']);
        $dupes = session('duplicate_agents');
        $this->assertNotEmpty($dupes);

        $dupe = $dupes[0];
        foreach (['name', 'branch', 'email', 'contact', 'created_by', 'url', 'reasons'] as $key) {
            $this->assertArrayHasKey($key, $dupe, "Agent duplicate payload is missing '{$key}'.");
        }
        $this->assertSame('JUAN CRUZ', $dupe['name']);
        $this->assertSame('Manila Branch', $dupe['branch']);
        $this->assertSame('juan@example.com', $dupe['email']);
        $this->assertSame('09171112222', $dupe['contact']);
        $this->assertSame($this->user->name, $dupe['created_by']);
        $this->assertSame(route('agents.edit', $existing), $dupe['url']);

        $ok = $this->actingAs($this->user)->post(route('agents.store'), [
            'name' => 'juan cruz',
            'confirm_duplicate' => 1,
        ]);
        $ok->assertRedirect(route('agents.index'));
        $this->assertSame(2, Agent::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function juan_cruz_warns_for_existing_juan_cruz_staff(): void
    {
        $this->existing(['name' => 'JUAN CRUZ STAFF']);

        $matches = AgentDuplicateChecker::find($this->agency->id, ['name' => 'JUAN CRUZ']);
        $this->assertCount(1, $matches);
        $this->assertStringContainsString('Similar name', implode(' ', $matches->first()['reasons']));

        $response = $this->actingAs($this->user)->post(route('agents.store'), ['name' => 'JUAN CRUZ']);
        $response->assertSessionHasErrors(['duplicate']);
        $this->assertSame(0, Agent::where('name', 'JUAN CRUZ')->count());
        $this->assertSame(1, Agent::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function unrelated_name_does_not_warn(): void
    {
        $this->existing(['name' => 'JUAN CRUZ']);

        $response = $this->actingAs($this->user)->post(route('agents.store'), ['name' => 'MARIA SANTOS']);

        $response->assertRedirect(route('agents.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('agents', ['name' => 'MARIA SANTOS', 'agency_id' => $this->agency->id]);
    }

    #[Test]
    public function bulk_row_duplicate_of_existing_and_within_file_are_row_errors(): void
    {
        $this->existing(['name' => 'JUAN CRUZ']);

        $csv = "Name,Contact,Status\n"
            ."JUAN CRUZ,09170000000,Active\n"      // matches existing
            ."PEDRO REYES,09173330000,Active\n"    // ok
            ."PEDRO REYES,09173330000,Active\n";   // duplicate of the previous row

        $response = $this->actingAs($this->user)->post(route('agents.bulk.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('agents.csv', $csv),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('bulk_errors');
        $errors = session('bulk_errors');
        $this->assertCount(2, $errors);
        $this->assertStringContainsString('Possible duplicate', $errors[0]['errors'][0]);
        $this->assertStringContainsString('in this same file', $errors[1]['errors'][0]);

        // All-or-nothing: nothing inserted.
        $this->assertSame(1, Agent::where('agency_id', $this->agency->id)->count());
        $this->assertDatabaseMissing('agents', ['name' => 'PEDRO REYES']);
    }
}
