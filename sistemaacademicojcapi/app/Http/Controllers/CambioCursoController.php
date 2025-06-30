<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CursoEstudianteGestion;
use App\Models\ActividadSistema;
use Illuminate\Support\Facades\Auth;

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

        $inscripcionActual->update(['estado' => 'no_inscrito']);

        $nuevaInscripcion = CursoEstudianteGestion::create([
            'estudiantes_id_estudiante' => $estudianteId,
            'cursos_id_curso' => $nuevoCursoId,
            'gestiones_id_gestion' => $gestionId,
            'estado' => 'inscrito'
        ]);

        $nuevaInscripcion->load(['curso.nivel_educativo']);

        // 🔒 Registrar actividad
        $user = Auth::user();
        ActividadSistema::create([
            'usuario_id' => $user->id_user,
            'accion' => 'cambiar_curso',
            'modulo' => 'curso_estudiante',
            'descripcion' => "Cambio de curso para estudiante ID $estudianteId en gestión $gestionId al curso $nuevoCursoId",
            'ip' => $request->ip() ?? $request->server('REMOTE_ADDR'),
            'navegador' => $request->userAgent(),
        ]);

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

        $inscripcionActual = CursoEstudianteGestion::where([
            ['estudiantes_id_estudiante', $estudianteId],
            ['gestiones_id_gestion', $gestionId],
            ['estado', 'inscrito']
        ])->first();

        $inscripcionAnterior = CursoEstudianteGestion::where([
            ['estudiantes_id_estudiante', $estudianteId],
            ['gestiones_id_gestion', $gestionId],
            ['estado', 'no_inscrito']
        ])->latest('id')->first();

        if (!$inscripcionActual || !$inscripcionAnterior) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede revertir el cambio de curso. Verifique si existe una inscripción anterior.'
            ], 404);
        }

        $inscripcionActual->update(['estado' => 'no_inscrito']);
        $inscripcionAnterior->update(['estado' => 'inscrito']);

        $inscripcionAnterior->load(['curso.nivel_educativo']);

        // 🔒 Registrar actividad
        $user = Auth::user();
        ActividadSistema::create([
            'usuario_id' => $user->id_user,
            'accion' => 'revertir_curso',
            'modulo' => 'curso_estudiante',
            'descripcion' => "Reversión de curso para estudiante ID $estudianteId en gestión $gestionId. Curso reactivado: {$inscripcionAnterior->cursos_id_curso}",
            'ip' => $request->ip() ?? $request->server('REMOTE_ADDR'),
            'navegador' => $request->userAgent(),
        ]);

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
