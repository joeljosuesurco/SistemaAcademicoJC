<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActividadSistema;
use Illuminate\Support\Facades\Auth;

class ActividadSistemaController extends Controller
{
    /**
     * Listar logs del sistema con filtros opcionales.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ActividadSistema::class); // Solo admins

        $query = ActividadSistema::query()->latest();

        // Cargar relación con usuario → persona_rol → rol
        $query->with(['usuario.persona_rol.rol']);

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('modulo')) {
            $query->where('modulo', 'LIKE', "%{$request->modulo}%");
        }

        if ($request->filled('accion')) {
            $query->where('accion', 'LIKE', "%{$request->accion}%");
        }

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(30),
        ]);
    }

    /**
     * Registrar una actividad manualmente.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        ActividadSistema::create([
            'usuario_id' => $user->id_user,
            'accion' => $request->accion,
            'modulo' => $request->modulo,
            'descripcion' => $request->descripcion,
            'ip' => $request->ip() ?? $request->server('REMOTE_ADDR'),
            'navegador' => $request->userAgent(),
        ]);

        return response()->json(['success' => true]);
    }
}
