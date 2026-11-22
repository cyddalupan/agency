<?php

namespace Tests\Feature\GodMode;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Card 6b036f25045ef95278e77e5e — `agencies:remove-empty`.
 *
 * Three things must hold, or the command is a data-loss button:
 *   1. a non-empty agency is refused and left exactly as it was,
 *   2. a dry run (no --force) removes nothing,
 *   3. --force="<exact name>" removes an empty agency and keeps its audit trail.
 */
class RemoveEmptyAgencyTest extends TestCase
{
    use RefreshDatabase;

    private function agency(array $attrs = []): Agency
    {
        return Agency::factory()->create($attrs);
    }

    public function test_refuses_an_agency_with_dependents(): void
    {
        $agency = $this->agency(['name' => 'Has Staff']);

        User::factory()->create(['agency_id' => $agency->id]);

        $this->artisan('agencies:remove-empty', [
            'agency' => $agency->id,
            '--force' => 'Has Staff',
        ])->assertExitCode(1);

        $this->assertDatabaseHas('agencies', ['id' => $agency->id, 'name' => 'Has Staff']);
        $this->assertSame(1, User::withoutGlobalScopes()->where('agency_id', $agency->id)->count());
    }

    public function test_refuses_an_agency_with_applicants_even_when_named_correctly(): void
    {
        $agency = $this->agency(['name' => 'Has Applicants']);

        Applicant::factory()->create(['agency_id' => $agency->id]);

        $this->artisan('agencies:remove-empty', [
            'agency' => $agency->id,
            '--force' => 'Has Applicants',
        ])->assertExitCode(1);

        $this->assertDatabaseHas('agencies', ['id' => $agency->id]);
        $this->assertSame(1, Applicant::withoutGlobalScopes()->where('agency_id', $agency->id)->count());
    }

    public function test_dry_run_removes_nothing(): void
    {
        $agency = $this->agency(['name' => 'Empty One']);

        $this->artisan('agencies:remove-empty', ['agency' => $agency->id])
            ->expectsOutputToContain('Dry run: nothing was removed.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('agencies', ['id' => $agency->id]);
    }

    public function test_wrong_force_name_does_not_delete(): void
    {
        $agency = $this->agency(['name' => 'Exact Name Only']);

        $this->artisan('agencies:remove-empty', [
            'agency' => $agency->id,
            '--force' => 'exact name only',
        ])->assertExitCode(1);

        $this->assertDatabaseHas('agencies', ['id' => $agency->id]);
    }

    public function test_force_removes_an_empty_agency_and_keeps_the_audit_trail(): void
    {
        $agency = $this->agency(['name' => 'Disposable', 'subdomain' => 'disposable']);

        AuditEvent::withoutGlobalScopes()->create([
            'agency_id'     => $agency->id,
            'actor_user_id' => null,
            'action'        => 'created',
            'subject_type'  => Agency::class,
            'subject_id'    => $agency->id,
            'subject_label' => 'Disposable',
            'created_at'    => now(),
        ]);

        $this->artisan('agencies:remove-empty', [
            'agency' => $agency->id,
            '--force' => 'Disposable',
        ])->assertExitCode(0);

        $this->assertDatabaseMissing('agencies', ['id' => $agency->id]);

        // The trail survives, moved out of the tenant scope, plus one `deleted`.
        $kept = AuditEvent::withoutGlobalScopes()->where('subject_id', $agency->id)->whereNull('agency_id')->get();

        $this->assertCount(2, $kept);
        $this->assertSame(
            ['created', 'deleted'],
            $kept->sortBy('id')->pluck('action')->values()->all()
        );

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Agency::class,
            'subject_id'   => $agency->id,
            'action'       => 'godmode.agency_deleted',
        ]);
    }
}
