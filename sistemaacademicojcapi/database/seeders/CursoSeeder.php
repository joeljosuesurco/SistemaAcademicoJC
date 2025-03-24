<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;
use Faker\Factory as Faker;

class CursoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $niveles = ['Inicial', 'Primaria', 'Secundaria'];
        $paralelos = ['A', 'B', 'C'];

        for ($i = 0; $i < 6; $i++) {
            Curso::create([
                'grado_curso' => $faker->numberBetween(1, 6) . '°',
                'paralelo_curso' => $faker->randomElement($paralelos),
                'nivel_curso' => $faker->randomElement($niveles),
                'aula_curso' => 'Aula ' . $faker->numberBetween(1, 10),
            ]);
        }
    }
}
