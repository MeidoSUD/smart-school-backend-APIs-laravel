<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'admin@smartschool.com')->exists()) {
            return;
        }

        User::create([
            'user_type' => 'staff',
            'role' => 'admin',
            'email' => 'admin@smartschool.com',
            'password' => Hash::make('password'),
            'phone_number' => '0555000000',
            'lang_id' => 1,
            'is_active' => 'yes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::create([
            'user_type' => 'student',
            'role' => 'student',
            'email' => 'student@smartschool.com',
            'password' => Hash::make('password'),
            'phone_number' => '0555000001',
            'lang_id' => 1,
            'is_active' => 'yes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::create([
            'user_type' => 'parent',
            'role' => 'parent',
            'email' => 'parent@smartschool.com',
            'password' => Hash::make('password'),
            'phone_number' => '0555000002',
            'lang_id' => 1,
            'is_active' => 'yes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
