<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployerIndexTest extends TestCase
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
    public function unauthenticated_user_cannot_access_employers(): void
    {
        $response = $this->get(route('employers.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_view_employers_list(): void
    {
        Employer::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.index'));

        $response->assertOk();
        $response->assertSee('Employers');
    }

    #[Test]
    public function employer_list_shows_company_name(): void
    {
        Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Acme Corp',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.index'));

        $response->assertSee('Acme Corp');
    }

    #[Test]
    public function employer_list_paginates(): void
    {
        Employer::factory()->count(25)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.index'));

        $response->assertOk();
    }

    #[Test]
    public function employer_list_is_tenant_scoped(): void
    {
        $agency1 = Agency::factory()->create();
        $agency2 = Agency::factory()->create();

        Employer::factory()->create([
            'agency_id' => $agency1->id,
            'name' => 'Agency One Employer',
        ]);
        Employer::factory()->create([
            'agency_id' => $agency2->id,
            'name' => 'Agency Two Employer',
        ]);

        app()->instance('tenant_agency', $agency1);

        $response = $this->actingAs($this->user)
            ->get(route('employers.index'));

        $response->assertSee('Agency One Employer');
        $response->assertDontSee('Agency Two Employer');
    }
}
