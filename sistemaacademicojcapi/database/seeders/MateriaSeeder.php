<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Materia;
use Faker\Factory as Faker;

class MateriaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $materias = ['Matemáticas', 'Lenguaje', 'Historia', 'Ciencias', 'Educación Física'];

        foreach ($materias as $materia) {
            Materia::create([
                'area_materia' => $faker->randomElement(['Ciencias Exactas', 'Lenguas', 'Humanidades']),
                'nombre_materia' => $materia,
                'sigla_materia' => strtoupper(substr($materia, 0, 3)),
                'estado_materia' => 'activa',
            ]);
        }
    }
}
