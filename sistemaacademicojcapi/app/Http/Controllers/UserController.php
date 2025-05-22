<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('persona_rol.persona', 'persona_rol.rol')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de usuarios',
            'data' => $users
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            //'name_user' => 'required|string|max:30',
            //'name_user' => 'required|string|max:30|unique:users,name_user',
            'username' => 'required|string|max:30|unique:users,name_user,' . $id . ',id_user',
            'password' => 'required|string|min:6',
            'state_user' => 'required|string|max:255',
            'persona_rol_id_persona_rol' => 'required|exists:persona_rol,id_persona_rol',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado correctamente.',
            'data' => $user
        ], 201);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle del usuario',
            'data' => $user
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            //'name_user' => 'required|string|max:30',
            'name_user' => 'required|string|max:30|unique:users,name_user,' . $id . ',id_user',
            'password' => 'nullable|string|min:6',
            'state_user' => 'required|string|max:255',
            'persona_rol_id_persona_rol' => 'required|exists:persona_rol,id_persona_rol',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente.',
            'data' => $user
        ], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
                'data' => null
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado correctamente.',
            'data' => null
        ], 200);
    }
    //// JOEL PARAR PERFUIL ES ESTO NO OLVIDAR
    public function perfil()
    {
        $user = auth('api')->user();

        $user->load('persona_rol.persona', 'persona_rol.rol');

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function updatePerfil(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name_user' => 'required|string|max:30|unique:users,name_user,' . $user->id_user . ',id_user',
            'password' => 'nullable|string|min:6',
        ]);

        $user->name_user = $validated['name_user'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente.',
            'data' => $user
        ]);
    }

}
