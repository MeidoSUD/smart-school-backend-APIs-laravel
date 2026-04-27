<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    // public function run(): void
    // {
    //     $roles = [
    //         ['name' => 'Admin', 'slug' => 'admin', 'is_active' => 0, 'is_system' => 1, 'is_superadmin' => 0],
    //         ['name' => 'Teacher', 'slug' => 'teacher', 'is_active' => 0, 'is_system' => 1, 'is_superadmin' => 0],
    //         ['name' => 'Accountant', 'slug' => 'accountant', 'is_active' => 0, 'is_system' => 1, 'is_superadmin' => 0],
    //         ['name' => 'Librarian', 'slug' => 'librarian', 'is_active' => 0, 'is_system' => 1, 'is_superadmin' => 0],
    //         ['name' => 'Receptionist', 'slug' => 'receptionist', 'is_active' => 0, 'is_system' => 1, 'is_superadmin' => 0],
    //         ['name' => 'Super Admin', 'slug' => 'super_admin', 'is_active' => 0, 'is_system' => 1, 'is_superadmin' => 1],
    //     ];

    //     foreach ($roles as $role) {
    //         DB::table('roles')->updateOrInsert(
    //             ['slug' => $role['slug']],
    //             $role
    //         );
    //     }
    // }
}