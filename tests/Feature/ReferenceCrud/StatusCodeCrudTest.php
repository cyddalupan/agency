<?php

namespace Tests\Feature\ReferenceCrud;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\StatusCode;
use App\Models\StatusTransition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatusCodeCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_status_codes(): void
    {
        $this->get(route('status-codes.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function non_admin_cannot_access_status_codes(): void
    {
        $agency = Agency::factory()->create();
        $staff = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'staff']);

        $this->actingAs($staff)->get(route('status-codes.index'))->assertForbidden(403);
    }

    #[Test]
    public function index_lists_statuses(): void
    {
        StatusCode::factory()->create(['label' => 'For Deployment']);
        StatusCode::factory()->create(['label' => 'Deployed']);

        $this->actingAs($this->admin)
            ->get(route('status-codes.index'))
            ->assertOk()
            ->assertSee('For Deployment')
            ->assertSee('Deployed');
    }

    #[Test]
    public function store_creates_status_with_code(): void
    {
        $this->actingAs($this->admin)
            ->post(route('status-codes.store'), [
                'code'        => 60,
                'label'       => 'For Tesda',
                'description' => 'Tesda training pending',
                'color'       => '#ff0000',
                'sort_order'  => 60,
            ])
            ->assertRedirect(route('status-codes.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('status_codes', [
            'code'   => 60,
            'label'  => 'For Tesda',
            'color'  => '#ff0000',
        ]);
    }

    #[Test]
    public function store_requires_unique_code(): void
    {
        StatusCode::factory()->create(['code' => 10]);

        $this->actingAs($this->admin)
            ->post(route('status-codes.store'), ['code' => 10, 'label' => 'Duplicate'])
            ->assertSessionHasErrors('code');
    }

    #[Test]
    public function update_changes_status_label(): void
    {
        $status = StatusCode::factory()->create(['label' => 'Old Label']);

        $this->actingAs($this->admin)
            ->put(route('status-codes.update', $status), ['label' => 'New Label'])
            ->assertRedirect(route('status-codes.index'));

        $this->assertDatabaseHas('status_codes', ['code' => $status->code, 'label' => 'New Label']);
    }

    #[Test]
    public function destroy_deletes_unreferenced_status(): void
    {
        $status = StatusCode::factory()->create(['code' => 90]);

        $this->actingAs($this->admin)
            ->delete(route('status-codes.destroy', $status))
            ->assertRedirect(route('status-codes.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('status_codes', ['code' => $status->code]);
    }

    #[Test]
    public function destroy_blocks_status_used_by_transitions(): void
    {
        $from = StatusCode::factory()->create(['code' => 91]);
        $to = StatusCode::factory()->create(['code' => 92]);
        StatusTransition::create(['from_code' => 91, 'to_code' => 92, 'is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->delete(route('status-codes.destroy', $from))
            ->assertRedirect(route('status-codes.index'));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('status_codes', ['code' => 91]);
    }

    #[Test]
    public function destroy_blocks_status_used_by_applicants(): void
    {
        $status = StatusCode::factory()->create(['code' => 93]);
        $agency = Agency::factory()->create();
        Applicant::factory()->create(['agency_id' => $agency->id, 'status_code' => 93]);

        $response = $this->actingAs($this->admin)
            ->delete(route('status-codes.destroy', $status))
            ->assertRedirect(route('status-codes.index'));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('status_codes', ['code' => 93]);
    }
}
