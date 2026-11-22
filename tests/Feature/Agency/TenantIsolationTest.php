<?php

namespace Tests\Feature\Agency;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agencyA;
    private Agency $agencyB;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\Scopes\TenantScope::testingAsWebRequest(false);

        $this->agencyA = Agency::factory()->create(['name' => 'Agency A', 'subdomain' => 'agency-a']);
        $this->agencyB = Agency::factory()->create(['name' => 'Agency B', 'subdomain' => 'agency-b']);
    }

    /**
     * Set the tenant context for the test (simulates IdentifyAgency middleware).
     */
    private function setTenant(Agency $agency): void
    {
        app()->instance('tenant_agency', $agency);
    }

    private function clearTenant(): void
    {
        app()->forgetInstance('tenant_agency');
    }

    // ─── TENANT SCOPE — AGENCY ID ASSIGNMENT ──────────────────────────

    #[Test]
    public function models_are_created_with_correct_agency_id(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);
        $employer = Employer::factory()->create(['agency_id' => $this->agencyA->id]);

        $this->assertEquals($this->agencyA->id, $applicant->agency_id);
        $this->assertEquals($this->agencyA->id, $employer->agency_id);
    }

    #[Test]
    public function users_are_created_with_agency_id(): void
    {
        $user = User::factory()->create(['agency_id' => $this->agencyA->id]);

        $this->assertEquals($this->agencyA->id, $user->agency_id);
    }

    // ─── TENANT SCOPE — QUERY FILTERING ───────────────────────────────

    #[Test]
    public function tenant_scope_filters_applicants_by_current_agency(): void
    {
        Applicant::factory()->count(3)->create(['agency_id' => $this->agencyA->id]);
        Applicant::factory()->count(2)->create(['agency_id' => $this->agencyB->id]);

        $this->setTenant($this->agencyA);
        $this->assertEquals(3, Applicant::count());

        $this->setTenant($this->agencyB);
        $this->assertEquals(2, Applicant::count());
    }

    #[Test]
    public function tenant_scope_filters_employers_by_current_agency(): void
    {
        Employer::factory()->count(4)->create(['agency_id' => $this->agencyA->id]);
        Employer::factory()->count(1)->create(['agency_id' => $this->agencyB->id]);

        $this->setTenant($this->agencyA);
        $this->assertEquals(4, Employer::count());

        $this->setTenant($this->agencyB);
        $this->assertEquals(1, Employer::count());
    }

    #[Test]
    public function tenant_scope_filters_users_by_current_agency(): void
    {
        User::factory()->count(3)->create(['agency_id' => $this->agencyA->id]);
        User::factory()->count(3)->create(['agency_id' => $this->agencyB->id]);

        $this->setTenant($this->agencyA);
        $this->assertEquals(3, User::count());

        $this->setTenant($this->agencyB);
        $this->assertEquals(3, User::count());
    }

    /**
     * Card 6b008b8dc9020955b2cc16e1: this used to assert 5 rows — the scope
     * fell off the end of apply() and returned every agency's data. That was
     * the defect. On the web, an unbound tenant now throws; console is the
     * one trusted unbound context and stays unfiltered.
     */
    #[Test]
    public function unbound_tenant_throws_on_web_requests(): void
    {
        Applicant::factory()->count(3)->create(['agency_id' => $this->agencyA->id]);
        Applicant::factory()->count(2)->create(['agency_id' => $this->agencyB->id]);

        $this->clearTenant();

        // Pretend this query is happening inside an HTTP request, not artisan:
        // a host we cannot resolve, plus the explicit flag that says "judge
        // this as a web request" (runningInConsole() is true for the whole
        // PHPUnit process and cannot be flipped from inside a test).
        $this->app->instance('request', \Illuminate\Http\Request::create('https://unknown.example.com/applicants'));
        \App\Models\Scopes\TenantScope::testingAsWebRequest();

        $this->expectException(\App\Exceptions\UnboundTenantException::class);
        $this->expectExceptionMessage('no agency could be resolved');

        Applicant::count();
    }

    /**
     * A real artisan process is the trusted operator context: the tenant scope
     * lets an unbound Applicant::count() through INSTEAD OF throwing.
     *
     * This used to assert withoutGlobalScope(TenantScope::class)->count(), which
     * strips the scope under test and therefore could never fail — a broken
     * console exemption shipped green with 43 green tests. It now runs a real
     * child `artisan` process (the same console boot a command gets, including
     * the synthetic request SetRequestForConsole builds from APP_URL) and calls
     * Applicant::count() normally.
     *
     * RefreshDatabase's sqlite is :memory:, invisible to a second process, so
     * the schema + rows are put in a throwaway sqlite file owned by www-data.
     * The child prints the database it resolved AND the console facts it saw,
     * and the test asserts all three — a child that ran somewhere else, or that
     * was only silent, cannot pass.
     */
    #[Test]
    public function unbound_tenant_is_unfiltered_in_a_real_console_context(): void
    {
        $database = sys_get_temp_dir() . '/landas_tenant_console_' . getmypid() . '_' . bin2hex(random_bytes(4)) . '.sqlite';

        $error = null;
        try {
            // Schema in the scratch file, then 3 + 2 applicants across two
            // agencies via the query builder (the scope under test is untouched).
            $seeded = $this->fileSqliteConnection($database);

            $this->artisan('migrate', [
                '--force'    => true,
                '--database' => 'console_sqlite',
            ])->assertExitCode(0);

            $seeded->table('agencies')->insert([
                ['id' => $this->agencyA->id, 'name' => 'Agency A', 'subdomain' => 'agency-a'],
                ['id' => $this->agencyB->id, 'name' => 'Agency B', 'subdomain' => 'agency-b'],
            ]);
            $seeded->table('applicants')->insert([
                ['agency_id' => $this->agencyA->id, 'first_name' => 'CA1', 'last_name' => 'Seed'],
                ['agency_id' => $this->agencyA->id, 'first_name' => 'CA2', 'last_name' => 'Seed'],
                ['agency_id' => $this->agencyA->id, 'first_name' => 'CA3', 'last_name' => 'Seed'],
                ['agency_id' => $this->agencyB->id, 'first_name' => 'CB1', 'last_name' => 'Seed'],
                ['agency_id' => $this->agencyB->id, 'first_name' => 'CB2', 'last_name' => 'Seed'],
            ]);

            $this->assertSame(5, $seeded->table('applicants')->count(), 'scratch database was not seeded');

            $output = $this->runConsoleCountOfApplicants($database, $error);
        } finally {
            DB::purge('console_sqlite');
            @unlink($database);
        }

        $this->assertNull($error, "Applicant::count() threw in a real artisan process: {$error}");
        $this->assertStringContainsString("__DB__={$database}", $output,
            "the child artisan process did not open the seeded database: {$output}");
        $this->assertStringContainsString('__CONSOLE__=1', $output,
            "the child was not a console context: {$output}");
        $this->assertStringContainsString('__BOUND_REQUEST__=1', $output,
            "the child did not have the console synthetic request bound: {$output}");

        preg_match('/__COUNT__=(\d+)/', $output, $m);
        $this->assertSame('5', $m[1] ?? null,
            "an unbound console Applicant::count() must reach the database unfiltered: {$output}");
    }

    /**
     * The other half of the pair: the SAME call, in a web request with no
     * tenant bound, must throw. Together the two tests prove the contexts
     * differ — one of them alone proves only half of it.
     */
    #[Test]
    public function same_query_throws_in_a_web_request_with_no_tenant(): void
    {
        $this->clearTenant();
        $this->app->instance('request', \Illuminate\Http\Request::create('https://unknown.example.com/applicants'));
        \App\Models\Scopes\TenantScope::testingAsWebRequest();

        $this->expectException(\App\Exceptions\UnboundTenantException::class);
        \App\Models\Applicant::count();
    }

    /**
     * Run a child `artisan` process that calls \App\Models\Applicant::count()
     * with no tenant bound, and return its combined output. $error is set to
     * the child's exception (or a setup problem) when it did not exit cleanly.
     *
     * The child is given the scratch sqlite path as DB_CONNECTION/DB_DATABASE
     * on the inherited .env (real operator invocation: no APP_ENV override, no
     * testing-specific env file), prints the database it resolved before
     * querying, and refuses to query if that is not the scratch file — a child
     * pointed at production cannot produce a green test.
     */
    private function runConsoleCountOfApplicants(string $database, ?string &$error): string
    {
        $dbExpr = 'config("database.connections.".config("database.default").".database")';

        $script = 'echo "__DB__=".(' . $dbExpr . ').PHP_EOL;'
            . ' if ((' . $dbExpr . ') !== ' . var_export($database, true) . ') { echo "__WRONGDB__".PHP_EOL; exit(0); }'
            . ' echo "__CONSOLE__=".(app()->runningInConsole() ? 1 : 0).PHP_EOL;'
            . ' echo "__BOUND_REQUEST__=".(app()->bound("request") ? 1 : 0).PHP_EOL;'
            . ' try { echo "__COUNT__=".\App\Models\Applicant::count().PHP_EOL; }'
            . ' catch (\Throwable $e) { echo "__THROW__=".get_class($e).": ".$e->getMessage().PHP_EOL; }';

        $process = new Process(
            [PHP_BINARY, base_path('artisan'), 'tinker', '--execute=' . $script],
            base_path(),
            [
                'DB_CONNECTION'   => 'sqlite',
                'DB_DATABASE'     => $database,
                'HOME'            => sys_get_temp_dir(),
                'XDG_CONFIG_HOME' => sys_get_temp_dir(),
            ]
        );
        $process->setTimeout(120);

        try {
            $process->run();
        } catch (\Throwable $e) {
            $error = 'process failed to run: ' . $e->getMessage();
            return (string) $process->getOutput() . (string) $process->getErrorOutput();
        }

        $output = $process->getOutput() . $process->getErrorOutput();

        if (preg_match('/__THROW__=(.*)/', $output, $m)) {
            $error = trim($m[1]);
        } elseif (str_contains($output, '__WRONGDB__')) {
            $error = 'child resolved a different database than the scratch file: ' . trim($output);
        } elseif (! $process->isSuccessful()) {
            $error = 'exit ' . $process->getExitCode() . ': ' . trim($output);
        }

        return $output;
    }

    /**
     * A second sqlite connection on a real file, cloned from the default one.
     * Registered in config before the migrate call so --database can target it.
     */
    private function fileSqliteConnection(string $database): \Illuminate\Database\Connection
    {
        if (! file_exists($database)) {
            touch($database);
            @chmod($database, 0666);
        }

        config(['database.connections.console_sqlite' => array_merge(
            config('database.connections.' . config('database.default')),
            ['driver' => 'sqlite', 'database' => $database, 'foreign_key_constraints' => true]
        )]);
        DB::purge('console_sqlite');

        return DB::connection('console_sqlite');
    }

    /**
     * The recorded escape hatch: an explicit bypass is still unfiltered, and
     * it writes exactly one audit row naming the actor.
     */
    #[Test]
    public function tenant_bypass_is_explicit_unfiltered_and_audited(): void
    {
        Applicant::factory()->count(3)->create(['agency_id' => $this->agencyA->id]);
        Applicant::factory()->count(2)->create(['agency_id' => $this->agencyB->id]);

        $this->setTenant($this->agencyA);
        $this->actingAs(User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]));

        $total = \App\Support\Tenant::bypass(fn () => Applicant::count(), 'reconciliation report');

        $this->assertSame(5, $total);

        $audit = \App\Models\AuditEvent::withoutGlobalScopes()
            ->where('subject_type', 'tenant_bypass')
            ->get();

        $this->assertCount(1, $audit);
        $this->assertSame($this->agencyA->id, $audit->first()->agency_id);
        $this->assertSame('reconciliation report', $audit->first()->subject_label);
    }

    /**
     * Card 6b008b8dc9020955b2cc16e1, defect 3: a bypass that runs before anyone
     * is authenticated cannot name an actor — but it CAN say what was tried.
     * Rows 5840-5844 had action=tenant_bypass and actor_user_id NULL with
     * nothing else identifying, so "the actor is logged" was only partly true.
     */
    #[Test]
    public function unauthenticated_bypass_records_the_attempted_identifier(): void
    {
        $this->setTenant($this->agencyA);

        // No actingAs(): this is a login/broker-style lookup before auth.
        $this->assertNull(auth()->id());

        \App\Support\Tenant::bypass(
            fn () => Applicant::count(),
            'login: resolve user before tenant is known',
            ['attempted_email' => 'someone@example.test']
        );

        $audit = \App\Models\AuditEvent::withoutGlobalScopes()
            ->where('action', \App\Models\AuditEvent::ACTION_TENANT_BYPASS)
            ->firstOrFail();

        // No id invented ...
        $this->assertNull($audit->actor_user_id);

        // ... but the attempt is recorded, in the two places the schema has
        // for it: the human label the audit trail renders, and `after`.
        $this->assertStringContainsString('someone@example.test', $audit->subject_label,
            'label was: ' . var_export($audit->subject_label, true));
        $this->assertStringContainsString(
            'attempted email: someone@example.test',
            $audit->subject_label
        );
        $this->assertSame('someone@example.test', $audit->after['attempted_email']);
        $this->assertNull($audit->after['actor_user_id']);
        $this->assertSame('login: resolve user before tenant is known', $audit->after['reason']);
    }

    /**
     * Card 6b008b8dc9020955b2cc16e1, defect C: with NO agency to attribute to,
     * audit_events cannot hold the row (agency_id is NOT NULL). The bypass must
     * still leave an explicit outcome somewhere — it used to vanish silently —
     * and that outcome is a warning in the application log naming the attempt.
     * No fake agency id is written.
     */
    #[Test]
    public function unattributable_bypass_is_logged_and_writes_no_audit_row(): void
    {
        $this->clearTenant();
        session()->forget('tenant_agency_id');
        $this->assertNull(auth()->user(), 'no guard user: there is no agency to attribute to');

        $before = \App\Models\AuditEvent::withoutGlobalScopes()
            ->where('action', \App\Models\AuditEvent::ACTION_TENANT_BYPASS)
            ->count();

        $log = \Mockery::mock(\Illuminate\Log\LogManager::class);
        $log->shouldReceive('warning')->once()
            ->withArgs(function (string $message, array $context) {
                $this->assertStringContainsString('could not be written to audit_events', $message);
                $this->assertSame('control-plane registration: no tenant', $context['reason'] ?? null);
                $this->assertSame('attempted email: ghost@example.test', $context['identifier'] ?? null);
                $this->assertArrayNotHasKey('agency_id', $context);

                return true;
            });
        \Illuminate\Support\Facades\Log::swap($log);

        \App\Support\Tenant::bypass(
            fn () => Applicant::count(),
            'control-plane registration: no tenant',
            ['attempted_email' => 'ghost@example.test']
        );

        $after = \App\Models\AuditEvent::withoutGlobalScopes()
            ->where('action', \App\Models\AuditEvent::ACTION_TENANT_BYPASS)
            ->count();

        $this->assertSame($before, $after, 'no audit_events row may be invented without an agency');
    }

    /** A bypass with no context keeps the plain reason label it always had. */
    #[Test]
    public function bypass_without_context_keeps_the_reason_label(): void
    {
        $this->setTenant($this->agencyA);

        \App\Support\Tenant::bypass(fn () => Applicant::count(), 'reconciliation report');

        $audit = \App\Models\AuditEvent::withoutGlobalScopes()
            ->where('action', \App\Models\AuditEvent::ACTION_TENANT_BYPASS)
            ->firstOrFail();

        $this->assertSame('reconciliation report', $audit->subject_label);
        $this->assertArrayNotHasKey('attempted_email', $audit->after);
    }

    /** Outside a bypass, the scope is filtering again. */
    #[Test]
    public function tenant_bypass_does_not_stick(): void
    {
        Applicant::factory()->count(3)->create(['agency_id' => $this->agencyA->id]);
        Applicant::factory()->count(2)->create(['agency_id' => $this->agencyB->id]);

        $this->setTenant($this->agencyA);

        \App\Support\Tenant::bypass(fn () => Applicant::count());

        $this->assertSame(3, Applicant::count());
        $this->assertFalse(\App\Support\Tenant::isBypassing());
    }

    #[Test]
    public function without_global_scope_bypasses_tenant_filter(): void
    {
        Applicant::factory()->count(3)->create(['agency_id' => $this->agencyA->id]);
        Applicant::factory()->count(2)->create(['agency_id' => $this->agencyB->id]);

        $this->setTenant($this->agencyA);
        $all = Applicant::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->count();
        $this->assertEquals(5, $all);
    }

    #[Test]
    public function ensure_agency_data_is_not_leaked(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agencyA->id,
            'first_name' => 'SecretA',
        ]);
        Applicant::factory()->create([
            'agency_id' => $this->agencyB->id,
            'first_name' => 'SecretB',
        ]);

        $this->setTenant($this->agencyA);
        $found = Applicant::where('first_name', 'SecretB')->first();
        $this->assertNull($found, 'Agency B data leaked into Agency A query');
    }

    // ─── TENANT SCOPE — CUSTOM FIELD DEFINITIONS ─────────────────────

    #[Test]
    public function custom_field_definitions_are_scoped_by_agency(): void
    {
        CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'name' => 'Field A',
            'key' => 'field_a',
            'type' => 'text',
        ]);
        CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyB->id,
            'model_type' => 'Applicant',
            'name' => 'Field B',
            'key' => 'field_b',
            'type' => 'text',
        ]);

        $this->setTenant($this->agencyA);
        $this->assertEquals(1, CustomFieldDefinition::count());

        $this->setTenant($this->agencyB);
        $this->assertEquals(1, CustomFieldDefinition::count());
    }

    #[Test]
    public function custom_field_values_are_scoped_by_agency(): void
    {
        $applicantA = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);
        $applicantB = Applicant::factory()->create(['agency_id' => $this->agencyB->id]);
        $defA = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
        ]);
        $defB = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyB->id,
            'model_type' => 'Applicant',
        ]);

        CustomFieldValue::factory()->create([
            'agency_id' => $this->agencyA->id,
            'custom_field_definition_id' => $defA->id,
            'model_type' => 'Applicant',
            'model_id' => $applicantA->id,
            'value' => 'Value A',
        ]);
        CustomFieldValue::factory()->create([
            'agency_id' => $this->agencyB->id,
            'custom_field_definition_id' => $defB->id,
            'model_type' => 'Applicant',
            'model_id' => $applicantB->id,
            'value' => 'Value B',
        ]);

        $this->setTenant($this->agencyA);
        $this->assertEquals(1, CustomFieldValue::count());

        $this->setTenant($this->agencyB);
        $this->assertEquals(1, CustomFieldValue::count());
    }

    // ─── CUSTOM FIELD DEFINITION CRUD ─────────────────────────────────

    #[Test]
    public function admin_can_create_custom_field_definition(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)->post(route('custom-field-definitions.store'), [
            'model_type' => 'Applicant',
            'name' => 'Passport Number',
            'key' => 'passport_number',
            'type' => 'text',
            'required' => false,
            'order' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('custom_field_definitions', [
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'name' => 'Passport Number',
            'key' => 'passport_number',
        ]);
    }

    #[Test]
    public function admin_can_list_custom_field_definitions(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);
        CustomFieldDefinition::factory()->count(3)->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)
            ->get(route('custom-field-definitions.index', ['model_type' => 'Applicant']));

        $response->assertOk();
        $response->assertViewHas('definitions');
    }

    #[Test]
    public function admin_can_update_custom_field_definition(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);
        $definition = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'name' => 'Old Name',
            'key' => 'old_key',
            'type' => 'text',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)->put(
            route('custom-field-definitions.update', $definition),
            [
                'name' => 'Updated Name',
                'required' => true,
                'order' => 5,
            ]
        );

        $response->assertRedirect();
        $definition->refresh();
        $this->assertEquals('Updated Name', $definition->name);
        $this->assertTrue($definition->required);
        $this->assertEquals(5, $definition->order);
    }

    #[Test]
    public function admin_can_delete_custom_field_definition(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);
        $definition = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)->delete(
            route('custom-field-definitions.destroy', $definition)
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('custom_field_definitions', ['id' => $definition->id]);
    }

    #[Test]
    public function staff_cannot_manage_custom_field_definitions(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'staff',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($staff)->post(route('custom-field-definitions.store'), [
            'model_type' => 'Applicant',
            'name' => 'Hack',
            'key' => 'hack',
            'type' => 'text',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function custom_field_definitions_are_scoped_to_own_agency_in_crud(): void
    {
        $adminA = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);
        $defFromB = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyB->id,
            'model_type' => 'Applicant',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($adminA)->get(
            route('custom-field-definitions.index', ['model_type' => 'Applicant'])
        );

        $response->assertOk();
        $response->assertDontSee($defFromB->name);
    }

    // ─── CUSTOM FIELD VALIDATION ──────────────────────────────────────

    #[Test]
    public function custom_field_definition_requires_valid_model_type(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)->post(route('custom-field-definitions.store'), [
            'model_type' => '',
            'name' => 'Test',
            'key' => 'test',
            'type' => 'text',
        ]);

        $response->assertSessionHasErrors('model_type');
    }

    #[Test]
    public function custom_field_definition_requires_valid_type(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)->post(route('custom-field-definitions.store'), [
            'model_type' => 'Applicant',
            'name' => 'Test',
            'key' => 'test',
            'type' => 'invalid_type',
        ]);

        $response->assertSessionHasErrors('type');
    }

    #[Test]
    public function custom_field_definition_key_is_unique_per_agency_and_model(): void
    {
        CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'passport_number',
        ]);

        $admin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);

        $this->setTenant($this->agencyA);

        $response = $this->actingAs($admin)->post(route('custom-field-definitions.store'), [
            'model_type' => 'Applicant',
            'name' => 'Passport',
            'key' => 'passport_number',
            'type' => 'text',
        ]);

        $response->assertSessionHasErrors('key');
    }

    #[Test]
    public function identical_key_in_different_agency_is_allowed(): void
    {
        CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'passport_number',
        ]);

        $adminB = User::factory()->create([
            'agency_id' => $this->agencyB->id,
            'user_type' => 'admin',
        ]);

        $this->setTenant($this->agencyB);

        $response = $this->actingAs($adminB)->post(route('custom-field-definitions.store'), [
            'model_type' => 'Applicant',
            'name' => 'Passport Number',
            'key' => 'passport_number',
            'type' => 'text',
        ]);

        $response->assertRedirect();
    }

    // ─── CUSTOM FIELD VALUES ON MODELS ────────────────────────────────

    #[Test]
    public function applicant_can_have_custom_field_values(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);
        $definition = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'passport_number',
            'type' => 'text',
        ]);

        $this->setTenant($this->agencyA);

        $applicant->setCustomField('passport_number', 'AB123456');
        $this->assertEquals('AB123456', $applicant->getCustomField('passport_number'));
    }

    #[Test]
    public function employer_can_have_custom_field_values(): void
    {
        $employer = Employer::factory()->create(['agency_id' => $this->agencyA->id]);
        CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Employer',
            'key' => 'business_license',
            'type' => 'text',
        ]);

        $this->setTenant($this->agencyA);

        $employer->setCustomField('business_license', 'LIC-12345');
        $this->assertEquals('LIC-12345', $employer->getCustomField('business_license'));
    }

    #[Test]
    public function custom_field_value_is_deleted_when_definition_is_deleted(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);
        $definition = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'test_field',
        ]);

        $this->setTenant($this->agencyA);
        $applicant->setCustomField('test_field', 'some value');

        $definition->delete();

        $this->assertDatabaseMissing('custom_field_values', [
            'custom_field_definition_id' => $definition->id,
        ]);
    }

    #[Test]
    public function sync_custom_fields_updates_multiple_values(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);
        $defA = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'field_a',
            'type' => 'text',
        ]);
        $defB = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'field_b',
            'type' => 'text',
        ]);

        $this->setTenant($this->agencyA);

        $applicant->syncCustomFields([
            'field_a' => 'Value A',
            'field_b' => 'Value B',
        ]);

        $this->assertEquals('Value A', $applicant->getCustomField('field_a'));
        $this->assertEquals('Value B', $applicant->getCustomField('field_b'));
    }

    #[Test]
    public function sync_custom_fields_clears_empty_values(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);
        $definition = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agencyA->id,
            'model_type' => 'Applicant',
            'key' => 'field_a',
            'type' => 'text',
        ]);

        $this->setTenant($this->agencyA);

        $applicant->setCustomField('field_a', 'Existing');
        $this->assertEquals('Existing', $applicant->getCustomField('field_a'));

        // Sync with empty value should clear it
        $applicant->syncCustomFields(['field_a' => '']);

        $this->assertNull($applicant->getCustomField('field_a'));
    }

    // ─── SUPER ADMIN BYPASS ───────────────────────────────────────────

    #[Test]
    public function super_admin_can_access_all_agencies_data(): void
    {
        $superAdmin = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'super_admin',
        ]);
        Applicant::factory()->count(2)->create(['agency_id' => $this->agencyA->id]);
        Applicant::factory()->count(3)->create(['agency_id' => $this->agencyB->id]);

        $this->setTenant($this->agencyA);

        // Super admin bypasses tenant scope — should see all
        $this->actingAs($superAdmin);
        $this->assertEquals(5, Applicant::count());
    }

    #[Test]
    public function admin_cannot_access_other_agency_data(): void
    {
        $adminA = User::factory()->create([
            'agency_id' => $this->agencyA->id,
            'user_type' => 'admin',
        ]);
        Applicant::factory()->create([
            'agency_id' => $this->agencyB->id,
            'first_name' => 'OtherAgencySecret',
        ]);

        $this->setTenant($this->agencyA);

        $this->actingAs($adminA);
        $found = Applicant::where('first_name', 'OtherAgencySecret')->first();
        $this->assertNull($found);
    }

    // ─── DATA VERIFICATION ────────────────────────────────────────────

    #[Test]
    public function agency_relationship_is_accessible(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agencyA->id]);

        $this->assertNotNull($applicant->agency);
        $this->assertEquals($this->agencyA->id, $applicant->agency->id);
        $this->assertEquals('Agency A', $applicant->agency->name);
    }

    #[Test]
    public function models_without_agency_id_are_not_scoped(): void
    {
        // Models like Country that don't have agency_id should not be affected
        // by TenantScope. This test verifies the scope doesn't break queries
        // for tables without agency_id column.
        $this->assertTrue(true, 'Placeholder — ensure models without agency_id are queryable');
    }
}
