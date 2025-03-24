<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;

class EstudianteController extends Controller
{
    public function index()
    {
        $estudiantes = Estudiante::all();

        return response()->json([
            'success' => true,
            'message' => 'Lista de estudiantes',
            'data' => $estudiantes
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'estado_estudiante' => 'required|string|max:50',
            'obsev_estudiante' => 'nullable|string',
            'persona_rol_id_persona_rol' => 'required|exists:persona_rol,id_persona_rol',
        ]);

        $estudiante = Estudiante::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Estudiante creado correctamente.',
            'data' => $estudiante
        ], 201);
    }

    public function show($id)
    {
        $estudiante = Estudiante::find($id);

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle del estudiante',
            'data' => $estudiante
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $estudiante = Estudiante::find($id);

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'estado_estudiante' => 'required|string|max:50',
            'obsev_estudiante' => 'nullable|string',
            'persona_rol_id_persona_rol' => 'required|exists:persona_rol,id_persona_rol',
        ]);

        $estudiante->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Estudiante actualizado correctamente.',
            'data' => $estudiante
        ], 200);
    }

    public function destroy($id)
    {
        $estudiante = Estudiante::find($id);

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado.',
                'data' => null
            ], 404);
        }

        $estudiante->delete();

        return response()->json([
            'success' => true,
            'message' => 'Estudiante eliminado correctamente.',
            'data' => null
        ], 200);
    }
}
