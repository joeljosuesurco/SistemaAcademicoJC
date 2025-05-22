<?php

namespace App\Http\Controllers;

use App\Models\Seguimiento;
use Illuminate\Http\Request;
use App\Models\Estudiante;
use App\Models\CursoProfesorMateriaGestion;
use App\Models\Materia;

class SeguimientoController extends Controller
{
    public function index()
    {
        $seguimientos = Seguimiento::with([
            'estudiante.persona_rol.persona',
            'curso.nivel_educativo',
            'materia.nivel_educativo',
            'gestion'
        ])->get();

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
        $seguimiento->load([
            'estudiante.persona_rol.persona',
            'curso.nivel_educativo',
            'materia.nivel_educativo',
            'gestion'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Seguimiento registrado correctamente.',
            'data' => $seguimiento
        ], 201);
    }

    public function show($id)
    {
        $seguimiento = Seguimiento::with([
            'estudiante.persona_rol.persona',
            'curso.nivel_educativo',
            'materia.nivel_educativo',
            'gestion'
        ])->find($id);

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
        $seguimiento->load([
            'estudiante.persona_rol.persona',
            'curso.nivel_educativo',
            'materia.nivel_educativo',
            'gestion'
        ]);

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

    // para ver por alumno

    public function indexByEstudiante($estudianteId)
    {
        $seguimientos = Seguimiento::with([
            'curso.nivel_educativo',
            'materia.nivel_educativo',
            'gestion'
        ])
        ->where('estudiantes_id_estudiante', $estudianteId)
        ->orderBy('fecha_reg_seg', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Seguimientos del estudiante',
            'data' => $seguimientos,
        ]);
    }

    public function createForm($estudianteId)
    {
        // Traer estudiante con su inscripción activa
        $estudiante = Estudiante::with(['cursos' => function($q) {
            $q->where('estado', 'inscrito')
              ->with('gestion', 'curso.nivel_educativo');
        }])->findOrFail($estudianteId);

        $inscripcion  = $estudiante->cursos->first();
        $cursoId      = $inscripcion->cursos_id_curso;
        $gestionId    = $inscripcion->gestiones_id_gestion;

        // Obtener los IDs de materia desde el pivot
        $materiaIds = CursoProfesorMateriaGestion::where('cursos_id_curso', $cursoId)
            ->where('gestiones_id_gestion', $gestionId)
            ->pluck('materias_id_materia');

        // Traer las materias por esos IDs
        $materias = Materia::whereIn('id_materia', $materiaIds)->get();

        return response()->json([
            'success' => true,
            'message' => 'Datos para crear seguimiento',
            'data'    => [
                'estudiante'   => $estudiante,
                'curso_id'     => $cursoId,
                'gestion_id'   => $gestionId,
                'materias'     => $materias,
            ],
        ], 200);
    }
}
