<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;

class EstudianteController extends Controller
{
    public function index()
    {
        $estudiantes = Estudiante::with([
            'persona_rol.persona',
            'cursos.curso.nivel_educativo',
            'cursos.gestion'
        ])->get();

        $estudiantes = $estudiantes->map(function ($est) {
            $inscripcionActiva = $est->cursos->firstWhere('estado', 'inscrito');
            $curso = $inscripcionActiva?->curso;
            $gestion = $inscripcionActiva?->gestion;

            $est->nivel_educativo_id = $curso?->nivel_educativo_id;
            $est->curso_id = $curso?->id_curso;
            $est->gestion_id = $gestion?->id_gestion;

            // Agrega aquí:
            $est->grado_curso = $curso?->grado_curso;
            $est->paralelo_curso = $curso?->paralelo_curso;
            $est->curso_nombre = $curso ? $curso->grado_curso . ' ' . $curso->paralelo_curso : null;
            $est->nivel_nombre = $curso?->nivel_educativo?->nombre;

            unset($est->cursos);
            return $est;
        });


        return response()->json([
            'success' => true,
            'message' => 'Lista de estudiantes',
            'data' => $estudiantes
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            //'estado_estudiante' => 'required|string|max:50',
            'obsev_estudiante' => 'nullable|string',
            'persona_rol_id_persona_rol' => 'required|exists:persona_rol,id_persona_rol',
            'libreta_anterior' => 'nullable|string|max:50',
            'rude' => 'required|string|max:50|unique:estudiantes,rude',
        ]);

        $estudiante = Estudiante::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Estudiante creado correctamente.',
            'data' => $estudiante
        ], 201);
    }


    public function show($id)
    {
        $estudiante = Estudiante::with([
            'persona_rol.persona.documento',
            'cursos.curso.nivel_educativo',
            'cursos.gestion',
            'padres.padre.persona_rol.persona'
        ])->find($id);

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle del estudiante',
            'data' => $estudiante
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $estudiante = Estudiante::find($id);

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            //'estado_estudiante' => 'required|string|max:50',
            'obsev_estudiante' => 'nullable|string',
            'persona_rol_id_persona_rol' => 'required|exists:persona_rol,id_persona_rol',
            'libreta_anterior' => 'nullable|string|max:50',
            'rude' => 'required|string|max:50|unique:estudiantes,rude,' . $id . ',id_estudiante',
        ]);

        $estudiante->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Estudiante actualizado correctamente.',
            'data' => $estudiante
        ], 200);
    }


    public function destroy($id)
    {
        $estudiante = Estudiante::find($id);

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado.',
                'data' => null
            ], 404);
        }

        $estudiante->delete();

        return response()->json([
            'success' => true,
            'message' => 'Estudiante eliminado correctamente.',
            'data' => null
        ], 200);
    }
}
