<?php

namespace App\Http\Controllers;

use App\Models\Padre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PadreInfoController extends Controller
{
    public function index(): JsonResponse
    {
        $padres = Padre::with([
            'persona_rol.persona.documento',
            'estudiantes.estudiante.persona_rol.persona',
            'estudiantes.estudiante.cursos' => function ($query) {
                $query->where('estado', 'inscrito')
                      ->with(['curso.nivel_educativo', 'gestion']);
            }
        ])->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de padres con detalles',
            'data' => $padres
        ]);
    }

    public function show($id): JsonResponse
    {
        $padre = Padre::with([
            'persona_rol.persona.documento',
            'estudiantes.estudiante.persona_rol.persona',
            'estudiantes.estudiante.cursos' => function ($query) {
                $query->where('estado', 'inscrito')
                      ->with(['curso.nivel_educativo', 'gestion']);
            }
        ])->find($id);

        if (!$padre) {
            return response()->json([
                'success' => false,
                'message' => 'Padre no encontrado.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle del padre',
            'data' => $padre
        ]);
    }
}
