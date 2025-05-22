<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seguimiento;
use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\Curso;
use App\Models\Gestion;
use Faker\Factory as Faker;

class SeguimientoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $estudiantes = Estudiante::all();
        $materias = Materia::all();
        $cursos = Curso::all();
        $gestion = Gestion::first();

        foreach ($estudiantes as $estudiante) {
            Seguimiento::create([
                'fecha_reg_seg' => $faker->date(),
                'asistencia' => 'Sí',
                'participacion' => 'Alta',
                'disciplina' => 'Buena',
                'puntualidad' => 'Excelente',
                'respeto' => 'Sí',
                'tolerancia' => 'Sí',
                'estado_animo' => $faker->randomElement(['Contento', 'Estresado', 'Motivado']),
                'observaciones_seguimiento' => $faker->sentence(4),
                'estudiantes_id_estudiante' => $estudiante->id_estudiante,
                'cursos_id_curso' => $cursos->random()->id_curso,
                'materias_id_materia' => $materias->random()->id_materia,
                'gestiones_id_gestion' => $gestion->id_gestion,
            ]);
        }
    }
}
