<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nota;
use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\Gestion;
use Faker\Factory as Faker;

class NotaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $estudiantes = Estudiante::all();
        $cursos = Curso::all();
        $materias = Materia::all();
        $gestion = Gestion::first();

        foreach ($estudiantes as $estudiante) {
            foreach ($materias->random(2) as $materia) {
                Nota::create([
                    'bimestre' => $faker->randomElement(['1er', '2do', '3er', '4to']),
                    'nota' => $faker->numberBetween(30, 100),
                    'promedio_anual' => null,
                    'observacion_nota' => $faker->randomElement(['', 'Excelente', 'Debe mejorar']),
                    'estudiantes_id_estudiante' => $estudiante->id_estudiante,
                    'cursos_id_curso' => $cursos->random()->id_curso,
                    'materias_id_materia' => $materia->id_materia,
                    'gestiones_id_gestion' => $gestion->id_gestion,
                ]);
            }
        }
    }
}
