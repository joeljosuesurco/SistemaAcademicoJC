<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gestion;
use App\Models\CursoEstudianteGestion;
use App\Models\CursoProfesorMateriaGestion;
use App\Models\Nota;
use App\Models\DimensionNota;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class NotaGestion2024Seeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $gestion = Gestion::where('gestion', '2024')->first();

        if (!$gestion) {
            $this->command->warn('❌ La gestión 2024 no existe. Asegúrate de correr primero el seeder Gestion2024Seeder.');
            return;
        }

        $inscripciones = CursoEstudianteGestion::where('gestiones_id_gestion', $gestion->id_gestion)
            ->where('estado', 'inscrito')
            ->get();

        foreach ($inscripciones as $inscripcion) {
            $materiasAsignadas = CursoProfesorMateriaGestion::where([
                ['cursos_id_curso', $inscripcion->cursos_id_curso],
                ['gestiones_id_gestion', $gestion->id_gestion],
            ])->pluck('materias_id_materia');

            foreach ($materiasAsignadas as $materiaId) {
                for ($periodoNum = 1; $periodoNum <= 3; $periodoNum++) {
                    $notaFinal = $faker->numberBetween(25, 100); // valores variados

                    $nota = Nota::create([
                        'estudiantes_id_estudiante' => $inscripcion->estudiantes_id_estudiante,
                        'materias_id_materia'       => $materiaId,
                        'cursos_id_curso'           => $inscripcion->cursos_id_curso,
                        'gestiones_id_gestion'      => $gestion->id_gestion,
                        'periodo'                   => "Trimestre $periodoNum",
                        'numero_periodo'            => $periodoNum,
                        'nota_final'                => $notaFinal,
                        'observacion'               => $notaFinal >= 51 ? 'Aprobado' : 'Reprobado',
                    ]);

                    // Añadir dimensiones si deseas que sumen al total
                    $dimensiones = [
                        'ser_docente'      => 5,
                        'saber_docente'    => 45,
                        'hacer_docente'    => 40,
                        'decidir_docente'  => 5,
                        'ser_autoeval'     => 2,
                        'decidir_autoeval' => 3,
                    ];

                    foreach ($dimensiones as $nombre => $porcentaje) {
                        $valor = $faker->numberBetween(0, 100);
                        DimensionNota::create([
                            'notas_id_nota'    => $nota->id_nota,
                            'nombre_dimension' => $nombre,
                            'porcentaje'       => $porcentaje,
                            'valor_obtenido'   => $valor,
                        ]);
                    }
                }
            }
        }

        $this->command->info('✅ Notas con dimensiones para gestión 2024 generadas correctamente.');
    }
}
