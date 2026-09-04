<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployerEditTest extends TestCase
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
    public function unauthenticated_user_cannot_access_edit_form(): void
    {
        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->get(route('employers.edit', $employer));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function edit_form_displays_correctly(): void
    {
        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.edit', $employer));

        $response->assertOk();
        $response->assertSee('Edit FRA');
        $response->assertSee($employer->name);
    }

    #[Test]
    public function update_requires_name(): void
    {
        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('employers.update', $employer), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function update_changes_employer(): void
    {
        $employer = Employer::factory()->create([
            'agency_id'  => $this->agency->id,
            'name' => 'Old Name',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('employers.update', $employer), [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ]);

        $response->assertRedirect(route('employers.index'));
        $this->assertDatabaseHas('employers', [
            'id'   => $employer->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    #[Test]
    public function update_preserves_agency_id(): void
    {
        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $this->actingAs($this->user)
            ->put(route('employers.update', $employer), [
                'name' => 'Updated Name',
            ]);

        $this->assertDatabaseHas('employers', [
            'id'        => $employer->id,
            'agency_id' => $this->agency->id,
        ]);
    }
}
