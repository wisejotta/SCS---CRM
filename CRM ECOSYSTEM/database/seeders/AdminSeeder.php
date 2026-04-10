<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Agent;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'role' => UserRole::ADMIN->value,
        ])->each(function($admin) {
            Agent::factory()->create([
                'user_id' => $admin->id,
            ]);
        });
    }
}
