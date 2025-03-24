<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    public function index()
    {
        $materias = Materia::all();

        return response()->json([
            'success' => true,
            'message' => 'Lista de materias',
            'data' => $materias
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_materia' => 'required|string|max:100',
            'nombre_materia' => 'required|string|max:100',
            'sigla_materia' => 'required|string|max:20',
            'estado_materia' => 'required|string|max:50',
        ]);

        $materia = Materia::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Materia creada correctamente.',
            'data' => $materia
        ], 201);
    }

    public function show($id)
    {
        $materia = Materia::find($id);

        if (!$materia) {
            return response()->json([
                'success' => false,
                'message' => 'Materia no encontrada.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle de la materia',
            'data' => $materia
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $materia = Materia::find($id);

        if (!$materia) {
            return response()->json([
                'success' => false,
                'message' => 'Materia no encontrada.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'area_materia' => 'required|string|max:100',
            'nombre_materia' => 'required|string|max:100',
            'sigla_materia' => 'required|string|max:20',
            'estado_materia' => 'required|string|max:50',
        ]);

        $materia->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Materia actualizada correctamente.',
            'data' => $materia
        ], 200);
    }

    public function destroy($id)
    {
        $materia = Materia::find($id);

        if (!$materia) {
            return response()->json([
                'success' => false,
                'message' => 'Materia no encontrada.',
                'data' => null
            ], 404);
        }

        $materia->delete();

        return response()->json([
            'success' => true,
            'message' => 'Materia eliminada correctamente.',
            'data' => null
        ], 200);
    }
}
