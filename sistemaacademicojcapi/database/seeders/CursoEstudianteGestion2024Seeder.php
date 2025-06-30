<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CursoEstudianteGestion;
use App\Models\Gestion;
use Illuminate\Support\Facades\DB;

class CursoEstudianteGestion2024Seeder extends Seeder
{
    public function run(): void
    {
        $gestion2025 = Gestion::where('gestion', '2025')->first();
        $gestion2024 = Gestion::where('gestion', '2024')->first();

        if (!$gestion2025 || !$gestion2024) {
            $this->command->warn('❌ No se encontraron ambas gestiones (2024 y 2025).');
            return;
        }

        $inscripciones2025 = CursoEstudianteGestion::where('gestiones_id_gestion', $gestion2025->id_gestion)
            ->where('estado', 'inscrito')
            ->get();

        $contador = 0;

        foreach ($inscripciones2025 as $inscripcion) {
            // Verificamos que no exista ya la inscripción en 2024
            $yaExiste = CursoEstudianteGestion::where([
                ['cursos_id_curso', $inscripcion->cursos_id_curso],
                ['gestiones_id_gestion', $gestion2024->id_gestion],
                ['estudiantes_id_estudiante', $inscripcion->estudiantes_id_estudiante],
            ])->exists();

            if (!$yaExiste) {
                CursoEstudianteGestion::create([
                    'cursos_id_curso' => $inscripcion->cursos_id_curso,
                    'gestiones_id_gestion' => $gestion2024->id_gestion,
                    'estudiantes_id_estudiante' => $inscripcion->estudiantes_id_estudiante,
                    'estado' => 'inscrito'
                ]);
                $contador++;
            }
        }

        $this->command->info("✅ Se copiaron $contador inscripciones de 2025 a 2024.");
    }
}
