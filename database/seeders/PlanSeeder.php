<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::firstOrCreate(['slug' => 'free'], [
            'name' => 'Free',
            'description' => 'Basic friendship and activity features.',
            'price' => 0,
            'currency' => 'KES',
            'interval' => 'monthly',
            'features' => ['Basic discovery', 'Create activities', 'Join communities'],
            'is_active' => true,
        ]);

        Plan::firstOrCreate(['slug' => 'plus'], [
            'name' => 'Let\'s Hang Plus',
            'description' => 'Premium discovery and organizer tools.',
            'price' => 500,
            'currency' => 'KES',
            'interval' => 'monthly',
            'features' => ['Advanced filters', 'Profile boost', 'Premium communities', 'Organizer analytics'],
            'is_active' => true,
        ]);
    }
}
