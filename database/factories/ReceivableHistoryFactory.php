<?php

namespace Database\Factories;

use App\Models\Receivable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceivableHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'receivable_id' => Receivable::factory(),
            'agency_id'     => null,
            'user_id'       => User::factory(),
            'from_status'   => 'pending',
            'to_status'     => 'received',
            'note'          => fake()->optional()->sentence(),
        ];
    }
}
