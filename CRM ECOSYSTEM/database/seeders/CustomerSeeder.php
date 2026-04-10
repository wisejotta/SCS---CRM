<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        User::factory(150)->create()->each(function($user) {
            Customer::factory()->create(['user_id' => $user->id]);
        });

        foreach(Customer::get() as $customer) {
            Lead::factory()->create([
                'agent_id' => 1,
                'customer_id' => $customer->id,
            ]);
        }
    }
}
