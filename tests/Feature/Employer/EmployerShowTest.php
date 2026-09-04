<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployerShowTest extends TestCase
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
    public function unauthenticated_user_cannot_view_employer(): void
    {
        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->get(route('employers.show', $employer));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_displays_employer_details(): void
    {
        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Tech Corp',
            'email' => 'info@techcorp.com',
            'contact' => '09170000000',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.show', $employer));

        $response->assertOk();
        $response->assertSee('Tech Corp');
        $response->assertSee('info@techcorp.com');
    }

    #[Test]
    public function show_has_edit_button(): void
    {
        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.show', $employer));

        $response->assertSee('Edit');
    }

    #[Test]
    public function show_has_back_button(): void
    {
        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.show', $employer));

        $response->assertSee('Back to FRAs');
    }
}
