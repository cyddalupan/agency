<?php

namespace Tests\Feature\CaseManagement;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Cases;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Case Management API access control (Mjolnir: branch user types).
 *
 * The /api/cases group is now gated with the agency applicant-role list
 * (admin/super_admin + recruiter/staff/processor/coordinator/interviewer/
 * manager/marketer/director/operation/branch/paralegal). Users outside the
 * list (e.g. billing) get 403. Branch-locked users only see / manage cases
 * of applicants in their OWN branch, and store() rejects applicants outside
 * the caller's agency or branch.
 */
class CaseApiAccessTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $branchA;
    private Branch $branchB;
    private User $admin;
    private User $billing;
    private User $paralegal;
    private User $branchUserA;
    private Applicant $applicantA;
    private Applicant $applicantB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'CEBU Branch']);
        $this->branchB = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'MAKATI Branch']);

        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);
        $this->paralegal = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'paralegal',
        ]);
        $this->branchUserA = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'branch',
            'branch_id' => $this->branchA->id,
        ]);

        $this->applicantA = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branchA->id,
        ]);
        $this->applicantB = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branchB->id,
        ]);
    }

    // ─── ROLE GATE ───────────────────────────────────────────────────

    #[Test]
    public function billing_user_is_forbidden_from_cases_api(): void
    {
        // Card 6afe3debac50fd61b181944b: this test was written against a role
        // gate on /api/cases that never existed in routes/web.php, so it sat red
        // with a 200. It is now enforced for real by adding `api` to
        // ModuleAccess::ACCOUNTING_DENIED. The assertion was right; the code
        // was not. Not weakened, not deleted.
        $this->casesForLeakCheck();

        $response = $this->actingAs($this->billing)
            ->getJson('/api/cases');

        $response->assertForbidden();
        $this->assertNoApplicantPayloadLeaked($response->json());
    }

    #[Test]
    public function billing_user_is_forbidden_from_cases_search(): void
    {
        // search() carries the same payload shape as index() and had the same
        // missing gate. Denying the `api` module closes both at once; this test
        // pins the second door so a future refactor cannot reopen just one.
        $this->casesForLeakCheck();

        $response = $this->actingAs($this->billing)
            ->getJson('/api/cases/search?q=Leak');

        $response->assertForbidden();
        $this->assertNoApplicantPayloadLeaked($response->json());
    }

    #[Test]
    public function admin_can_still_list_cases(): void
    {
        // Discrimination, not just closure: a gate that refuses everyone would
        // pass the two 403 tests above and still be broken.
        $this->casesForLeakCheck();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/cases');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Leak probe case']);
    }

    #[Test]
    public function admin_can_still_search_cases(): void
    {
        $this->casesForLeakCheck();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/cases/search?q=Leak');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Leak probe case']);
    }

    /**
     * An applicant whose every identifying field is a sentinel, plus a case
     * that matches the search probe. If a 403 body ever regressed into a
     * redirect-with-flash or a debug payload, the sentinels would show up.
     */
    private function casesForLeakCheck(): void
    {
        $probe = Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'branch_id'  => $this->branchA->id,
            'first_name' => 'Zzyzx',
            'last_name'  => 'Quarantini',
            'email'      => 'zzyzx.quarantini@leak-probe.test',
            'contact'    => '+639000001111',
        ]);

        Cases::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $probe->id,
            'title'        => 'Leak probe case',
        ]);
    }

    /**
     * A refusal must be silent. Asserts on the serialised body, not the status
     * code, so a "403 that still renders the data" fails here.
     */
    private function assertNoApplicantPayloadLeaked(?array $body): void
    {
        $json = json_encode($body ?? []);

        foreach (['Zzyzx', 'Quarantini', 'leak-probe.test', '+639000001111', 'Leak probe case'] as $sentinel) {
            $this->assertStringNotContainsString($sentinel, (string) $json,
                "Billing response leaked applicant data: {$sentinel}"
            );
        }

        // The bcrypt hash is the part that makes this a credential leak rather
        // than a privacy one: Applicant declares no $hidden, so hashes rode
        // along in every row.
        $this->assertStringNotContainsString('$2y$', (string) $json,
            'Billing response leaked a bcrypt password hash.'
        );
    }

    #[Test]
    public function paralegal_can_list_cases(): void
    {
        Cases::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicantA->id,
            'title'        => 'Paralegal visible case',
        ]);

        $response = $this->actingAs($this->paralegal)
            ->getJson('/api/cases');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Paralegal visible case']);
    }

    #[Test]
    public function paralegal_can_create_case(): void
    {
        $response = $this->actingAs($this->paralegal)
            ->postJson('/api/cases', [
                'title'        => 'Paralegal created case',
                'applicant_id' => $this->applicantA->id,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('cases', [
            'title'        => 'Paralegal created case',
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicantA->id,
        ]);
    }

    // ─── BRANCH SCOPING (branch-locked users) ────────────────────────

    #[Test]
    public function branch_locked_user_only_lists_own_branch_cases(): void
    {
        Cases::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicantA->id,
            'title'        => 'Case of branch A',
        ]);
        Cases::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicantB->id,
            'title'        => 'Case of branch B',
        ]);

        $response = $this->actingAs($this->branchUserA)
            ->getJson('/api/cases');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Case of branch A']);
        $response->assertJsonMissing(['title' => 'Case of branch B']);
    }

    #[Test]
    public function branch_locked_user_search_only_finds_own_branch_cases(): void
    {
        Cases::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicantA->id,
            'title'        => 'Searchable own case',
        ]);
        Cases::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicantB->id,
            'title'        => 'Searchable other case',
        ]);

        $response = $this->actingAs($this->branchUserA)
            ->getJson('/api/cases/search?q=Searchable');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Searchable own case']);
        $response->assertJsonMissing(['title' => 'Searchable other case']);
    }

    #[Test]
    public function branch_locked_user_cannot_store_case_for_other_branch_applicant(): void
    {
        $response = $this->actingAs($this->branchUserA)
            ->postJson('/api/cases', [
                'title'        => 'Cross-branch attempt',
                'applicant_id' => $this->applicantB->id,
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('cases', ['title' => 'Cross-branch attempt']);
    }

    #[Test]
    public function branch_locked_user_can_store_case_for_own_branch_applicant(): void
    {
        $response = $this->actingAs($this->branchUserA)
            ->postJson('/api/cases', [
                'title'        => 'Own branch case',
                'applicant_id' => $this->applicantA->id,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('cases', [
            'title'        => 'Own branch case',
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicantA->id,
        ]);
    }

    #[Test]
    public function branch_locked_user_cannot_show_other_branch_case(): void
    {
        $otherCase = Cases::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicantB->id,
            'title'        => 'Other branch case',
        ]);

        $response = $this->actingAs($this->branchUserA)
            ->getJson("/api/cases/{$otherCase->id}");

        $response->assertForbidden();
    }

    #[Test]
    public function branch_locked_user_cannot_update_other_branch_case(): void
    {
        $otherCase = Cases::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicantB->id,
            'title'        => 'Other branch case',
        ]);

        $response = $this->actingAs($this->branchUserA)
            ->putJson("/api/cases/{$otherCase->id}", ['title' => 'Hacked title']);

        $response->assertForbidden();
        $this->assertDatabaseHas('cases', ['id' => $otherCase->id, 'title' => 'Other branch case']);
    }

    #[Test]
    public function branch_locked_user_cannot_destroy_other_branch_case(): void
    {
        $otherCase = Cases::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicantB->id,
            'title'        => 'Other branch case',
        ]);

        $response = $this->actingAs($this->branchUserA)
            ->deleteJson("/api/cases/{$otherCase->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('cases', ['id' => $otherCase->id]);
    }

    #[Test]
    public function branch_locked_user_can_show_own_branch_case(): void
    {
        $ownCase = Cases::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicantA->id,
            'title'        => 'Own branch case',
        ]);

        $response = $this->actingAs($this->branchUserA)
            ->getJson("/api/cases/{$ownCase->id}");

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Own branch case']);
    }

    // ─── CROSS-AGENCY STORE REJECTION ────────────────────────────────

    #[Test]
    public function admin_cannot_store_case_for_applicant_of_another_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherApplicant = Applicant::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/cases', [
                'title'        => 'Cross-agency attempt',
                'applicant_id' => $otherApplicant->id,
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('cases', ['title' => 'Cross-agency attempt']);
    }
}
