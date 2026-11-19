<?php

namespace Tests\Feature\Agency;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6afc416bf5cbaf78c28dc57e: the agency-scoped user screens accepted a
 * password without requiring a confirmation, unlike users.store/users.update.
 * A typo'd password silently locked the user out with no way to notice it at
 * entry time. These tests assert the *behaviour* (the write does not happen and
 * the request comes back with a password error), not the markup — except where
 * the presence of the input is itself the requirement.
 */
class AgencyUserPasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin  = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function target(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ], $attrs));
    }

    /** Full store payload; overrides win. */
    private function storePayload(array $overrides = []): array
    {
        return array_merge([
            'name'      => 'New Staffer',
            'email'     => 'new.staffer@example.com',
            'password'  => 'brand-new-pass-1',
            'user_type' => 'staff',
            'status'    => 'active',
        ], $overrides);
    }

    /** Full update payload; overrides win. */
    private function updatePayload(User $target, array $overrides = []): array
    {
        return array_merge([
            'name'      => $target->name,
            'email'     => $target->email,
            'user_type' => $target->user_type,
            'status'    => 'active',
        ], $overrides);
    }

    // ─── UPDATE ──────────────────────────────────────────────────────

    #[Test]
    public function update_rejects_a_mismatched_password_confirmation(): void
    {
        $target = $this->target();
        $before = $target->password;

        $response = $this->actingAs($this->admin)
            ->put(route('agencies.users.update', [$this->agency, $target]), $this->updatePayload($target, [
                'password'              => 'brand-new-pass-1',
                'password_confirmation' => 'COMPLETELY-DIFFERENT',
            ]));

        $response->assertSessionHasErrors('password');

        $fresh = $target->fresh();
        $this->assertSame($before, $fresh->password, 'Password hash must not change on a mismatch.');
        $this->assertFalse(Hash::check('brand-new-pass-1', $fresh->password));
        $this->assertFalse(Hash::check('COMPLETELY-DIFFERENT', $fresh->password));
    }

    #[Test]
    public function update_rejects_a_password_without_any_confirmation(): void
    {
        $target = $this->target();
        $before = $target->password;

        $response = $this->actingAs($this->admin)
            ->put(route('agencies.users.update', [$this->agency, $target]), $this->updatePayload($target, [
                'password' => 'brand-new-pass-1',
            ]));

        $response->assertSessionHasErrors('password');
        $this->assertSame($before, $target->fresh()->password);
    }

    #[Test]
    public function update_accepts_a_matching_confirmation(): void
    {
        $target = $this->target();

        $response = $this->actingAs($this->admin)
            ->put(route('agencies.users.update', [$this->agency, $target]), $this->updatePayload($target, [
                'password'              => 'brand-new-pass-1',
                'password_confirmation' => 'brand-new-pass-1',
            ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('agencies.show', $this->agency));
        $this->assertTrue(Hash::check('brand-new-pass-1', $target->fresh()->password));
    }

    #[Test]
    public function update_still_allows_saving_without_touching_the_password(): void
    {
        $target = $this->target();
        $before = $target->password;

        $response = $this->actingAs($this->admin)
            ->put(route('agencies.users.update', [$this->agency, $target]), $this->updatePayload($target, [
                'name' => 'Renamed Staffer',
            ]));

        $response->assertSessionHasNoErrors();
        $this->assertSame('Renamed Staffer', $target->fresh()->name);
        $this->assertSame($before, $target->fresh()->password);
    }

    #[Test]
    public function update_still_enforces_the_eight_character_minimum(): void
    {
        $target = $this->target();
        $before = $target->password;

        $response = $this->actingAs($this->admin)
            ->put(route('agencies.users.update', [$this->agency, $target]), $this->updatePayload($target, [
                'password'              => 'short',
                'password_confirmation' => 'short',
            ]));

        $response->assertSessionHasErrors('password');
        $this->assertSame($before, $target->fresh()->password);
    }

    // ─── STORE ───────────────────────────────────────────────────────

    #[Test]
    public function store_rejects_a_mismatched_password_confirmation(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('agencies.users.store', $this->agency), $this->storePayload([
                'password'              => 'brand-new-pass-1',
                'password_confirmation' => 'COMPLETELY-DIFFERENT',
            ]));

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'new.staffer@example.com']);
    }

    #[Test]
    public function store_rejects_a_password_without_any_confirmation(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('agencies.users.store', $this->agency), $this->storePayload([
                'password' => 'brand-new-pass-1',
            ]));

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'new.staffer@example.com']);
    }

    #[Test]
    public function store_accepts_a_matching_confirmation(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('agencies.users.store', $this->agency), $this->storePayload([
                'password'              => 'brand-new-pass-1',
                'password_confirmation' => 'brand-new-pass-1',
            ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('agencies.show', $this->agency));

        $created = User::where('email', 'new.staffer@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue(Hash::check('brand-new-pass-1', $created->password));
    }

    #[Test]
    public function store_still_enforces_the_eight_character_minimum(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('agencies.users.store', $this->agency), $this->storePayload([
                'password'              => 'short',
                'password_confirmation' => 'short',
            ]));

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'new.staffer@example.com']);
    }

    // ─── VIEWS ───────────────────────────────────────────────────────

    #[Test]
    public function edit_view_emits_the_confirmation_field(): void
    {
        $target = $this->target();

        $this->actingAs($this->admin)
            ->get(route('agencies.users.edit', [$this->agency, $target]))
            ->assertOk()
            ->assertSee('name="password_confirmation"', false);
    }

    #[Test]
    public function create_view_emits_the_confirmation_field(): void
    {
        $this->actingAs($this->admin)
            ->get(route('agencies.users.create', $this->agency))
            ->assertOk()
            ->assertSee('name="password_confirmation"', false);
    }
}
