<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantDeleteTest extends TestCase
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

    #[Test]
    public function unauthenticated_user_cannot_delete(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->delete(route('applicants.destroy', $applicant));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_delete_applicant(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Delete',
            'last_name'  => 'Me',
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('applicants.destroy', $applicant));

        $response->assertRedirect(route('applicants.index'));
        $this->assertDatabaseMissing('applicants', ['id' => $applicant->id]);
    }

    #[Test]
    public function delete_removes_record_permanently(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $this->actingAs($this->user)->delete(route('applicants.destroy', $applicant));

        $this->assertDatabaseCount('applicants', 0);
    }
}
