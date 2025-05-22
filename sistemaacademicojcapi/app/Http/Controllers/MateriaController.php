<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    public function index()
    {
        $materias = Materia::with('nivel_educativo')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de materias',
            'data' => $materias
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'campo_materia' => 'required|string|max:100',
            'area_materia' => 'required|string|max:100',
            'sigla_materia' => 'required|string|max:20',
            'nivel_educativo_id' => 'required|exists:nivel_educativos,id',
        ]);

        $materia = Materia::create($validated);
        $materia->load('nivel_educativo');

        return response()->json([
            'success' => true,
            'message' => 'Materia creada correctamente.',
            'data' => $materia
        ], 201);
    }

    public function show($id)
    {
        $materia = Materia::with('nivel_educativo')->find($id);

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
            'campo_materia' => 'required|string|max:100',
            'area_materia' => 'required|string|max:100',
            'sigla_materia' => 'required|string|max:20',
            'nivel_educativo_id' => 'required|exists:nivel_educativos,id',
        ]);

        $materia->update($validated);
        $materia->load('nivel_educativo');

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
    ////JOEL ESTE ES FILTRADO POR NIVEL ES LO QUE NO DABA
    public function porNivel($nivel_id)
    {
        $materias = Materia::with('nivel_educativo')
            ->where('nivel_educativo_id', $nivel_id)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Materias del nivel educativo',
            'data' => $materias
        ], 200);
    }
}
