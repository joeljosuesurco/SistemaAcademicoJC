<?php

namespace App\Http\Controllers;

use App\Models\Padre;
use Illuminate\Http\Request;

class PadreController extends Controller
{
    public function index()
    {
        $padres = Padre::with('persona_rol.persona')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de padres',
            'data' => $padres
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'estado_padre' => 'required|string|max:50',
            'profesion_padre' => 'nullable|string|max:50',
            'persona_rol_id_persona_rol' => 'required|exists:persona_rol,id_persona_rol',
        ]);

        $padre = Padre::create($validated);
        $padre->load('persona_rol.persona');

        return response()->json([
            'success' => true,
            'message' => 'Padre registrado correctamente.',
            'data' => $padre
        ], 201);
    }

    public function show($id)
    {
        $padre = Padre::with('persona_rol.persona')->find($id);

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
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $padre = Padre::find($id);

        if (!$padre) {
            return response()->json([
                'success' => false,
                'message' => 'Padre no encontrado.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'estado_padre' => 'required|string|max:50',
            'profesion_padre' => 'nullable|string|max:50',
            'persona_rol_id_persona_rol' => 'required|exists:persona_rol,id_persona_rol',
        ]);

        $padre->update($validated);
        $padre->load('persona_rol.persona');

        return response()->json([
            'success' => true,
            'message' => 'Padre actualizado correctamente.',
            'data' => $padre
        ], 200);
    }

    public function destroy($id)
    {
        $padre = Padre::find($id);

        if (!$padre) {
            return response()->json([
                'success' => false,
                'message' => 'Padre no encontrado.',
                'data' => null
            ], 404);
        }

        $padre->delete();

        return response()->json([
            'success' => true,
            'message' => 'Padre eliminado correctamente.',
            'data' => null
        ], 200);
    }
}
