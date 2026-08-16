<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WithdrawnRepatTabTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_withdrawn_repat(): void
    {
        $response = $this->get(route('applicants.withdrawn'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_view_withdrawn_repat_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.withdrawn'));

        $response->assertOk();
        $response->assertViewHas('applicants');
    }

    #[Test]
    public function only_cancelled_backout_repatriated_applicants_show(): void
    {
        // The three "withdrawn" statuses
        Applicant::factory()->withStatus(38)->create(['agency_id' => $this->agency->id]); // Cancel
        Applicant::factory()->withStatus(50)->create(['agency_id' => $this->agency->id]); // Backout
        Applicant::factory()->withStatus(35)->create(['agency_id' => $this->agency->id]); // Repatriated

        // Other statuses must NOT appear
        Applicant::factory()->withStatus(0)->create(['agency_id' => $this->agency->id]); // Pending
        Applicant::factory()->withStatus(8)->create(['agency_id' => $this->agency->id]); // Deployed

        $response = $this->actingAs($this->user)
            ->get(route('applicants.withdrawn'));

        $response->assertOk();

        foreach (Applicant::whereIn('status_code', [38, 50, 35])->get() as $a) {
            $response->assertSee($a->first_name);
        }

        foreach (Applicant::whereIn('status_code', [0, 8])->get() as $a) {
            $response->assertDontSee($a->first_name);
        }
    }

    #[Test]
    public function page_does_not_show_all_status_chips(): void
    {
        Applicant::factory()->withStatus(0)->create(['agency_id' => $this->agency->id]); // Pending
        Applicant::factory()->withStatus(38)->create(['agency_id' => $this->agency->id]); // Cancel

        $response = $this->actingAs($this->user)
            ->get(route('applicants.withdrawn'));

        $response->assertOk();
        $response->assertSee('Cancel');
        $response->assertSee('Backout');
        $response->assertSee('Repatriated');
        $response->assertDontSee('Pending');
    }

    #[Test]
    public function search_filters_within_withdrawn_statuses(): void
    {
        Applicant::factory()->withStatus(38)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'UniqueNameCancel',
        ]);
        Applicant::factory()->withStatus(50)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'UniqueNameBackout',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.withdrawn', ['search' => 'UniqueNameCancel']));

        $response->assertOk();
        $response->assertSee('UniqueNameCancel');
        $response->assertDontSee('UniqueNameBackout');
    }

    #[Test]
    public function withdrawn_list_respects_branch_scoping(): void
    {
        $branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Branch A']);
        $branchB = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Branch B']);

        $branchUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff', // non-admin branch account → scoped to own branch
            'branch_id' => $branchA->id,
        ]);

        $scoped = Applicant::factory()->withStatus(38)->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $branchA->id,
        ]);
        $otherBranch = Applicant::factory()->withStatus(38)->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $branchB->id,
        ]);

        $response = $this->actingAs($branchUser)
            ->get(route('applicants.withdrawn'));

        $response->assertOk();
        $response->assertSee($scoped->first_name);
        $response->assertDontSee($otherBranch->first_name);
    }

    #[Test]
    public function tenant_cannot_see_other_tenants_withdrawn_applicants(): void
    {
        $otherAgency = Agency::factory()->create();
        $other = Applicant::factory()->withStatus(38)->create([
            'agency_id' => $otherAgency->id,
        ]);

        // Simulate tenant context so the TenantScope applies
        app()->instance('tenant_agency', $this->agency);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.withdrawn'));

        $response->assertOk();
        $response->assertDontSee($other->first_name);
    }

    #[Test]
    public function withdrawn_page_shows_empty_state_when_no_matches(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.withdrawn'));

        $response->assertOk();
        $response->assertSee('No applicants');
    }

    #[Test]
    public function withdrawn_export_returns_csv_with_only_three_statuses(): void
    {
        Applicant::factory()->withStatus(38)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'CancelExport',
        ]);
        Applicant::factory()->withStatus(50)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'BackoutExport',
        ]);
        Applicant::factory()->withStatus(35)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'RepatExport',
        ]);
        Applicant::factory()->withStatus(0)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'PendingExport',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.withdrawn.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('CancelExport', $csv);
        $this->assertStringContainsString('BackoutExport', $csv);
        $this->assertStringContainsString('RepatExport', $csv);
        $this->assertStringNotContainsString('PendingExport', $csv);
    }

    #[Test]
    public function withdrawn_export_respects_search_filter(): void
    {
        Applicant::factory()->withStatus(38)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'SearchMatch',
        ]);
        Applicant::factory()->withStatus(38)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'SearchNoMatch',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.withdrawn.export', ['search' => 'SearchMatch']));

        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('SearchMatch', $csv);
        $this->assertStringNotContainsString('SearchNoMatch', $csv);
    }
}
