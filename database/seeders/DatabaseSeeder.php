<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            SettingSeeder::class,
            UserSeeder::class,
            AcademicSeeder::class,
            StudentSeeder::class,
            ClassSectionSeeder::class,
            FeeSeeder::class,
            PaymentSeeder::class,
            TimetableSeeder::class,
            BookSeeder::class,
            HostelRoomTypeSeeder::class,
            HostelSeeder::class,
            OnlineAdmissionSeeder::class,
            DashboardSupportSeeder::class,
        ]);
    }
}
