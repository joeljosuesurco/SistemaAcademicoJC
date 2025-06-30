<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActividadSistema;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name_user' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $user = Auth::guard('api')->user();

        // Registrar log de inicio de sesión exitoso
        ActividadSistema::create([
            'usuario_id' => $user->id_user,
            'accion' => 'login',
            'modulo' => 'auth',
            'descripcion' => 'Inicio de sesión exitoso',
            'ip' => $request->ip(),
            'navegador' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    public function me()
    {
        $user = Auth::guard('api')->user()->load('persona_rol.rol', 'persona_rol.persona');

        return response()->json([
            'success' => true,
            'message' => 'Usuario autenticado',
            'data' => $user
        ]);
    }
}
