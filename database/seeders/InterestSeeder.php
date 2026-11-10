<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InterestSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            'Coffee', 'Football', 'Basketball', 'Hiking', 'Board Games',
            'Movies', 'Gaming', 'Photography', 'Networking', 'Study Groups',
            'Restaurants', 'Music', 'Beach', 'Fitness', 'Creative Workshops',
            'Travel', 'Cooking', 'Reading', 'Tech', 'Art',
        ];

        foreach ($interests as $name) {
            Interest::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'category' => 'general']
            );
        }
    }
}
