<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use Illuminate\Http\Request;

class ListarNotasController extends Controller
{
    public function index(Request $request)
    {
        $query = Nota::with([
            'dimensiones',
            'estudiante.persona_rol.persona',
            'curso.nivel_educativo',
            'materia.nivel_educativo',
            'gestion',
        ]);

        // Filtros opcionales
        if ($request->filled('estudiante_id')) {
            $query->where('estudiantes_id_estudiante', $request->estudiante_id);
        }
        if ($request->filled('curso_id')) {
            $query->where('cursos_id_curso', $request->curso_id);
        }
        if ($request->filled('materia_id')) {
            $query->where('materias_id_materia', $request->materia_id);
        }
        if ($request->filled('gestion_id')) {
            $query->where('gestiones_id_gestion', $request->gestion_id);
        }
        if ($request->filled('numero_periodo')) {
            $query->where('numero_periodo', $request->numero_periodo);
        }

        $notas = $query->get();

        $resultado = $notas->map(function($nota) {
            $dims = $nota->dimensiones->keyBy('nombre_dimension');

            // Raw values como enteros
            $raw = [
                'ser_docente'     => (int) round(optional($dims->get('ser_docente'))->valor_obtenido ?? 0),
                'saber_docente'   => (int) round(optional($dims->get('saber_docente'))->valor_obtenido ?? 0),
                'hacer_docente'   => (int) round(optional($dims->get('hacer_docente'))->valor_obtenido ?? 0),
                'decidir_docente' => (int) round(optional($dims->get('decidir_docente'))->valor_obtenido ?? 0),
                'ser_autoeval'    => (int) round(optional($dims->get('ser_autoeval'))->valor_obtenido ?? 0),
                'decidir_autoeval'=> (int) round(optional($dims->get('decidir_autoeval'))->valor_obtenido ?? 0),
            ];

            // Pesos porcentuales convertidos a puntos enteros
            $pesos_pct = [
                'ser_docente'     => (int) round($raw['ser_docente']     * 5  / 100),
                'saber_docente'   => (int) round($raw['saber_docente']   * 45 / 100),
                'hacer_docente'   => (int) round($raw['hacer_docente']   * 40 / 100),
                'decidir_docente' => (int) round($raw['decidir_docente'] * 5  / 100),
                'ser_autoeval'    => (int) round($raw['ser_autoeval']    * 5  / 100),
                'decidir_autoeval'=> (int) round($raw['decidir_autoeval']* 5  / 100),
            ];

            // Cálculo de nota final capeado a 100
            $nota_final_calc = min(array_sum($pesos_pct), 100);

            return [
                'id'             => $nota->id_nota,
                'periodo'        => $nota->periodo,
                'numero_periodo' => $nota->numero_periodo,
                'nota_final'     => $nota_final_calc,

                'estudiante' => [
                    'id'              => $nota->estudiante->id_estudiante,
                    'nombre_completo' => trim(
                        "{$nota->estudiante->persona_rol->persona->apellidos_pat} " .
                        "{$nota->estudiante->persona_rol->persona->apellidos_mat} " .
                        "{$nota->estudiante->persona_rol->persona->nombres_persona}"
                    ),
                ],

                'curso' => [
                    'grado'    => $nota->curso->grado_curso,
                    'paralelo' => $nota->curso->paralelo_curso,
                    'nivel'    => $nota->curso->nivel_educativo->codigo,
                    'nivelId'  => $nota->curso->nivel_educativo->id,
                ],

                'materia' => [
                    'id'    => $nota->materia->id_materia    ?? null,
                    'area'  => $nota->materia->area_materia  ?? null,
                    'sigla' => $nota->materia->sigla_materia ?? null,
                ],

                'valores_raw' => $raw,
                'pesos_pct'   => $pesos_pct,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $resultado,
        ], 200);
    }

    public function notasPorEstudiante($id, $gestion, $periodo)
    {
        $notas = Nota::with([
            'materia',
            'curso.nivel_educativo',
            'dimensiones'
        ])
        ->where('estudiantes_id_estudiante', $id)
        ->where('gestiones_id_gestion', $gestion)
        ->where('periodo', $periodo)
        ->get();

        $resultado = $notas->map(function($nota) {
            $dims = $nota->dimensiones->keyBy('nombre_dimension');

            $raw = [
                'ser_docente'     => (int) round(optional($dims->get('ser_docente'))->valor_obtenido ?? 0),
                'saber_docente'   => (int) round(optional($dims->get('saber_docente'))->valor_obtenido ?? 0),
                'hacer_docente'   => (int) round(optional($dims->get('hacer_docente'))->valor_obtenido ?? 0),
                'decidir_docente' => (int) round(optional($dims->get('decidir_docente'))->valor_obtenido ?? 0),
                'ser_autoeval'    => (int) round(optional($dims->get('ser_autoeval'))->valor_obtenido ?? 0),
                'decidir_autoeval'=> (int) round(optional($dims->get('decidir_autoeval'))->valor_obtenido ?? 0),
            ];

            $pesos_pct = [
                'ser_docente'     => (int) round($raw['ser_docente']     * 5  / 100),
                'saber_docente'   => (int) round($raw['saber_docente']   * 45 / 100),
                'hacer_docente'   => (int) round($raw['hacer_docente']   * 40 / 100),
                'decidir_docente' => (int) round($raw['decidir_docente'] * 5  / 100),
                'ser_autoeval'    => (int) round($raw['ser_autoeval']    * 5  / 100),
                'decidir_autoeval'=> (int) round($raw['decidir_autoeval']* 5  / 100),
            ];

            $nota_final_calc = min(array_sum($pesos_pct), 100);

            return [
                'periodo'        => $nota->periodo,
                'numero_periodo' => $nota->numero_periodo,
                'nota_final'     => $nota_final_calc,

                'curso' => [
                    'grado'    => $nota->curso->grado_curso,
                    'paralelo' => $nota->curso->paralelo_curso,
                    'nivel'    => $nota->curso->nivel_educativo->codigo,
                    'nivelId'  => $nota->curso->nivel_educativo->id,
                ],

                'materia' => [
                    'id'    => $nota->materia->id_materia    ?? null,
                    'area'  => $nota->materia->area_materia  ?? null,
                    'sigla' => $nota->materia->sigla_materia ?? null,
                ],

                'valores_raw' => $raw,
                'pesos_pct'   => $pesos_pct,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $resultado
        ], 200);
    }
}
