<?php

namespace Tests\Feature\FRA;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card "FINAS: FRA Feature" (2026-10-20): an admin can create an FRA account
 * (access level "FRA (Employer)") tied to one employer record, and that login
 * only ever sees its own employer's applicants inside /fra.
 */
class FraAccountTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private Employer $employerA;
    private Employer $employerB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['status' => 'active']);

        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'status' => 'active',
        ]);

        $this->employerA = Employer::create([
            'agency_id' => $this->agency->id,
            'name' => 'ALPHA MANPOWER SAUDI',
            'status' => 'active',
        ]);

        $this->employerB = Employer::create([
            'agency_id' => $this->agency->id,
            'name' => 'BETA CONTRACTING KUWAIT',
            'status' => 'active',
        ]);
    }

    #[Test]
    public function fra_role_is_offered_on_the_create_user_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.create'));

        $response->assertOk();
        $response->assertSee('FRA (Employer)');
        $response->assertSee('ALPHA MANPOWER SAUDI');
        $response->assertSee('BETA CONTRACTING KUWAIT'); // same-agency employers are all listed
    }

    #[Test]
    public function admin_can_create_an_fra_account_tied_to_an_employer(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'FRA',
            'surname' => 'Alpha',
            'email' => 'fra.alpha@example.test',
            'password' => 'Proof12345!',
            'password_confirmation' => 'Proof12345!',
            'user_type' => 'employer',
            'employer_id' => $this->employerA->id,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::where('email', 'fra.alpha@example.test')->firstOrFail();
        $this->assertSame('employer', $user->user_type);
        $this->assertSame($this->employerA->id, $user->employer_id);
        $this->assertSame($this->agency->id, $user->agency_id);
        $this->assertTrue($user->isFraAccount());
    }

    #[Test]
    public function fra_account_requires_an_employer(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'FRA',
            'email' => 'fra.none@example.test',
            'password' => 'Proof12345!',
            'password_confirmation' => 'Proof12345!',
            'user_type' => 'employer',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('employer_id');
        $this->assertNull(User::where('email', 'fra.none@example.test')->first());
    }

    #[Test]
    public function employer_from_another_agency_is_rejected(): void
    {
        $otherAgency = Agency::factory()->create(['status' => 'active']);
        $foreignEmployer = Employer::create([
            'agency_id' => $otherAgency->id,
            'name' => 'FOREIGN EMPLOYER',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'FRA',
            'email' => 'fra.foreign@example.test',
            'password' => 'Proof12345!',
            'password_confirmation' => 'Proof12345!',
            'user_type' => 'employer',
            'employer_id' => $foreignEmployer->id,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('employer_id');
        $this->assertNull(User::where('email', 'fra.foreign@example.test')->first());
    }

    #[Test]
    public function non_fra_role_does_not_keep_an_employer_link(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'employer_id' => $this->employerA->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'user_type' => 'staff',
            'status' => 'active',
        ])->assertRedirect(route('users.index'));

        $this->assertNull($user->fresh()->employer_id);
    }

    #[Test]
    public function fra_account_only_sees_its_own_employers_applicants(): void
    {
        $fra = User::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employerA->id,
            'user_type' => 'employer',
            'status' => 'active',
        ]);

        $mine = $this->applicant('MINE APPLICANT', $this->employerA->id, 1);
        $theirs = $this->applicant('OTHER APPLICANT', $this->employerB->id, 1);

        $response = $this->actingAs($fra)->get(route('fra.lineup'));

        $response->assertOk();
        $response->assertSee('MINE APPLICANT');
        $response->assertDontSee('OTHER APPLICANT');
    }

    #[Test]
    public function fra_account_cannot_open_another_employers_applicant(): void
    {
        $fra = User::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employerA->id,
            'user_type' => 'employer',
            'status' => 'active',
        ]);

        $theirs = $this->applicant('OTHER APPLICANT', $this->employerB->id, 1);

        $this->actingAs($fra)->get(route('fra.lineup.view', $theirs))->assertForbidden();
        $this->actingAs($fra)->post(route('fra.lineup.select', $theirs))->assertForbidden();
    }

    #[Test]
    public function fra_account_cannot_reach_the_agency_panel(): void
    {
        $fra = User::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employerA->id,
            'user_type' => 'employer',
            'status' => 'active',
        ]);

        // FRA accounts never get agency-panel modules (not even Applicants).
        $this->assertFalse($fra->canAccessModule('applicants'));
        $this->assertFalse($fra->canAccessModule('users'));
        $this->assertFalse($fra->canAccessModule('employers'));
    }

    private function applicant(string $name, int $employerId, int $statusCode): Applicant
    {
        return Applicant::create([
            'agency_id' => $this->agency->id,
            'employer_id' => $employerId,
            'first_name' => $name,
            'last_name' => 'TEST',
            'status' => 'active',
            'status_code' => $statusCode,
        ]);
    }
}
