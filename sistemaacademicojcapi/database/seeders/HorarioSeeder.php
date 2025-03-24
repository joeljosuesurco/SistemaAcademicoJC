<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\Curso;
use App\Models\Gestion;
use Faker\Factory as Faker;

class HorarioSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $materias = Materia::all();
        $cursos = Curso::all();
        $gestion = Gestion::first();

        for ($i = 0; $i < 10; $i++) {
            Horario::create([
                'dia' => $faker->randomElement($dias),
                'hora_inicio' => $faker->time('H:i:s'),
                'hora_fin' => $faker->time('H:i:s'),
                'materias_id_materia' => $materias->random()->id_materia,
                'cursos_id_curso' => $cursos->random()->id_curso,
                'gestiones_id_gestion' => $gestion->id_gestion,
            ]);
        }
    }
}
