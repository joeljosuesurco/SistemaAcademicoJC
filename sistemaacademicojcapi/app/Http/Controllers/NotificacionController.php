<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index()
    {
        $notificaciones = Notificacion::all();

        return response()->json([
            'success' => true,
            'message' => 'Lista de notificaciones',
            'data' => $notificaciones
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo_notificacion' => 'required|string|max:255',
            'mensaje_notificacion' => 'required|string|max:255',
            'fecha_notificacion' => 'required|date',
            'estado_notificacion' => 'required|string|max:50',
            'tipo_notificacion' => 'required|string|max:50',
            'users_id_user' => 'required|exists:users,id_user',
        ]);

        $notificacion = Notificacion::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notificación creada correctamente.',
            'data' => $notificacion
        ], 201);
    }

    public function show($id)
    {
        $notificacion = Notificacion::find($id);

        if (!$notificacion) {
            return response()->json([
                'success' => false,
                'message' => 'Notificación no encontrada.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle de la notificación',
            'data' => $notificacion
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $notificacion = Notificacion::find($id);

        if (!$notificacion) {
            return response()->json([
                'success' => false,
                'message' => 'Notificación no encontrada.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'titulo_notificacion' => 'required|string|max:255',
            'mensaje_notificacion' => 'required|string|max:255',
            'fecha_notificacion' => 'required|date',
            'estado_notificacion' => 'required|string|max:50',
            'tipo_notificacion' => 'required|string|max:50',
            'users_id_user' => 'required|exists:users,id_user',
        ]);

        $notificacion->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notificación actualizada correctamente.',
            'data' => $notificacion
        ], 200);
    }

    public function destroy($id)
    {
        $notificacion = Notificacion::find($id);

        if (!$notificacion) {
            return response()->json([
                'success' => false,
                'message' => 'Notificación no encontrada.',
                'data' => null
            ], 404);
        }

        $notificacion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notificación eliminada correctamente.',
            'data' => null
        ], 200);
    }

    ///cuidaod con esto
    public function visibles()
    {
        $notificaciones = Notificacion::where('estado_notificacion', 'activo')
            ->where('tipo_notificacion', 'publico')
            ->orderBy('fecha_notificacion', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notificaciones
        ]);
    }



}
