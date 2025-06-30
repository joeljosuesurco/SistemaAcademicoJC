<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Gestion;

class AdministrarGestionController extends Controller
{
    public function cambiarGestion(Request $request)
    {
        $request->validate([
            'nueva_gestion' => 'required|integer|digits:4|min:2000',
            'password' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Contraseña incorrecta.'], 403);
        }

        // Cerrar la gestión activa actual (si existe)
        Gestion::where('estado_gestion', 'activa')->update([
            'estado_gestion' => 'inactiva'
        ]);

        // Crear una nueva gestión como activa
        $nueva = new Gestion();
        $nueva->nombre_gestion = 'Gestión ' . $request->nueva_gestion;
        $nueva->gestion = $request->nueva_gestion;
        $nueva->inicio_gestion = $request->inicio_gestion ?? $request->nueva_gestion . '-02-01';
        $nueva->fin_gestion = $request->fin_gestion ?? $request->nueva_gestion . '-11-30';
        $nueva->estado_gestion = 'activa';
        $nueva->save();

        return response()->json([
            'success' => true,
            'gestion' => $nueva,
            'message' => 'Nueva gestión activada correctamente.'
        ], 201);
    }
}
