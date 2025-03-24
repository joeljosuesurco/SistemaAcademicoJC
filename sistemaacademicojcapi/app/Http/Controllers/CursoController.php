<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index()
    {
        $cursos = Curso::all();

        return response()->json([
            'success' => true,
            'message' => 'Lista de cursos',
            'data' => $cursos
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grado_curso' => 'required|string|max:50',
            'paralelo_curso' => 'required|string|max:10',
            'nivel_curso' => 'required|string|max:50',
            'aula_curso' => 'required|string|max:50',
        ]);

        $curso = Curso::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Curso creado correctamente.',
            'data' => $curso
        ], 201);
    }

    public function show($id)
    {
        $curso = Curso::find($id);

        if (!$curso) {
            return response()->json([
                'success' => false,
                'message' => 'Curso no encontrado.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle del curso',
            'data' => $curso
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $curso = Curso::find($id);

        if (!$curso) {
            return response()->json([
                'success' => false,
                'message' => 'Curso no encontrado.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'grado_curso' => 'required|string|max:50',
            'paralelo_curso' => 'required|string|max:10',
            'nivel_curso' => 'required|string|max:50',
            'aula_curso' => 'required|string|max:50',
        ]);

        $curso->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Curso actualizado correctamente.',
            'data' => $curso
        ], 200);
    }

    public function destroy($id)
    {
        $curso = Curso::find($id);

        if (!$curso) {
            return response()->json([
                'success' => false,
                'message' => 'Curso no encontrado.',
                'data' => null
            ], 404);
        }

        $curso->delete();

        return response()->json([
            'success' => true,
            'message' => 'Curso eliminado correctamente.',
            'data' => null
        ], 200);
    }
}
