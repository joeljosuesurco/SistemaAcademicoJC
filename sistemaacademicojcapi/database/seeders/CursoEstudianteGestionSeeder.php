<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CursoEstudianteGestion;
use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Gestion;

class CursoEstudianteGestionSeeder extends Seeder
{
    public function run(): void
    {
        $estudiantes = Estudiante::all();
        $cursos = Curso::all();
        $gestion = Gestion::first();

        foreach ($estudiantes as $estudiante) {
            // Verificamos si ya está inscrito en esa gestión
            $yaInscrito = CursoEstudianteGestion::where('gestiones_id_gestion', $gestion->id_gestion)
                ->where('estudiantes_id_estudiante', $estudiante->id_estudiante)
                ->exists();

            if (!$yaInscrito) {
                CursoEstudianteGestion::create([
                    'cursos_id_curso' => $cursos->random()->id_curso,
                    'gestiones_id_gestion' => $gestion->id_gestion,
                    'estudiantes_id_estudiante' => $estudiante->id_estudiante,
                    'estado' => 'inscrito'
                ]);
            }
        }
    }
}

