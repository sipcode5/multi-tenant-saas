<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'           => 'Starter',
                'slug'           => 'starter',
                'stripe_price_id' => 'price_mock_starter',
                'price_monthly'  => 900,
                'price_yearly'   => 8640,
                'features'       => ['Up to 5 members', '5 GB storage', 'Email support'],
                'is_popular'     => false,
            ],
            [
                'name'           => 'Pro',
                'slug'           => 'pro',
                'stripe_price_id' => 'price_mock_pro',
                'price_monthly'  => 2900,
                'price_yearly'   => 27840,
                'features'       => ['Up to 25 members', '50 GB storage', 'Priority support', 'Advanced analytics'],
                'is_popular'     => true,
            ],
            [
                'name'           => 'Enterprise',
                'slug'           => 'enterprise',
                'stripe_price_id' => 'price_mock_enterprise',
                'price_monthly'  => 9900,
                'price_yearly'   => 95040,
                'features'       => ['Unlimited members', '500 GB storage', 'Dedicated support', 'SLA', 'SSO'],
                'is_popular'     => false,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
