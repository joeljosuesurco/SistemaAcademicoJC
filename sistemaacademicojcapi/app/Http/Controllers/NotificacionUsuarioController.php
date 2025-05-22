<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\User;
use App\Models\NotificacionUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class NotificacionUsuarioController extends Controller
{
    // Listar TODAS las notificaciones asignadas al usuario autenticado
    public function index()
    {
        $user = Auth::user();

        $notificaciones = NotificacionUsuario::with('notificacion')
            ->where('users_id_user', $user->id_user)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Notificaciones asignadas al usuario',
            'data' => $notificaciones,
        ]);
    }

    // Listar SOLO las notificaciones no leídas para el usuario dado
    public function notificacionesNoLeidas($userId)
    {
        $noLeidas = NotificacionUsuario::with('notificacion')
            ->where('users_id_user', $userId)
            ->where('leido', false)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Notificaciones no leídas',
            'data' => $noLeidas,
        ]);
    }

    // Asignar una notificación a uno o varios usuarios
    public function store(Request $request)
    {
        $request->validate([
            'notificaciones_id_notificacion' => 'required|exists:notificaciones,id_notificacion',
            'usuarios' => 'required|array',
            'usuarios.*' => 'exists:users,id_user',
        ]);

        $notificacionId = $request->notificaciones_id_notificacion;
        $usuarios = $request->usuarios;

        $creadas = [];

        foreach ($usuarios as $userId) {
            $asignacion = NotificacionUsuario::firstOrCreate([
                'notificaciones_id_notificacion' => $notificacionId,
                'users_id_user' => $userId,
            ], [
                'leido' => false,
            ]);

            $creadas[] = $asignacion;
        }

        return response()->json([
            'success' => true,
            'message' => 'Notificación asignada correctamente',
            'data' => $creadas,
        ]);
    }

    // Marcar una notificación como leída por el usuario autenticado
    public function marcarLeido($id)
    {
        $user = Auth::user();
        Log::info("📥 Marcar como leído: notificación {$id} para usuario {$user->id_user}");

        $notificacionUsuario = NotificacionUsuario::where('notificaciones_id_notificacion', $id)
            ->where('users_id_user', $user->id_user)
            ->first();

        if (!$notificacionUsuario) {
            Log::warning("❌ No se encontró asignación para notificación {$id} y user {$user->id_user}");
            return response()->json([
                'success' => false,
                'message' => 'Notificación no asignada a este usuario.',
            ], 404);
        }

        $notificacionUsuario->leido = true;
        $notificacionUsuario->fecha_lectura = now();
        $notificacionUsuario->save();

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída.',
            'data' => $notificacionUsuario,
        ]);
    }
///////ojito
    public function ultimos($userId)
    {
        $notificaciones = NotificacionUsuario::with('notificacion')
            ->where('users_id_user', $userId)
            ->orderByDesc('id')
            ->take(3)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Últimos comunicados asignados al usuario',
            'data' => $notificaciones,
        ]);
    }
////////OJITO CON ESTO IFGUAL
    public function usuariosPorGrupo($grupo)
    {
        switch ($grupo) {
            case 'estudiantes':
                $usuarios = User::whereHas('persona_rol.rol', fn($q) => $q->where('nombre', 'Estudiante'))->get();
                break;
            case 'profesores':
                $usuarios = User::whereHas('persona_rol.rol', fn($q) => $q->where('nombre', 'Profesor'))->get();
                break;
            case 'todos':
            default:
                $usuarios = User::all();
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuarios encontrados',
            'data' => $usuarios,
        ]);
    }

}
