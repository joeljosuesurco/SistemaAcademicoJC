<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use Illuminate\Http\JsonResponse;

class ProfesorInfoController extends Controller
{
    public function index(): JsonResponse
    {
        // Solo profesores activos
        $profesores = Profesor::activos()
            ->with('persona_rol.persona.documento')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de profesores activos',
            'data' => $profesores
        ]);
    }

    public function show($id): JsonResponse
    {
        $profesor = Profesor::with([
            'persona_rol.persona.documento',
            'curso_profesor_materia_gestion.materia.nivel_educativo',
            'curso_profesor_materia_gestion.curso.nivel_educativo',
            'curso_profesor_materia_gestion.gestion',
        ])->find($id);

        if (!$profesor) {
            return response()->json([
                'success' => false,
                'message' => 'Profesor no encontrado.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle del profesor con asignaciones',
            'data' => $profesor
        ]);
    }

}
