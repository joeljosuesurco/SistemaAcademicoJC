<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use Illuminate\Http\Request;

class NotaController extends Controller
{
    public function index()
    {
        $notas = Nota::with([
            'estudiante' => function ($q) {
                $q->with(['persona_rol' => function ($q2) {
                    $q2->with('persona');
                }]);
            },
            'curso.nivel_educativo',
            'materia.nivel_educativo',
            'gestion',
            'dimensiones',
        ])->get();

        foreach ($notas as $nota) {
            $nota->estudiante?->persona_rol?->load('persona'); // 👈 este es el único cambio
        }

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
        $nota->load([
            'estudiante.persona_rol.persona',
            'curso.nivel_educativo',
            'materia.nivel_educativo',
            'gestion'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nota registrada correctamente.',
            'data' => $nota
        ], 201);
    }

    public function show($id)
    {
        $nota = Nota::with([
            'estudiante.persona_rol.persona',
            'curso.nivel_educativo',
            'materia.nivel_educativo',
            'gestion'
        ])->find($id);

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
        $nota->load([
            'estudiante.persona_rol.persona',
            'curso.nivel_educativo',
            'materia.nivel_educativo',
            'gestion'
        ]);

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

