<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CursoEstudianteGestion;

class CambioCursoController extends Controller
{
    public function cambiar(Request $request)
    {
        $request->validate([
            'estudiantes_id_estudiante' => 'required|exists:estudiantes,id_estudiante',
            'gestiones_id_gestion' => 'required|exists:gestiones,id_gestion',
            'nuevo_curso_id' => 'required|exists:cursos,id_curso',
        ]);

        $estudianteId = $request->estudiantes_id_estudiante;
        $gestionId = $request->gestiones_id_gestion;
        $nuevoCursoId = $request->nuevo_curso_id;

        // Buscar inscripción actual activa
        $inscripcionActual = CursoEstudianteGestion::where([
            ['estudiantes_id_estudiante', $estudianteId],
            ['gestiones_id_gestion', $gestionId],
            ['estado', 'inscrito']
        ])->first();

        if (!$inscripcionActual) {
            return response()->json([
                'success' => false,
                'message' => 'El estudiante no tiene una inscripción activa en esta gestión.'
            ], 404);
        }

        // Marcar inscripción anterior como no inscrito
        $inscripcionActual->update(['estado' => 'no_inscrito']);

        // Registrar nueva inscripción
        $nuevaInscripcion = CursoEstudianteGestion::create([
            'estudiantes_id_estudiante' => $estudianteId,
            'cursos_id_curso' => $nuevoCursoId,
            'gestiones_id_gestion' => $gestionId,
            'estado' => 'inscrito'
        ]);

        $nuevaInscripcion->load(['curso.nivel_educativo']);

        return response()->json([
            'success' => true,
            'message' => 'Cambio de curso realizado correctamente.',
            'data' => $nuevaInscripcion
        ], 201);
    }

    public function revertir(Request $request)
    {
        $request->validate([
            'estudiantes_id_estudiante' => 'required|exists:estudiantes,id_estudiante',
            'gestiones_id_gestion' => 'required|exists:gestiones,id_gestion',
        ]);

        $estudianteId = $request->estudiantes_id_estudiante;
        $gestionId = $request->gestiones_id_gestion;

        // Buscar inscripción actual (estado = inscrito)
        $inscripcionActual = CursoEstudianteGestion::where([
            ['estudiantes_id_estudiante', $estudianteId],
            ['gestiones_id_gestion', $gestionId],
            ['estado', 'inscrito']
        ])->first();

        // Buscar inscripción anterior (estado = no_inscrito)
        $inscripcionAnterior = CursoEstudianteGestion::where([
            ['estudiantes_id_estudiante', $estudianteId],
            ['gestiones_id_gestion', $gestionId],
            ['estado', 'no_inscrito']
        ])->latest('id')->first(); // más reciente

        if (!$inscripcionActual || !$inscripcionAnterior) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede revertir el cambio de curso. Verifique si existe una inscripción anterior.'
            ], 404);
        }

        // Revertir los estados
        $inscripcionActual->update(['estado' => 'no_inscrito']);
        $inscripcionAnterior->update(['estado' => 'inscrito']);

        $inscripcionAnterior->load(['curso.nivel_educativo']);

        return response()->json([
            'success' => true,
            'message' => 'Cambio de curso revertido correctamente.',
            'data' => [
                'curso_anterior_id' => $inscripcionAnterior->cursos_id_curso,
                'curso_actual_inactivado' => $inscripcionActual->cursos_id_curso,
                'curso_anterior' => $inscripcionAnterior
            ]
        ]);
    }
}
