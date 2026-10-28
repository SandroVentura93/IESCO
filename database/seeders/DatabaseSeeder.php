<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // Create default test user (idempotent)
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'Usuario123']
        );

        // Create admin/docente/alumno users (idempotent)
        $this->call(\Database\Seeders\CreateAuthUsersSeeder::class);
        // seed sample courses
        $this->call(\Database\Seeders\CourseSeeder::class);
    }
}
