<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            AdminAccessSeeder::class,
            SettingSeeder::class,
            FreePlanSeeder::class,
            PlanSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
