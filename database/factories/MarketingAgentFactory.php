<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\MarketingAgency;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarketingAgentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'marketing_agency_id' => MarketingAgency::factory(),
            // Card 6b0363676e4f389b5b3fd002: derive from the parent. The FK is
            // declared first on purpose: expandAttributes() evaluates closures
            // against the definition captured BEFORE the first pass, so a
            // closure above its parent would see the unresolved factory.
            'agency_id' => fn (array $a) => MarketingAgency::find($a['marketing_agency_id'] ?? null)?->agency_id ?? Agency::factory(),
            'name' => $this->faker->name(),
            'contact' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'status' => 'active',
        ];
    }
}
