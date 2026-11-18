<?php

namespace Tests\Feature\Security;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * An admin resetting ANOTHER user's password is a sensitive action equivalent
 * to privilege escalation: set the password, log in as that user, act. These
 * tests guard that both admin-side update paths leave an audit record, that
 * the record names the actor, and that the password itself never leaks in.
 *
 * The self-service flow (PasswordResetController) logs `password_reset`; the
 * admin flow must be distinguishable from it, hence `user_password_reset_by_admin`.
 */
class AdminPasswordResetAuditTest extends TestCase
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

    /** Full update payload for users.update (name/email/user_type/status required). */
    private function userPayload(User $user, array $overrides = []): array
    {
        return array_merge([
            'name'      => $user->name,
            'email'     => $user->email,
            'user_type' => $user->user_type,
            'status'    => 'active',
        ], $overrides);
    }

    /** Everything the log row exposes, as one string, for leak assertions. */
    private function logPersistence(ActivityLog $log): string
    {
        return $log->action
            . '|' . (string) $log->description
            . '|' . json_encode($log->metadata);
    }

    private function assertPasswordNotLeaked(ActivityLog $log, string $plaintext): void
    {
        $blob = $this->logPersistence($log);

        $this->assertStringNotContainsString($plaintext, $blob, 'Plaintext password leaked into the audit log.');
        $this->assertStringNotContainsString('$2y$', $blob, 'A bcrypt hash leaked into the audit log.');
        $this->assertStringNotContainsString('$argon2', $blob, 'An argon2 hash leaked into the audit log.');

        // The stored hash must not be present either, even in part.
        $storedHash = User::find($log->subject_id)?->password;
        if ($storedHash) {
            $this->assertStringNotContainsString($storedHash, $blob, 'The stored password hash leaked into the audit log.');
        }
    }

    // ─── ADMIN RESETS VIA users.update ───────────────────────────────

    #[Test]
    public function admin_password_reset_is_logged_with_actor_and_target(): void
    {
        $target = $this->target();

        $this->actingAs($this->admin)
            ->put(route('users.update', $target), $this->userPayload($target, [
                'password'              => 'new-secret-123',
                'password_confirmation' => 'new-secret-123',
            ]));

        $log = ActivityLog::where('action', 'user_password_reset_by_admin')->latest()->first();

        $this->assertNotNull($log);
        $this->assertSame($this->agency->id, $log->agency_id);
        $this->assertSame($this->admin->id, $log->user_id);
        $this->assertSame(User::class, $log->subject_type);
        $this->assertSame($target->id, $log->subject_id);
    }

    #[Test]
    public function admin_password_reset_metadata_names_target_and_actor(): void
    {
        $target = $this->target();

        $this->actingAs($this->admin)
            ->put(route('users.update', $target), $this->userPayload($target, [
                'password'              => 'new-secret-123',
                'password_confirmation' => 'new-secret-123',
            ]));

        $log = ActivityLog::where('action', 'user_password_reset_by_admin')->latest()->first();

        $this->assertNotNull($log);
        $this->assertSame($target->email, $log->metadata['target_email']);
        $this->assertSame($this->admin->id, $log->metadata['reset_by']);
        $this->assertStringContainsString($this->admin->name, $log->description);
    }

    #[Test]
    public function admin_password_reset_never_logs_the_password_or_its_hash(): void
    {
        $target = $this->target();
        $plaintext = 'new-secret-123';

        $this->actingAs($this->admin)
            ->put(route('users.update', $target), $this->userPayload($target, [
                'password'              => $plaintext,
                'password_confirmation' => $plaintext,
            ]));

        $log = ActivityLog::where('action', 'user_password_reset_by_admin')->latest()->first();

        $this->assertNotNull($log);
        $this->assertPasswordNotLeaked($log, $plaintext);

        // Sanity: the password actually changed, so the absence above is meaningful.
        $this->assertTrue(Hash::check($plaintext, $target->fresh()->password));
    }

    #[Test]
    public function updating_a_user_without_a_password_writes_no_reset_log(): void
    {
        $target = $this->target();

        $this->actingAs($this->admin)
            ->put(route('users.update', $target), $this->userPayload($target, [
                'name' => 'Renamed Person',
            ]));

        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Renamed Person']);
        $this->assertDatabaseMissing('activity_logs', [
            'action'     => 'user_password_reset_by_admin',
            'subject_id' => $target->id,
        ]);
    }

    #[Test]
    public function role_and_password_changed_together_write_one_log_each(): void
    {
        $target = $this->target(['user_type' => 'staff']);

        $this->actingAs($this->admin)
            ->put(route('users.update', $target), $this->userPayload($target, [
                'user_type'             => 'manager',
                'password'              => 'new-secret-123',
                'password_confirmation' => 'new-secret-123',
            ]));

        $this->assertSame(1, ActivityLog::where('action', 'role_changed')
            ->where('subject_id', $target->id)->count());
        $this->assertSame(1, ActivityLog::where('action', 'user_password_reset_by_admin')
            ->where('subject_id', $target->id)->count());

        $roleLog = ActivityLog::where('action', 'role_changed')->where('subject_id', $target->id)->first();
        $this->assertSame('staff', $roleLog->metadata['old_role']);
        $this->assertSame('manager', $roleLog->metadata['new_role']);
    }

    #[Test]
    public function admin_reset_is_distinguishable_from_self_service_reset(): void
    {
        $target = $this->target();

        $this->actingAs($this->admin)
            ->put(route('users.update', $target), $this->userPayload($target, [
                'password'              => 'new-secret-123',
                'password_confirmation' => 'new-secret-123',
            ]));

        $log = ActivityLog::where('subject_id', $target->id)->latest()->first();

        $this->assertNotNull($log);
        $this->assertNotSame('password_reset', $log->action);
        // A prefix-anchored auditor query for self-service resets must not catch this.
        $this->assertSame(0, ActivityLog::where('action', 'like', 'password_reset%')
            ->where('subject_id', $target->id)->count());
    }

    #[Test]
    public function no_reset_log_is_written_when_the_update_fails(): void
    {
        // The log must fire AFTER the write. If it fired before, a failed save
        // (DB error, constraint violation, an observer throwing) would leave an
        // audit row accusing a named admin of a reset that never happened.
        // That is worse than no log: it is a false record against a person.
        $target = $this->target();

        User::updating(function (User $user) use ($target) {
            if ($user->id === $target->id) {
                throw new \RuntimeException('boom: simulated write failure');
            }
        });

        try {
            $this->actingAs($this->admin)
                ->put(route('users.update', $target), $this->userPayload($target, [
                    'password'              => 'new-secret-123',
                    'password_confirmation' => 'new-secret-123',
                ]));
        } catch (\RuntimeException $e) {
            // expected - the save blew up
        }

        // The write genuinely failed...
        $this->assertFalse(Hash::check('new-secret-123', $target->fresh()->password));

        // ...so there must be no phantom audit entry.
        $this->assertDatabaseMissing('activity_logs', [
            'action'     => 'user_password_reset_by_admin',
            'subject_id' => $target->id,
        ]);
    }

    #[Test]
    public function agency_path_writes_no_reset_log_when_the_update_fails(): void
    {
        $target = $this->target();
        $actor  = User::factory()->superAdmin()->create();

        User::updating(function (User $user) use ($target) {
            if ($user->id === $target->id) {
                throw new \RuntimeException('boom: simulated write failure');
            }
        });

        try {
            $this->actingAs($actor)
                ->put(route('agencies.users.update', [$this->agency, $target]), [
                    'name'                  => $target->name,
                    'email'                 => $target->email,
                    'user_type'             => $target->user_type,
                    'status'                => 'active',
                    'password'              => 'new-secret-123',
                    'password_confirmation' => 'new-secret-123',
                ]);
        } catch (\RuntimeException $e) {
            // expected
        }

        $this->assertFalse(Hash::check('new-secret-123', $target->fresh()->password));
        $this->assertDatabaseMissing('activity_logs', [
            'action'     => 'user_password_reset_by_admin',
            'subject_id' => $target->id,
        ]);
    }

    // ─── ADMIN RESETS VIA agencies.users.update ──────────────────────

    private function viaAgencyUserUpdate(User $target, array $overrides = []): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->put(route('agencies.users.update', [$this->agency, $target]), array_merge([
                'name'      => $target->name,
                'email'     => $target->email,
                'user_type' => $target->user_type,
                'status'    => 'active',
            ], $overrides));
    }

    #[Test]
    public function agency_user_password_reset_is_logged_with_actor_and_target(): void
    {
        $target = $this->target();
        $actor = User::factory()->superAdmin()->create();

        $this->actingAs($actor)
            ->put(route('agencies.users.update', [$this->agency, $target]), [
                'name'      => $target->name,
                'email'     => $target->email,
                'user_type' => $target->user_type,
                'status'    => 'active',
                'password'  => 'new-secret-123',
                'password_confirmation' => 'new-secret-123',
            ]);

        $log = ActivityLog::where('action', 'user_password_reset_by_admin')->latest()->first();

        $this->assertNotNull($log);
        $this->assertSame($actor->id, $log->user_id);
        $this->assertSame($target->id, $log->subject_id);
        $this->assertSame($target->email, $log->metadata['target_email']);
        $this->assertPasswordNotLeaked($log, 'new-secret-123');
    }

    #[Test]
    public function agency_user_update_without_password_writes_no_reset_log(): void
    {
        $target = $this->target();

        $this->viaAgencyUserUpdate($target, ['name' => 'Renamed Member']);

        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Renamed Member']);
        $this->assertDatabaseMissing('activity_logs', [
            'action'     => 'user_password_reset_by_admin',
            'subject_id' => $target->id,
        ]);
    }
}
