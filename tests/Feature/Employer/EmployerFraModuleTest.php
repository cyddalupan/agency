<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "FRA Module":
 *   1. Admin — add Delete FRA function.
 *   2. Remove Add FRA & Bulk Upload from Receptionist (staff), Processing
 *      (processor), Paralegal, Branch and Operation users.
 */
class EmployerFraModuleTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Employer $employer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Acme FRA',
        ]);
    }

    private function user(string $type): User
    {
        return User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => $type,
        ]);
    }

    // ---------------------------------------------------------------
    // Task 1: Admin — Delete FRA function
    // ---------------------------------------------------------------

    #[Test]
    public function admin_sees_delete_button_on_index(): void
    {
        $response = $this->actingAs($this->user('admin'))
            ->get(route('employers.index'));

        $response->assertOk();
        $response->assertSee(route('employers.destroy', $this->employer), false);
    }

    #[Test]
    public function admin_sees_delete_button_on_show_page(): void
    {
        $response = $this->actingAs($this->user('admin'))
            ->get(route('employers.show', $this->employer));

        $response->assertOk();
        $response->assertSee(route('employers.destroy', $this->employer), false);
    }

    #[Test]
    public function admin_can_delete_fra(): void
    {
        $response = $this->actingAs($this->user('admin'))
            ->delete(route('employers.destroy', $this->employer));

        $response->assertRedirect(route('employers.index'));
        $this->assertDatabaseMissing('employers', ['id' => $this->employer->id]);
    }

    #[Test]
    public function non_admin_cannot_delete_fra(): void
    {
        $response = $this->actingAs($this->user('billing'))
            ->delete(route('employers.destroy', $this->employer));

        $response->assertForbidden();
        $this->assertDatabaseHas('employers', ['id' => $this->employer->id]);
    }

    #[Test]
    public function rest_of_account_cannot_delete_fra(): void
    {
        foreach (['staff', 'processor', 'paralegal', 'branch', 'operation'] as $type) {
            $response = $this->actingAs($this->user($type))
                ->delete(route('employers.destroy', $this->employer));

            $response->assertForbidden();
        }

        $this->assertDatabaseHas('employers', ['id' => $this->employer->id]);
    }

    // ---------------------------------------------------------------
    // Task 2: Add FRA & Bulk Upload hidden / blocked for the 5 roles
    // ---------------------------------------------------------------

    #[Test]
    public function admin_sees_add_and_bulk_upload_links(): void
    {
        $response = $this->actingAs($this->user('admin'))
            ->get(route('employers.index'));

        $response->assertOk();
        $response->assertSee('Add FRA');
        $response->assertSee('Bulk Upload');
    }

    #[Test]
    public function rest_of_account_does_not_see_add_or_bulk_upload_links(): void
    {
        foreach (['staff', 'processor', 'paralegal', 'branch', 'operation'] as $type) {
            $response = $this->actingAs($this->user($type))
                ->get(route('employers.index'));

            $response->assertOk();
            $response->assertSee('FRAs');                 // list still visible
            $response->assertDontSee('Add FRA');
            $response->assertDontSee('Bulk Upload');
            $response->assertDontSee('Delete FRA');
        }
    }

    #[Test]
    public function rest_of_account_cannot_open_add_or_bulk_pages(): void
    {
        foreach (['staff', 'processor', 'paralegal', 'branch', 'operation'] as $type) {
            $user = $this->user($type);

            $this->actingAs($user)->get(route('employers.create'))->assertForbidden();
            $this->actingAs($user)->get(route('employers.bulk'))->assertForbidden();
            $this->actingAs($user)->get(route('employers.bulk.template'))->assertForbidden();
        }
    }

    #[Test]
    public function rest_of_account_cannot_post_new_fra_or_bulk_import(): void
    {
        $staff = $this->user('staff');

        $this->actingAs($staff)
            ->post(route('employers.store'), ['name' => 'Sneaky FRA'])
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('employers.bulk.import'), [])
            ->assertForbidden();

        $this->assertDatabaseMissing('employers', ['name' => 'Sneaky FRA']);
    }

    #[Test]
    public function admin_can_still_open_add_and_bulk_pages(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->get(route('employers.create'))->assertOk();
        $this->actingAs($admin)->get(route('employers.bulk'))->assertOk();
    }
}
