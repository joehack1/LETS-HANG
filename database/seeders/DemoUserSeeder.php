<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['email' => 'demo@letshang.test'], [
            'name' => 'Demo User',
            'phone' => '+254700000099',
            'password' => Hash::make('password123'),
            'date_of_birth' => '1995-05-05',
            'country' => 'KE',
            'city' => 'Nairobi',
            'role' => 'user',
            'account_status' => 'active',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'terms_accepted_at' => now(),
        ]);

        User::firstOrCreate(['email' => 'admin@letshang.test'], [
            'name' => 'Admin User',
            'phone' => '+254700000098',
            'password' => Hash::make('password123'),
            'date_of_birth' => '1990-01-01',
            'country' => 'KE',
            'city' => 'Nairobi',
            'role' => 'admin',
            'account_status' => 'active',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'terms_accepted_at' => now(),
        ]);
    }
}
