<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    public function index()
    {
        $personas = Persona::all();

        return response()->json([
            'success' => true,
            'message' => 'Lista de personas',
            'data' => $personas
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres_persona' => 'required|string|max:100',
            'apellidos_pat' => 'required|string|max:100',
            'apellidos_mat' => 'required|string|max:100',
            'sexo_persona' => 'required|string|max:100',
            'direccion_persona' => 'required|string|max:100',
            'nacionalidad_persona' => 'required|string|max:100',
            'celular_persona' => 'nullable|string|max:100',
            'fotografia_persona' => 'nullable|string|max:100',
        ]);

        $persona = Persona::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Persona registrada correctamente.',
            'data' => $persona
        ], 201);
    }

    public function show($id)
    {
        $persona = Persona::find($id);
        //$persona = Persona::with(['documento', 'persona_roles.rol'])->find($id);

        if (!$persona) {
            return response()->json([
                'success' => false,
                'message' => 'Persona no encontrada.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle de la persona',
            'data' => $persona
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $persona = Persona::find($id);

        if (!$persona) {
            return response()->json([
                'success' => false,
                'message' => 'Persona no encontrada.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'nombres_persona' => 'required|string|max:100',
            'apellidos_pat' => 'required|string|max:100',
            'apellidos_mat' => 'required|string|max:100',
            'sexo_persona' => 'required|string|max:100',
            'direccion_persona' => 'required|string|max:100',
            'nacionalidad_persona' => 'required|string|max:100',
            'celular_persona' => 'nullable|string|max:100',
            'fotografia_persona' => 'nullable|string|max:100',
        ]);

        $persona->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Persona actualizada correctamente.',
            'data' => $persona
        ], 200);
    }

    public function destroy($id)
    {
        $persona = Persona::find($id);

        if (!$persona) {
            return response()->json([
                'success' => false,
                'message' => 'Persona no encontrada.',
                'data' => null
            ], 404);
        }

        $persona->delete();

        return response()->json([
            'success' => true,
            'message' => 'Persona eliminada correctamente.',
            'data' => null
        ], 200);
    }
}
