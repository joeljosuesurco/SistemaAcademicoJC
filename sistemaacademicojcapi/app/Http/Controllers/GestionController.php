<?php

namespace App\Http\Controllers;

use App\Models\Gestion;
use Illuminate\Http\Request;

class GestionController extends Controller
{
    public function index()
    {
        $gestiones = Gestion::all();

        return response()->json([
            'success' => true,
            'message' => 'Lista de gestiones',
            'data' => $gestiones
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_gestion' => 'required|string|max:50',
            'gestion' => 'required|string|max:50',
            'inicio_gestion' => 'required|date',
            'fin_gestion' => 'required|date|after_or_equal:inicio_gestion',
            'estado_gestion' => 'required|string|max:50',
        ]);

        $gestion = Gestion::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gestión creada correctamente.',
            'data' => $gestion
        ], 201);
    }

    public function show($id)
    {
        $gestion = Gestion::find($id);

        if (!$gestion) {
            return response()->json([
                'success' => false,
                'message' => 'Gestión no encontrada.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle de la gestión',
            'data' => $gestion
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $gestion = Gestion::find($id);

        if (!$gestion) {
            return response()->json([
                'success' => false,
                'message' => 'Gestión no encontrada.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'nombre_gestion' => 'required|string|max:50',
            'gestion' => 'required|string|max:50',
            'inicio_gestion' => 'required|date',
            'fin_gestion' => 'required|date|after_or_equal:inicio_gestion',
            'estado_gestion' => 'required|string|max:50',
        ]);

        $gestion->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gestión actualizada correctamente.',
            'data' => $gestion
        ], 200);
    }

    public function destroy($id)
    {
        $gestion = Gestion::find($id);

        if (!$gestion) {
            return response()->json([
                'success' => false,
                'message' => 'Gestión no encontrada.',
                'data' => null
            ], 404);
        }

        $gestion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gestión eliminada correctamente.',
            'data' => null
        ], 200);
    }
}
