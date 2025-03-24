<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CursoProfesorMateriaGestion;
use App\Models\Profesor;
use App\Models\Materia;
use App\Models\Curso;
use App\Models\Gestion;

class CursoProfesorMateriaGestionSeeder extends Seeder
{
    public function run(): void
    {
        $profesores = Profesor::all();
        $materias = Materia::all();
        $cursos = Curso::all();
        $gestion = Gestion::first();

        foreach ($materias as $materia) {
            CursoProfesorMateriaGestion::create([
                'cursos_id_curso' => $cursos->random()->id_curso,
                'profesores_id_profesor' => $profesores->random()->id_profesor,
                'materias_id_materia' => $materia->id_materia,
                'gestiones_id_gestion' => $gestion->id_gestion,
            ]);
        }
    }
}
