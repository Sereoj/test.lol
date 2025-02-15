<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesTableSeeder::class,
            AppsTableSeeder::class,
            CategoriesTableSeeder::class,
            AchievementsTableSeeder::class,
            StatusesTableSeeder::class,
            LevelsTableSeeder::class,
            TasksTableSeeder::class,
            EmploymentStatusesTableSeeder::class,
            SpecializationsTableSeeder::class,
            LocationsTableSeeder::class,
            UserSettingsTableSeeder::class,
            SourcesTableSeeder::class,
            SkillsTableSeeder::class,
            BadgesTableSeeder::class,
            FeeSeeder::class,
            UsersTableSeeder::class,
            PassportClientsSeeder::class,
        ]);
    }
}
