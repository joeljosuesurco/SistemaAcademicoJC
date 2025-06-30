<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gestion;
use App\Models\CursoProfesorMateriaGestion;

class CursoProfesorMateriaGestion2024Seeder extends Seeder
{
    public function run(): void
    {
        $gestion2025 = Gestion::where('gestion', '2025')->first();
        $gestion2024 = Gestion::where('gestion', '2024')->first();

        if (!$gestion2025 || !$gestion2024) {
            $this->command->warn('❌ No se encontraron ambas gestiones (2025 y 2024).');
            return;
        }

        $asignaciones = CursoProfesorMateriaGestion::where('gestiones_id_gestion', $gestion2025->id_gestion)->get();

        $contador = 0;

        foreach ($asignaciones as $asignacion) {
            // Verificar si ya existe esa asignación en 2024
            $yaExiste = CursoProfesorMateriaGestion::where([
                ['cursos_id_curso', $asignacion->cursos_id_curso],
                ['profesores_id_profesor', $asignacion->profesores_id_profesor],
                ['materias_id_materia', $asignacion->materias_id_materia],
                ['gestiones_id_gestion', $gestion2024->id_gestion],
            ])->exists();

            if (!$yaExiste) {
                CursoProfesorMateriaGestion::create([
                    'cursos_id_curso' => $asignacion->cursos_id_curso,
                    'profesores_id_profesor' => $asignacion->profesores_id_profesor,
                    'materias_id_materia' => $asignacion->materias_id_materia,
                    'gestiones_id_gestion' => $gestion2024->id_gestion,
                ]);
                $contador++;
            }
        }

        $this->command->info("✅ Se clonaron $contador asignaciones a la gestión 2024.");
    }
}
