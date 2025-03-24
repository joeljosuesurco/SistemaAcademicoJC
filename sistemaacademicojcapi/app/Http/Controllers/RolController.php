<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::all();

        return response()->json([
            'success' => true,
            'message' => 'Lista de roles',
            'data' => $roles
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre',
        ]);

        $rol = Rol::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Rol creado correctamente.',
            'data' => $rol
        ], 201);
    }

    public function show($id)
    {
        $rol = Rol::find($id);

        if (!$rol) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle del rol',
            'data' => $rol
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::find($id);

        if (!$rol) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre,' . $id . ',id_rol',
        ]);

        $rol->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado correctamente.',
            'data' => $rol
        ], 200);
    }

    public function destroy($id)
    {
        $rol = Rol::find($id);

        if (!$rol) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado.',
                'data' => null
            ], 404);
        }

        $rol->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rol eliminado correctamente.',
            'data' => null
        ], 200);
    }
}
