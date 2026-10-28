<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CreateAuthUsersSeeder extends Seeder
{
    public function run()
    {
        $password = 'Usuario123';

        // Admin
        User::updateOrCreate(
            ['email' => 'admin@local.test'],
            ['name' => 'Administrador', 'password' => $password, 'role' => 'admin']
        );

        // Docente
        User::updateOrCreate(
            ['email' => 'docente@local.test'],
            ['name' => 'Docente', 'password' => $password, 'role' => 'docente']
        );

        // Alumno
        User::updateOrCreate(
            ['email' => 'alumno@local.test'],
            ['name' => 'Alumno', 'password' => $password, 'role' => 'alumno']
        );
    }
}
