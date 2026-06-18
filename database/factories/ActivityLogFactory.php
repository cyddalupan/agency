<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'agency_id'    => Agency::factory(),
            'user_id'      => User::factory(),
            'subject_type' => User::class,
            'subject_id'   => User::factory(),
            'action'       => fake()->randomElement(['created', 'updated', 'activated', 'suspended', 'deactivated', 'login']),
            'description'  => fake()->sentence(),
            'metadata'     => json_encode(['ip' => fake()->ipv4()]),
        ];
    }
}
