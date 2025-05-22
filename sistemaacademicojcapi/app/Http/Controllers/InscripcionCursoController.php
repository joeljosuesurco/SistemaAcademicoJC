<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CursoEstudianteGestion;

class InscripcionCursoController extends Controller
{
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'estudiantes_id_estudiante' => 'required|exists:estudiantes,id_estudiante',
            'cursos_id_curso' => 'required|exists:cursos,id_curso',
            'gestiones_id_gestion' => 'required|exists:gestiones,id_gestion',
        ]);

        // Verificar si el estudiante ya está inscrito en ese curso y gestión
        $existe = CursoEstudianteGestion::where([
            ['estudiantes_id_estudiante', $request->estudiantes_id_estudiante],
            ['cursos_id_curso', $request->cursos_id_curso],
            ['gestiones_id_gestion', $request->gestiones_id_gestion],
        ])->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'El estudiante ya está inscrito en ese curso y gestión.'
            ], 409); // 409 Conflict
        }

        // Registrar la inscripción con estado 'inscrito'
        $inscripcion = CursoEstudianteGestion::create([
            'estudiantes_id_estudiante' => $request->estudiantes_id_estudiante,
            'cursos_id_curso' => $request->cursos_id_curso,
            'gestiones_id_gestion' => $request->gestiones_id_gestion,
            'estado' => 'inscrito', // ✅ Estado obligatorio
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estudiante inscrito en curso y gestión correctamente.',
            'data' => $inscripcion
        ], 201);
    }
}
