<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'name' => $this->faker->city() . ' Branch',
            'address' => $this->faker->address(),
            'contact' => $this->faker->phoneNumber(),
            'status' => 'active',
        ];
    }
}
