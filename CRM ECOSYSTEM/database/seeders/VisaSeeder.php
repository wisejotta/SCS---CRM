<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Visa;

class VisaSeeder extends Seeder
{
    public function run()
    {
        $visas = [
            ['Student Visa', 1700 ],
            ['Express Entry', 2200 ],
            ['Working Holiday', 1000 ],
            ['Tourism Visa', 500 ],
            ['B.O.B Preparation', 1500 ],
            ['IELTS Online', 650 ],
            ['Job Prep', 700 ],
        ];

        foreach($visas as $visa) {
            $visa = Visa::create([
                'name' => $visa[0],
                'price' => $visa[1],
            ]);

            $stripe = new \Stripe\StripeClient(config('stripe.secret'));
            $res = $stripe->products->create(['name' => $visa->name]);
            $visa->update(['external_id' => $res->id]);
        }
    }
}
