<?php

namespace App\Console\Commands;

use App\Models\AuditEvent;
use App\Models\User;
use App\Services\SensitiveActionLogger;
use App\Support\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

/**
 * Card 6b0341cf42a2071fc8b38ea5 (§5) — mint the first god-mode operator.
 *
 * The password is NEVER an argument, a seeder value, or a `.env` default: it
 * is typed at a hidden prompt, so it does not land in shell history, process
 * listings, a repository, or a card. It is handed to `User::create()` as
 * plaintext and the model's `hashed` cast (the only hashing path in the app)
 * hashes it once. Calling Hash::make() here would double-hash and produce an
 * account that silently never logs in.
 *
 * Run:  php artisan godmode:create landlord
 *       php artisan godmode:create landlord --email=ops@example.com
 *       php artisan godmode:create second --force
 */
class CreateGodModeAccount extends Command
{
    protected $signature = 'godmode:create
        {username : Login name for the operator}
        {--email= : Email for the operator (defaults to <username>@godmode.local)}
        {--force : Create even when a god account already exists}';

    protected $description = 'Create a god mode (super_admin) account with a hidden password prompt.';

    /** Minimum length for a god password (§4.2). Not 8: this is a cross-tenant key. */
    public const MIN_LENGTH = 16;

    /** Passwords a single operator would actually type, in addition to the file list. */
    private const OBVIOUS = [
        'password', 'password123', 'password1234', 'zenmaster12', 'admin', 'admin123',
        'godmode', 'godmode123', 'letmein', 'welcome', 'qwerty', 'changeme',
    ];

    public function handle(): int
    {
        $username = trim((string) $this->argument('username'));
        $email    = strtolower(trim((string) ($this->option('email') ?: $username.'@godmode.local')));

        // Console context: tenant scope is trusted there, but this account has
        // no agency, so an explicit bypass is what makes the intent visible.
        $existing = Tenant::bypass(
            fn () => User::withoutGlobalScopes()
                ->where('username', $username)
                ->orWhere('email', $email)
                ->first(),
            'godmode:create: check for an existing operator'
        );

        if ($existing) {
            $this->error("A user with that username or email already exists (#{$existing->id}, {$existing->user_type}).");

            return self::FAILURE;
        }

        $godExists = User::withoutGlobalScopes()->where('user_type', 'super_admin')->exists();

        if ($godExists && ! $this->option('force')) {
            $this->error('A super_admin account already exists. Re-run with --force to mint another god account on purpose.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (min '.self::MIN_LENGTH.' chars, input hidden)');
        $confirm  = (string) $this->secret('Confirm password');

        if ($password !== $confirm) {
            $this->error('Passwords do not match.');

            return self::FAILURE;
        }

        if ($error = $this->passwordPolicyError($password, $username, $email)) {
            $this->error($error);

            return self::FAILURE;
        }

        // One transaction around ALL THREE writes. The first run against the
        // live DB inserted the user and then died on audit_events.agency_id
        // NOT NULL (migration 2026_11_22_000001 was still pending), leaving a
        // half-created super_admin that also blocked a clean retry. An operator
        // account that exists but was never audited is exactly the row this
        // command must not be able to leave behind: either the user, the
        // activity row and the audit row all land, or none do.
        $user = DB::transaction(function () use ($email, $username, $password) {
            // Plaintext into the model: the `hashed` cast hashes exactly once.
            $user = User::create([
                'agency_id' => null,
                'name'      => 'God Mode Operator',
                'email'     => $email,
                'username'  => $username,
                'password'  => $password,
                'user_type' => 'super_admin',
                'status'    => 'active',
            ]);

            $operator = auth()->user();

            SensitiveActionLogger::log(
                'godmode.account_created',
                subject: $user,
                description: 'God mode account created'.($operator ? ' by '.$operator->email : ' (fresh install)').'.',
                metadata: ['created_by' => $operator?->id, 'username' => $username],
            );

            AuditEvent::withoutGlobalScopes()->create([
                'agency_id'     => null,
                'actor_user_id' => $operator?->id,
                'action'        => AuditEvent::ACTION_CREATED,
                'subject_type'  => User::class,
                'subject_id'    => $user->id,
                'subject_label' => $username,
                'before'        => null,
                'after'         => ['user_type' => 'super_admin', 'status' => 'active', 'password_set' => true],
            ]);

            return $user;
        });

        // Never echo the password back — not even masked.
        $this->info("God mode account created: {$username} <{$email}>");
        $this->info('Sign in at: '.url('/godmode/login'));

        return self::SUCCESS;
    }

    /** Returns an error message, or null when the password is acceptable. */
    private function passwordPolicyError(string $password, string $username, string $email): ?string
    {
        $validator = Validator::make(['password' => $password], [
            'password' => ['required', 'string', 'min:'.self::MIN_LENGTH],
        ]);

        if ($validator->fails()) {
            return 'Password must be at least '.self::MIN_LENGTH.' characters.';
        }

        $lower = Str::lower($password);
        $localPart = Str::lower(Str::before($email, '@'));

        // "Equal to the username" covers the account name alone and with the
        // usual padding people add to reach a length minimum (landlord,
        // landlordlandlord, landlord2026, !landlord).
        if (in_array($lower, self::OBVIOUS, true) || $this->matchesBlocklistedSeed($lower)) {
            return 'That password is a known/common value. Choose another.';
        }

        if ($this->sameAsAccountName($lower, Str::lower($username))
            || $this->sameAsAccountName($lower, $localPart)) {
            return 'That password is the account name with padding. Choose another.';
        }

        if ($this->isCommonPassword($lower)) {
            return 'That password appears on the common-password list. Choose another.';
        }

        return null;
    }

    /**
     * True when the password is the account name, the name repeated, or the
     * name with digits/symbols appended or prepended.
     */
    private function sameAsAccountName(string $password, string $name): bool
    {
        $name = preg_replace('/[^a-z0-9]/', '', $name);

        if ($name === '') {
            return false;
        }

        if ($password === $name) {
            return true;
        }

        // The name repeated to fill the password (landlordlandlord...).
        if (strlen($password) % strlen($name) === 0
            && $password === str_repeat($name, intdiv(strlen($password), strlen($name)))) {
            return true;
        }

        // Name + digits/symbols, or digits/symbols + name (landlord2026).
        return (bool) preg_match('/^'.preg_quote($name, '/').'[0-9!@#$%^&*_.-]*$/', $password)
            || (bool) preg_match('/^[0-9!@#$%^&*_.-]*'.preg_quote($name, '/').'$/', $password);
    }

    /**
     * A blocklisted value with padding still counts: 'zenmaster12zenmaster12'
     * and 'zenmaster12!!!!' are the same compromised secret, not a new one.
     */
    private function matchesBlocklistedSeed(string $lower): bool
    {
        foreach (self::OBVIOUS as $known) {
            if ($this->sameAsAccountName($lower, $known)) {
                return true;
            }
        }

        return false;
    }

    /** Optional 1000-entry list at database/data/common-passwords.txt; skipped if absent. */
    private function isCommonPassword(string $lower): bool
    {
        $path = database_path('data/common-passwords.txt');

        if (! is_readable($path)) {
            return false;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (Str::lower(trim($line)) === $lower) {
                return true;
            }
        }

        return false;
    }
}
