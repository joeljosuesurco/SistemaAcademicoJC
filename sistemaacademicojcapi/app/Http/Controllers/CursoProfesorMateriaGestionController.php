<?php

namespace App\Http\Controllers;

use App\Models\CursoProfesorMateriaGestion;
use Illuminate\Http\Request;

class CursoProfesorMateriaGestionController extends Controller
{
    public function index()
    {
        $asignaciones = CursoProfesorMateriaGestion::with([
            'curso.nivel_educativo',
            'profesor.persona_rol.persona',
            'materia.nivel_educativo',
            'gestion'
        ])->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de asignaciones curso-profesor-materia',
            'data' => $asignaciones
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cursos_id_curso' => 'required|exists:cursos,id_curso',
            'profesores_id_profesor' => 'required|exists:profesores,id_profesor',
            'materias_id_materia' => 'required|exists:materias,id_materia',
            'gestiones_id_gestion' => 'required|exists:gestiones,id_gestion',
        ]);

        $asignacion = CursoProfesorMateriaGestion::create($validated);
        $asignacion->load([
            'curso.nivel_educativo',
            'profesor.persona_rol.persona',
            'materia.nivel_educativo',
            'gestion'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Asignación registrada correctamente.',
            'data' => $asignacion
        ], 201);
    }

    public function show($id)
    {
        $asignacion = CursoProfesorMateriaGestion::with([
            'curso.nivel_educativo',
            'profesor.persona_rol.persona',
            'materia.nivel_educativo',
            'gestion'
        ])->find($id);

        if (!$asignacion) {
            return response()->json([
                'success' => false,
                'message' => 'Asignación no encontrada.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle de la asignación',
            'data' => $asignacion
        ]);
    }

    public function update(Request $request, $id)
    {
        $asignacion = CursoProfesorMateriaGestion::find($id);

        if (!$asignacion) {
            return response()->json([
                'success' => false,
                'message' => 'Asignación no encontrada.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'cursos_id_curso' => 'required|exists:cursos,id_curso',
            'profesores_id_profesor' => 'required|exists:profesores,id_profesor',
            'materias_id_materia' => 'required|exists:materias,id_materia',
            'gestiones_id_gestion' => 'required|exists:gestiones,id_gestion',
        ]);

        $asignacion->update($validated);
        $asignacion->load([
            'curso.nivel_educativo',
            'profesor.persona_rol.persona',
            'materia.nivel_educativo',
            'gestion'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Asignación actualizada correctamente.',
            'data' => $asignacion
        ]);
    }

    public function destroy($id)
    {
        $asignacion = CursoProfesorMateriaGestion::find($id);

        if (!$asignacion) {
            return response()->json([
                'success' => false,
                'message' => 'Asignación no encontrada.',
                'data' => null
            ], 404);
        }

        $asignacion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asignación eliminada correctamente.',
            'data' => null
        ]);
    }
}
