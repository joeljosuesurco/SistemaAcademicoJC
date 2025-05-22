<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use Illuminate\Http\Request;

class ProfesorController extends Controller
{
    public function index()
    {
        $profesores = Profesor::all();

        return response()->json([
            'success' => true,
            'message' => 'Lista de profesores',
            'data' => $profesores
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'especialidad_profesor' => 'required|string|max:50',
            'estado_profesor' => 'required|string|max:50',
            'titulo_provision_nacional' => 'nullable|string|max:100',
            'rda' => 'nullable|string|max:50',
            'cas' => 'nullable|string|max:50',
            'persona_rol_id_persona_rol' => 'required|exists:persona_rol,id_persona_rol',
        ]);

        $profesor = Profesor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profesor creado correctamente.',
            'data' => $profesor
        ], 201);
    }


    public function show($id)
    {
        $profesor = Profesor::with([
            'curso_profesor_materia_gestion.materia.nivel_educativo',
            'curso_profesor_materia_gestion.curso.nivel_educativo',
            'curso_profesor_materia_gestion.gestion',
        ])->find($id);

        if (!$profesor) {
            return response()->json([
                'success' => false,
                'message' => 'Profesor no encontrado.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle del profesor con asignaciones',
            'data' => $profesor
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $profesor = Profesor::find($id);

        if (!$profesor) {
            return response()->json([
                'success' => false,
                'message' => 'Profesor no encontrado.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'especialidad_profesor' => 'required|string|max:50',
            'estado_profesor' => 'required|string|max:50',
            'titulo_provision_nacional' => 'nullable|string|max:100',
            'rda' => 'nullable|string|max:50',
            'cas' => 'nullable|string|max:50',
            'persona_rol_id_persona_rol' => 'required|exists:persona_rol,id_persona_rol',
        ]);

        $profesor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profesor actualizado correctamente.',
            'data' => $profesor
        ], 200);
    }


    public function destroy($id)
    {
        $profesor = Profesor::find($id);

        if (!$profesor) {
            return response()->json([
                'success' => false,
                'message' => 'Profesor no encontrado.',
                'data' => null
            ], 404);
        }

        $profesor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profesor eliminado correctamente.',
            'data' => null
        ], 200);
    }
}
