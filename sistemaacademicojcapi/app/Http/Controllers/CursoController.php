<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CursoEstudianteGestion;

class CursoController extends Controller
{
    public function index()
    {
        $cursos = Curso::with('nivel_educativo')->get(); // 👈 CAMBIADO

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
            'nivel_educativo_id' => 'required|exists:nivel_educativos,id',
            'aula_curso' => 'required|string|max:50',
            'turno_curso' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $curso = Curso::create($validated);
        $curso->load('nivel_educativo'); // 👈 CAMBIADO

        return response()->json([
            'success' => true,
            'message' => 'Curso creado correctamente.',
            'data' => $curso
        ], 201);
    }

    public function show($id)
    {
        $curso = Curso::with('nivel_educativo')->find($id); // 👈 CAMBIADO

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
            'nivel_educativo_id' => 'required|exists:nivel_educativos,id',
            'aula_curso' => 'required|string|max:50',
            'turno_curso' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $curso->update($validated);
        $curso->load('nivel_educativo'); // 👈 CAMBIADO

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

    public function estudiantesInscritos($idCurso)
    {
        $gestion = \App\Models\Gestion::where('estado_gestion', 'activa')->first();

        if (!$gestion) {
            return response()->json(['success' => false, 'message' => 'No hay gestión activa.'], 404);
        }

        $inscripciones = \App\Models\CursoEstudianteGestion::with('estudiante.persona_rol.persona')
            ->where('cursos_id_curso', $idCurso)
            ->where('gestiones_id_gestion', $gestion->id_gestion)
            ->where('estado', 'inscrito')
            ->get();

        $estudiantes = $inscripciones->map(function ($inscripcion) {
            $persona = $inscripcion->estudiante->persona_rol->persona;
            return [
                'id_estudiante' => $inscripcion->estudiante->id_estudiante,
                'nombre_completo' => "{$persona->apellidos_pat} {$persona->apellidos_mat} {$persona->nombres_persona}",
                'rude' => $inscripcion->estudiante->rude,
            ];
        });

        return response()->json(['success' => true, 'data' => $estudiantes]);
    }

    public function horarioPorCurso($id)
    {
        $gestion = \App\Models\Gestion::where('estado_gestion', 'activa')->first();

        if (!$gestion) {
            return response()->json([
                'success' => false,
                'message' => 'No hay gestión activa.'
            ], 404);
        }

        $horarios = \App\Models\Horario::with('materia')
            ->where('cursos_id_curso', $id)
            ->where('gestiones_id_gestion', $gestion->id_gestion)
            ->orderBy('hora_inicio')
            ->get([
                'id_horario', 'dia', 'hora_inicio', 'hora_fin',
                'materias_id_materia', 'cursos_id_curso', 'gestiones_id_gestion'
            ]);

        $horariosConId = $horarios->map(function ($h) {
            return [
                'id' => $h->id_horario,
                'dia' => $h->dia,
                'hora_inicio' => $h->hora_inicio,
                'hora_fin' => $h->hora_fin,
                'materias_id_materia' => $h->materias_id_materia,
                'cursos_id_curso' => $h->cursos_id_curso,
                'gestiones_id_gestion' => $h->gestiones_id_gestion,
                'materia' => $h->materia // importante mantener esto para que Vue muestre bien
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $horariosConId
        ]);
    }


    public function horarioActual($idCurso)
    {
        $gestion = \App\Models\Gestion::where('estado_gestion', 'activa')->first();

        if (!$gestion) {
            return response()->json(['success' => false, 'message' => 'No hay gestión activa.'], 404);
        }

        $horarios = \App\Models\Horario::with('materia')
            ->where('cursos_id_curso', $idCurso)
            ->where('gestiones_id_gestion', $gestion->id_gestion)
            ->orderBy('hora_inicio')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $horarios,
        ]);
    }

    public function reporteEstudiantesPDF($id)
    {
        $curso = Curso::with('nivel_educativo')->findOrFail($id);
        $estudiantes = CursoEstudianteGestion::with('estudiante.persona_rol.persona')
            ->where('cursos_id_curso', $id)
            ->where('estado', 'inscrito')
            ->get();

        $pdf = Pdf::loadView('reportes.estudiantes_curso', compact('curso', 'estudiantes'));
        return $pdf->download("Reporte_Estudiantes_Curso_{$curso->grado_curso}_{$curso->paralelo_curso}.pdf");
    }

}
