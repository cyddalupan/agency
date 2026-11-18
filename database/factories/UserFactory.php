<?php

namespace Database\Factories;

use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'name'      => fake()->name(),
            'email'     => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'  => static::$password ??= Hash::make('password'),
            'user_type' => 'staff',
            'status'    => 'active',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn(array $attrs) => [
            'agency_id' => null,
            'user_type' => 'super_admin',
        ]);
    }

    public function asType(string $type): static
    {
        return $this->state(fn(array $attrs) => ['user_type' => $type]);
    }

    /**
     * A PRIVILEGED "sees everything" account (client 2026-09-28: MAE, EVELYN,
     * ANGEL).
     *
     * These bypass the own-records-only scoping and keep the gated folders /
     * Statistics report / full Reports tab / Agent Deduction create.
     *
     * User::isPrivileged() matches the user's NAME (or the local part of the
     * email) against config('mjolnir.privileged_accounts'), whose default list
     * is MAE,EVELYN,ANGEL. A factory user gets a fake name, so it can never be
     * privileged however its user_type is set — dozens of tests asked for "an
     * admin" and got 403 because admin alone does not open those screens.
     *
     * This state deliberately NAMES the user rather than overriding the config:
     * it exercises the real production path (the shipped name list) instead of
     * handing the test a privilege the running application could not grant.
     * Set user_type yourself — this composes with asType().
     */
    public function privileged(): static
    {
        return $this->privilegedAs('MAE');
    }

    /** A privileged account under another name from the shipped list (EVELYN). */
    public function privilegedAs(string $name): static
    {
        $name = strtoupper(trim($name));

        return $this->state(fn(array $attrs) => [
            'name'  => $name,
            // isPrivileged() matches the NAME, so the email only has to be a
            // valid, unique address — it must not collide when a test creates
            // two privileged users, and it must not accidentally match the
            // list through its local part.
            'email' => fake()->unique()->safeEmail(),
        ]);
    }
}
