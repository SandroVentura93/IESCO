<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $courseNames = [
            'Matemática',
            'Comunicación',
            'Ciencia y Tecnología',
        ];

        foreach ($courseNames as $name) {
            Course::updateOrCreate(['name' => $name], ['name' => $name]);
        }
    }
}
