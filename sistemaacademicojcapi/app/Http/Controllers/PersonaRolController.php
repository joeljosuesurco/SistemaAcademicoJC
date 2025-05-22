<?php

namespace App\Http\Controllers;

use App\Models\PersonaRol;
use Illuminate\Http\Request;

class PersonaRolController extends Controller
{
    public function index()
    {
        $personaRoles = PersonaRol::with(['persona', 'rol', 'user'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de persona-rol',
            'data' => $personaRoles
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'roles_id_rol' => 'required|exists:roles,id_rol',
            'personas_id_persona' => 'required|exists:personas,id_persona',
        ]);

        $personaRol = PersonaRol::create($validated);
        $personaRol->load(['persona', 'rol', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Rol asignado a la persona correctamente.',
            'data' => $personaRol
        ], 201);
    }

    public function show($id)
    {
        $personaRol = PersonaRol::with(['persona', 'rol', 'user'])->find($id);

        if (!$personaRol) {
            return response()->json([
                'success' => false,
                'message' => 'Asignación persona-rol no encontrada.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle de la asignación persona-rol',
            'data' => $personaRol
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $personaRol = PersonaRol::find($id);

        if (!$personaRol) {
            return response()->json([
                'success' => false,
                'message' => 'Asignación persona-rol no encontrada.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'roles_id_rol' => 'required|exists:roles,id_rol',
            'personas_id_persona' => 'required|exists:personas,id_persona',
        ]);

        $personaRol->update($validated);
        $personaRol->load(['persona', 'rol', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Asignación persona-rol actualizada correctamente.',
            'data' => $personaRol
        ], 200);
    }

    public function destroy($id)
    {
        $personaRol = PersonaRol::find($id);

        if (!$personaRol) {
            return response()->json([
                'success' => false,
                'message' => 'Asignación persona-rol no encontrada.',
                'data' => null
            ], 404);
        }

        $personaRol->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asignación persona-rol eliminada correctamente.',
            'data' => null
        ], 200);
    }
}
