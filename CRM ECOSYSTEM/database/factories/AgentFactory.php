<?php

namespace Database\Factories;

use App\Enums\AgentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentFactory extends Factory
{
    public function definition()
    {
        return [
            'type' => AgentType::FILE_OPENING,
        ];
    }
}
