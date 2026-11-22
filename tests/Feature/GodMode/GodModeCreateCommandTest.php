<?php

namespace Tests\Feature\GodMode;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b0341cf42a2071fc8b38ea5 §5 — `godmode:create`.
 *
 * The password never comes from an argument: a test that passes it as an
 * option is proving the wrong thing. These drive the hidden prompts the way
 * an operator does.
 */
class GodModeCreateCommandTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery-staple';

    #[Test]
    public function it_creates_an_operator_with_a_hidden_password_prompt(): void
    {
        $this->artisan('godmode:create', ['username' => 'landlord'])
            ->expectsQuestion('Password (min 16 chars, input hidden)', self::PASSWORD)
            ->expectsQuestion('Confirm password', self::PASSWORD)
            ->expectsOutputToContain('God mode account created')
            ->assertExitCode(0);

        $user = User::withoutGlobalScopes()->where('username', 'landlord')->firstOrFail();

        $this->assertSame('super_admin', $user->user_type);
        $this->assertSame('active', $user->status);
        $this->assertNull($user->agency_id);
        $this->assertSame('landlord@godmode.local', $user->email);

        // Hashed exactly once by the model cast: the plaintext never lands in
        // the column, and Hash::check proves the login will actually match.
        $this->assertNotSame(self::PASSWORD, $user->password);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));
    }

    #[Test]
    public function the_command_never_asks_for_the_password_as_an_argument(): void
    {
        // A password flag would land in shell history and process listings.
        // The signature has username, --email and --force only.
        $command = $this->app->make(\App\Console\Commands\CreateGodModeAccount::class);
        $definition = $command->getDefinition();

        $this->assertFalse($definition->hasArgument('password'));
        $this->assertFalse($definition->hasOption('password'));

        $this->assertSame(
            ['username'],
            array_values(array_map(fn ($arg) => $arg->getName(), array_filter(
                $definition->getArguments(),
                fn ($arg) => $arg->getName() !== 'command'
            )))
        );
    }

    #[Test]
    public function the_stored_password_is_a_hash_not_the_plaintext(): void
    {
        $this->artisan('godmode:create', ['username' => 'landlord'])
            ->expectsQuestion('Password (min 16 chars, input hidden)', self::PASSWORD)
            ->expectsQuestion('Confirm password', self::PASSWORD)
            ->assertExitCode(0);

        $raw = \Illuminate\Support\Facades\DB::table('users')->where('username', 'landlord')->value('password');

        $this->assertNotSame(self::PASSWORD, $raw);
        $this->assertStringStartsWith('$2y$', $raw);
    }

    #[Test]
    public function it_refuses_without_force_when_a_super_admin_exists(): void
    {
        User::factory()->superAdmin()->create(['agency_id' => null]);

        $this->artisan('godmode:create', ['username' => 'second-god'])
            ->expectsOutputToContain('A super_admin account already exists')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['username' => 'second-god']);
    }

    #[Test]
    public function force_creates_a_second_operator_deliberately(): void
    {
        User::factory()->superAdmin()->create(['agency_id' => null]);

        $this->artisan('godmode:create', ['username' => 'second-god', '--force' => true])
            ->expectsQuestion('Password (min 16 chars, input hidden)', self::PASSWORD)
            ->expectsQuestion('Confirm password', self::PASSWORD)
            ->assertExitCode(0);

        $this->assertSame(2, User::withoutGlobalScopes()->where('user_type', 'super_admin')->count());
    }

    #[Test]
    public function it_rejects_a_password_shorter_than_sixteen_characters(): void
    {
        $this->artisan('godmode:create', ['username' => 'landlord'])
            ->expectsQuestion('Password (min 16 chars, input hidden)', 'short-pw-123')
            ->expectsQuestion('Confirm password', 'short-pw-123')
            ->expectsOutputToContain('at least 16 characters')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['username' => 'landlord']);
    }

    #[Test]
    public function it_rejects_a_password_equal_to_the_username(): void
    {
        $this->artisan('godmode:create', ['username' => 'landlord'])
            ->expectsQuestion('Password (min 16 chars, input hidden)', 'landlordlandlordlandlord')
            ->expectsQuestion('Confirm password', 'landlordlandlordlandlord')
            ->expectsOutputToContain('Choose another')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['username' => 'landlord']);
    }

    #[Test]
    public function it_rejects_the_compromised_card_password(): void
    {
        // 'zenmaster12' is in a card in plaintext and is treated as burned.
        // It is also under the 16-character floor, so it is refused on length
        // before the blocklist; extended to 16 chars it must still be refused.
        $this->artisan('godmode:create', ['username' => 'landlord'])
            ->expectsQuestion('Password (min 16 chars, input hidden)', 'zenmaster12zenmaster12')
            ->expectsQuestion('Confirm password', 'zenmaster12zenmaster12')
            ->expectsOutputToContain('Choose another')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['username' => 'landlord']);
    }

    #[Test]
    public function it_rejects_a_confirmation_mismatch(): void
    {
        $this->artisan('godmode:create', ['username' => 'landlord'])
            ->expectsQuestion('Password (min 16 chars, input hidden)', self::PASSWORD)
            ->expectsQuestion('Confirm password', self::PASSWORD.'x')
            ->expectsOutputToContain('do not match')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['username' => 'landlord']);
    }

    #[Test]
    public function it_refuses_an_existing_username_or_email(): void
    {
        User::factory()->create([
            'agency_id' => null,
            'username'  => 'landlord',
            'email'     => 'landlord@godmode.local',
        ]);

        $this->artisan('godmode:create', ['username' => 'landlord', '--force' => true])
            ->expectsOutputToContain('already exists')
            ->assertExitCode(1);
    }

    #[Test]
    public function it_records_the_creation_on_both_spines(): void
    {
        $this->artisan('godmode:create', ['username' => 'landlord'])
            ->expectsQuestion('Password (min 16 chars, input hidden)', self::PASSWORD)
            ->expectsQuestion('Confirm password', self::PASSWORD)
            ->assertExitCode(0);

        $user = User::withoutGlobalScopes()->where('username', 'landlord')->firstOrFail();

        $this->assertDatabaseHas('activity_logs', ['action' => 'godmode.account_created']);

        $audit = AuditEvent::withoutGlobalScopes()
            ->where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->firstOrFail();

        $this->assertSame(AuditEvent::ACTION_CREATED, $audit->action);
        $this->assertNull($audit->agency_id);
    }

    #[Test]
    public function it_accepts_an_explicit_email(): void
    {
        $this->artisan('godmode:create', ['username' => 'landlord', '--email' => 'ops@example.test'])
            ->expectsQuestion('Password (min 16 chars, input hidden)', self::PASSWORD)
            ->expectsQuestion('Confirm password', self::PASSWORD)
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'username' => 'landlord',
            'email'    => 'ops@example.test',
        ]);
    }

    /** The generated operator can actually log in. */
    #[Test]
    public function the_created_operator_can_sign_in(): void
    {
        $this->artisan('godmode:create', ['username' => 'landlord'])
            ->expectsQuestion('Password (min 16 chars, input hidden)', self::PASSWORD)
            ->expectsQuestion('Confirm password', self::PASSWORD)
            ->assertExitCode(0);

        $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ])->assertRedirect(route('godmode.dashboard'));

        $this->assertTrue(auth('godmode')->check());
    }

    /**
     * Regression (QA, live deploy): the first real run inserted the operator
     * and then crashed on `audit_events.agency_id` NOT NULL, because migration
     * 2026_11_22_000001 was still pending. The user row survived, unaudited,
     * and blocked a clean retry with "a user already exists".
     *
     * The audit write is the failing step here, so the assertion is exact: NO
     * user row may exist afterwards. A command that can leave a super_admin
     * behind with no audit trail is a security hole, not a cosmetic bug.
     */
    #[Test]
    public function a_failure_while_auditing_leaves_no_half_created_operator(): void
    {
        // Make the audit insert fail exactly like the live NOT NULL schema did.
        AuditEvent::creating(function () {
            throw new \RuntimeException('audit_events.agency_id cannot be null');
        });

        $failed = false;

        try {
            $this->artisan('godmode:create', ['username' => 'landlord'])
                ->expectsQuestion('Password (min 16 chars, input hidden)', self::PASSWORD)
                ->expectsQuestion('Confirm password', self::PASSWORD)
                ->assertExitCode(1);
        } catch (\Throwable $e) {
            $failed = true;
            $this->assertStringContainsString('agency_id', $e->getMessage());
        }

        $this->assertTrue($failed, 'the command must not swallow a failed audit write');

        $this->assertSame(
            0,
            User::withoutGlobalScopes()->where('username', 'landlord')->count(),
            'a failed audit write must roll the whole account creation back'
        );
        $this->assertSame(0, \App\Models\ActivityLog::query()->where('action', 'godmode.account_created')->count());
    }
}
