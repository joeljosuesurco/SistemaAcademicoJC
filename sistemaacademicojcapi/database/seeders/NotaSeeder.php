<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nota;
use App\Models\Materia;
use App\Models\Gestion;
use App\Models\CursoEstudianteGestion;
use Faker\Factory as Faker;

class NotaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $gestion = Gestion::first();
        $materias = Materia::with('nivel_educativo')->get();

        $inscripciones = CursoEstudianteGestion::with(['curso.nivel_educativo', 'estudiante'])
            ->where('estado', 'inscrito')
            ->get();

        // Porcentajes para cada dimensión (enteros)
        $porcentajes = [
            'ser_docente'     => 5,
            'saber_docente'   => 45,
            'hacer_docente'   => 40,
            'decidir_docente' => 5,
            'ser_autoeval'    => 5,
            'decidir_autoeval'=> 5,
        ];

        foreach ($inscripciones as $inscripcion) {
            $curso   = $inscripcion->curso;
            $nivelId = $curso->nivel_educativo->id;

            $materiasNivel = $materias->where('nivel_educativo_id', $nivelId)->values();

            foreach ($materiasNivel->random(2) as $materia) {
                // Crear nota con valor provisional (entero)
                $nota = Nota::create([
                    'periodo'                   => 'trimestre',
                    'numero_periodo'            => $faker->numberBetween(1, 3),
                    'nota_final'                => 0,
                    'observacion'               => $faker->randomElement(['', 'Buen rendimiento', 'Debe reforzar']),
                    'estudiantes_id_estudiante' => $inscripcion->estudiantes_id_estudiante,
                    'cursos_id_curso'           => $inscripcion->cursos_id_curso,
                    'materias_id_materia'       => $materia->id_materia,
                    'gestiones_id_gestion'      => $gestion->id_gestion,
                ]);

                $totalValor = 0;

                // Crear dimensiones con porcentaje y valor_obtenido entero
                foreach ($porcentajes as $nombreDimension => $porcentaje) {
                    $valor = $faker->numberBetween(0, 100);
                    $nota->dimensiones()->create([
                        'nombre_dimension' => $nombreDimension,
                        'porcentaje'       => $porcentaje,
                        'valor_obtenido'   => $valor,
                    ]);
                    $totalValor += $valor;
                }

                // Actualizar nota_final como suma (entero)
                $nota->update(['nota_final' => $totalValor]);
            }
        }
    }
}

