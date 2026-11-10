<?php

namespace Database\Seeders;

use App\Models\ActivityCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ActivityCategorySeeder extends Seeder
{
    public function run(): void
    {
        $cats = ['Coffee', 'Sports', 'Hiking', 'Board Games', 'Movies', 'Gaming', 'Photography', 'Networking', 'Study', 'Dining', 'Music', 'Fitness', 'Workshops', 'Beach', 'Travel'];

        foreach ($cats as $name) {
            ActivityCategory::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
