<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Visa;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    public function definition()
    {
        return [
            'visa_id' => Visa::inRandomOrder()->first()->id,
            'status' => LeadStatus::from(rand(0, 1) ? 2 : 5)
        ];
    }
}
