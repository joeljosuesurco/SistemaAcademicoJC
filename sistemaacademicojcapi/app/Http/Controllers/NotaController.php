<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use Illuminate\Http\Request;

class NotaController extends Controller
{
    public function index()
    {
        $notas = Nota::all();

        return response()->json([
            'success' => true,
            'message' => 'Lista de notas',
            'data' => $notas
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bimestre' => 'required|string|max:30',
            'nota' => 'required|integer|min:0|max:100',
            'promedio_anual' => 'nullable|integer|min:0|max:100',
            'observacion_nota' => 'required|string|max:100',
            'estudiantes_id_estudiante' => 'required|exists:estudiantes,id_estudiante',
            'cursos_id_curso' => 'required|exists:cursos,id_curso',
            'materias_id_materia' => 'required|exists:materias,id_materia',
            'gestiones_id_gestion' => 'required|exists:gestiones,id_gestion',
        ]);

        $nota = Nota::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Nota registrada correctamente.',
            'data' => $nota
        ], 201);
    }

    public function show($id)
    {
        $nota = Nota::find($id);

        if (!$nota) {
            return response()->json([
                'success' => false,
                'message' => 'Nota no encontrada.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle de la nota',
            'data' => $nota
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $nota = Nota::find($id);

        if (!$nota) {
            return response()->json([
                'success' => false,
                'message' => 'Nota no encontrada.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'bimestre' => 'required|string|max:30',
            'nota' => 'required|integer|min:0|max:100',
            'promedio_anual' => 'nullable|integer|min:0|max:100',
            'observacion_nota' => 'required|string|max:100',
            'estudiantes_id_estudiante' => 'required|exists:estudiantes,id_estudiante',
            'cursos_id_curso' => 'required|exists:cursos,id_curso',
            'materias_id_materia' => 'required|exists:materias,id_materia',
            'gestiones_id_gestion' => 'required|exists:gestiones,id_gestion',
        ]);

        $nota->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Nota actualizada correctamente.',
            'data' => $nota
        ], 200);
    }

    public function destroy($id)
    {
        $nota = Nota::find($id);

        if (!$nota) {
            return response()->json([
                'success' => false,
                'message' => 'Nota no encontrada.',
                'data' => null
            ], 404);
        }

        $nota->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nota eliminada correctamente.',
            'data' => null
        ], 200);
    }
}
