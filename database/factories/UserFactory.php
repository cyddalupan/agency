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
}
