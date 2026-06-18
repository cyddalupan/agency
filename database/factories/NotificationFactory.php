<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'user_id'   => User::factory(),
            'type'      => fake()->randomElement(['status_change', 'approval', 'bill_due', 'message']),
            'data'      => ['message' => fake()->sentence()],
            'read_at'   => null,
        ];
    }

    public function unread(): static
    {
        return $this->state(fn(array $attrs) => ['read_at' => null]);
    }

    public function read(): static
    {
        return $this->state(fn(array $attrs) => ['read_at' => now()]);
    }
}
