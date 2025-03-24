<?php

namespace App\Http\Controllers;

use App\Models\Seguimiento;
use Illuminate\Http\Request;

class SeguimientoController extends Controller
{
    public function index()
    {
        $seguimientos = Seguimiento::all();

        return response()->json([
            'success' => true,
            'message' => 'Lista de seguimientos',
            'data' => $seguimientos
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_reg_seg' => 'required|date',
            'asistencia' => 'required|string|max:50',
            'participacion' => 'required|string|max:50',
            'disciplina' => 'required|string|max:50',
            'puntualidad' => 'required|string|max:50',
            'respeto' => 'required|string|max:50',
            'tolerancia' => 'required|string|max:50',
            'estado_animo' => 'required|string|max:50',
            'observaciones_seguimiento' => 'required|string|max:255',
            'estudiantes_id_estudiante' => 'required|exists:estudiantes,id_estudiante',
            'cursos_id_curso' => 'required|exists:cursos,id_curso',
            'materias_id_materia' => 'required|exists:materias,id_materia',
            'gestiones_id_gestion' => 'required|exists:gestiones,id_gestion',
        ]);

        $seguimiento = Seguimiento::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Seguimiento registrado correctamente.',
            'data' => $seguimiento
        ], 201);
    }

    public function show($id)
    {
        $seguimiento = Seguimiento::find($id);

        if (!$seguimiento) {
            return response()->json([
                'success' => false,
                'message' => 'Seguimiento no encontrado.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle del seguimiento',
            'data' => $seguimiento
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $seguimiento = Seguimiento::find($id);

        if (!$seguimiento) {
            return response()->json([
                'success' => false,
                'message' => 'Seguimiento no encontrado.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'fecha_reg_seg' => 'required|date',
            'asistencia' => 'required|string|max:50',
            'participacion' => 'required|string|max:50',
            'disciplina' => 'required|string|max:50',
            'puntualidad' => 'required|string|max:50',
            'respeto' => 'required|string|max:50',
            'tolerancia' => 'required|string|max:50',
            'estado_animo' => 'required|string|max:50',
            'observaciones_seguimiento' => 'required|string|max:255',
            'estudiantes_id_estudiante' => 'required|exists:estudiantes,id_estudiante',
            'cursos_id_curso' => 'required|exists:cursos,id_curso',
            'materias_id_materia' => 'required|exists:materias,id_materia',
            'gestiones_id_gestion' => 'required|exists:gestiones,id_gestion',
        ]);

        $seguimiento->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Seguimiento actualizado correctamente.',
            'data' => $seguimiento
        ], 200);
    }

    public function destroy($id)
    {
        $seguimiento = Seguimiento::find($id);

        if (!$seguimiento) {
            return response()->json([
                'success' => false,
                'message' => 'Seguimiento no encontrado.',
                'data' => null
            ], 404);
        }

        $seguimiento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Seguimiento eliminado correctamente.',
            'data' => null
        ], 200);
    }
}
