<?php

namespace App\Http\Controllers;

use App\Models\{Padre, Estudiante, PadreEstudiante, ActividadSistema};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Validator, Auth};

class AsignarHijosPadreController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'padres_id_padre' => 'required|exists:padres,id_padre',
            'estudiantes_id_estudiante' => 'required|exists:estudiantes,id_estudiante',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $existe = PadreEstudiante::where('padres_id_padre', $request->padres_id_padre)
            ->where('estudiantes_id_estudiante', $request->estudiantes_id_estudiante)
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Este estudiante ya está asignado a este padre.',
            ], 409);
        }

        $asignacion = PadreEstudiante::create([
            'padres_id_padre' => $request->padres_id_padre,
            'estudiantes_id_estudiante' => $request->estudiantes_id_estudiante,
        ]);

        // Log
        ActividadSistema::create([
            'usuario_id' => Auth::id(),
            'accion' => 'asignar',
            'modulo' => 'asignar-hijos',
            'descripcion' => "Asignó estudiante ID {$request->estudiantes_id_estudiante} al padre ID {$request->padres_id_padre}",
            'ip' => $request->ip(),
            'navegador' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estudiante asignado correctamente al padre.',
            'data' => $asignacion,
        ], 201);
    }

    public function destroy($padreId, $estudianteId)
    {
        $asignacion = PadreEstudiante::where('padres_id_padre', $padreId)
            ->where('estudiantes_id_estudiante', $estudianteId)
            ->first();

        if (!$asignacion) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la relación especificada.',
            ], 404);
        }

        $asignacion->delete();

        // Log
        ActividadSistema::create([
            'usuario_id' => Auth::id(),
            'accion' => 'eliminar',
            'modulo' => 'asignar-hijos',
            'descripcion' => "Eliminó relación padre ID {$padreId} - estudiante ID {$estudianteId}",
            'ip' => request()->ip(),
            'navegador' => request()->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Relación eliminada correctamente.',
        ], 200);
    }

    public function hijosDisponibles($padreId)
    {
        $padre = Padre::find($padreId);

        if (!$padre) {
            return response()->json([
                'success' => false,
                'message' => 'Padre no encontrado.',
            ], 404);
        }

        $asignados = PadreEstudiante::where('padres_id_padre', $padreId)
            ->pluck('estudiantes_id_estudiante');

        $estudiantes = Estudiante::with('persona_rol.persona')
            ->whereNotIn('id_estudiante', $asignados)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $estudiantes,
        ]);
    }

    public function hijosAsignados($padreId)
    {
        $asignaciones = PadreEstudiante::where('padres_id_padre', $padreId)->get();

        $estudiantes = [];

        foreach ($asignaciones as $rel) {
            $est = Estudiante::with('persona_rol.persona')
                ->where('id_estudiante', $rel->estudiantes_id_estudiante)
                ->first();

            if ($est) {
                $estudiantes[] = $est;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $estudiantes,
        ]);
    }
}
