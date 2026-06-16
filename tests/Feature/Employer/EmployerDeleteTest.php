<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployerDeleteTest extends TestCase
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
        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->delete(route('employers.destroy', $employer));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_delete_employer(): void
    {
        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('employers.destroy', $employer));

        $response->assertRedirect(route('employers.index'));
        $this->assertDatabaseMissing('employers', ['id' => $employer->id]);
    }
}
