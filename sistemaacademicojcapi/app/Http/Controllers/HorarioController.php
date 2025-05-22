<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function index()
    {
        $horarios = Horario::with(['materia.nivel_educativo', 'curso.nivel_educativo', 'gestion'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de horarios',
            'data' => $horarios
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dia' => 'required|string|max:30',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'materias_id_materia' => 'required|exists:materias,id_materia',
            'cursos_id_curso' => 'required|exists:cursos,id_curso',
            'gestiones_id_gestion' => 'required|exists:gestiones,id_gestion',
        ]);

        $horario = Horario::create($validated);
        $horario->load(['materia.nivel_educativo', 'curso.nivel_educativo', 'gestion']);

        return response()->json([
            'success' => true,
            'message' => 'Horario creado correctamente.',
            'data' => $horario
        ], 201);
    }

    public function show($id)
    {
        $horario = Horario::with(['materia.nivel_educativo', 'curso.nivel_educativo', 'gestion'])->find($id);

        if (!$horario) {
            return response()->json([
                'success' => false,
                'message' => 'Horario no encontrado.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle del horario',
            'data' => $horario
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $horario = Horario::find($id);

        if (!$horario) {
            return response()->json([
                'success' => false,
                'message' => 'Horario no encontrado.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'dia' => 'required|string|max:30',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'materias_id_materia' => 'required|exists:materias,id_materia',
            'cursos_id_curso' => 'required|exists:cursos,id_curso',
            'gestiones_id_gestion' => 'required|exists:gestiones,id_gestion',
        ]);

        $horario->update($validated);
        $horario->load(['materia.nivel_educativo', 'curso.nivel_educativo', 'gestion']);

        return response()->json([
            'success' => true,
            'message' => 'Horario actualizado correctamente.',
            'data' => $horario
        ], 200);
    }

    public function destroy($id)
    {
        $horario = Horario::find($id);

        if (!$horario) {
            return response()->json([
                'success' => false,
                'message' => 'Horario no encontrado.',
                'data' => null
            ], 404);
        }

        $horario->delete();

        return response()->json([
            'success' => true,
            'message' => 'Horario eliminado correctamente.',
            'data' => null
        ], 200);
    }
}
