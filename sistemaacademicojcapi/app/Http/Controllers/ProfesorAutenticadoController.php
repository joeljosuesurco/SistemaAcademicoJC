<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Nota;
use App\Models\CursoProfesorMateriaGestion;
use App\Models\Gestion;
use App\Models\CursoEstudianteGestion;


class ProfesorAutenticadoController extends Controller
{
    // Retorna los cursos y materias asignadas al profesor autenticado
    public function cursos()
    {
        $user = Auth::user();
        $personaRol = $user->persona_rol;
        $profesor = Profesor::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)->first();

        if (!$profesor) {
            return response()->json(['success' => false, 'message' => 'No se encontró el perfil del profesor.'], 404);
        }

        $asignaciones = $profesor->curso_profesor_materia_gestion()
            ->with(['curso.nivel_educativo', 'materia', 'gestion'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $asignaciones
        ]);
    }

    // Retorna los horarios del profesor autenticado
    public function horarios()
    {
        $user = Auth::user();
        $personaRol = $user->persona_rol;
        $profesor = Profesor::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)->first();

        if (!$profesor) {
            return response()->json(['success' => false, 'message' => 'No se encontró el perfil del profesor.'], 404);
        }

        $materiasIds = $profesor->curso_profesor_materia_gestion()->pluck('materias_id_materia')->unique();

        $horarios = \App\Models\Horario::with(['materia', 'curso', 'gestion'])
            ->whereIn('materias_id_materia', $materiasIds)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $horarios
        ]);
    }
    // notas del profesor

   public function notas()
    {
        $user = Auth::user();

        // Obtener el id_persona_rol desde el usuario
        $personaRol = $user->persona_rol;

        // Buscar el profesor vinculado a esa persona_rol
        $profesor = Profesor::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)->first();

        if (!$profesor) {
            return response()->json(['success' => false, 'message' => 'Profesor no encontrado.'], 404);
        }

        // Buscar la gestión activa
        $gestion = Gestion::where('estado_gestion', 'activa')->first();
        if (!$gestion) {
            return response()->json(['success' => false, 'message' => 'No hay gestión activa configurada.'], 500);
        }

        // Obtener las materias asignadas al profesor en la gestión activa
        $materiasIds = CursoProfesorMateriaGestion::where('profesores_id_profesor', $profesor->id_profesor)
            ->where('gestiones_id_gestion', $gestion->id_gestion)
            ->pluck('materias_id_materia');

        if ($materiasIds->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }

        // Cargar las notas de esas materias
        $notas = Nota::with([
                'dimensiones',
                'estudiante.persona_rol.persona',
                'curso.nivel_educativo',
                'materia.nivel_educativo',
                'gestion',
            ])
            ->where('gestiones_id_gestion', $gestion->id_gestion)
            ->whereIn('materias_id_materia', $materiasIds)
            ->get();

        return response()->json(['success' => true, 'data' => $notas]);
    }


    // subir notas profe
    public function estudiantesParaNota(Request $request)
    {
        $request->validate([
            'curso_id' => 'required|exists:cursos,id_curso',
            'materia_id' => 'required|exists:materias,id_materia',
            'periodo' => 'required|string',
            'numero_periodo' => 'required|integer',
        ]);

        $user = Auth::user();
        $personaRol = $user->persona_rol;

        $profesor = Profesor::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)->first();
        if (!$profesor) {
            return response()->json(['success' => false, 'message' => 'Profesor no encontrado.'], 404);
        }

        $gestion = Gestion::where('estado_gestion', 'activa')->first();
        if (!$gestion) {
            return response()->json(['success' => false, 'message' => 'No hay gestión activa configurada.'], 500);
        }

        $asignacionValida = CursoProfesorMateriaGestion::where([
            'cursos_id_curso' => $request->curso_id,
            'materias_id_materia' => $request->materia_id,
            'profesores_id_profesor' => $profesor->id_profesor,
            'gestiones_id_gestion' => $gestion->id_gestion,
        ])->exists();

        if (!$asignacionValida) {
            return response()->json(['success' => false, 'message' => 'No tienes asignada esta materia en este curso.'], 403);
        }

        $inscripciones = CursoEstudianteGestion::with([
            'estudiante.persona_rol.persona',
            'estudiante.notas' => function ($q) use ($request, $gestion) {
                $q->where('materias_id_materia', $request->materia_id)
                    ->where('gestiones_id_gestion', $gestion->id_gestion)
                    ->where('periodo', $request->periodo)
                    ->where('numero_periodo', $request->numero_periodo)
                    ->with('dimensiones');
            }
        ])
        ->where('cursos_id_curso', $request->curso_id)
        ->where('gestiones_id_gestion', $gestion->id_gestion)
        ->where('estado', 'inscrito')
        ->get();

        $estudiantes = $inscripciones->map(function ($e) {
            $persona = $e->estudiante->persona_rol->persona;
            $nombre = "{$persona->apellidos_pat} {$persona->apellidos_mat} {$persona->nombres_persona}";

            $dims = $e->estudiante->notas->first()?->dimensiones->pluck('valor_obtenido', 'nombre_dimension') ?? [];

            return [
                'id_estudiante' => $e->estudiante->id_estudiante,
                'nombre_completo' => $nombre,
                'ser' => $dims['ser_docente'] ?? '',
                'saber' => $dims['saber_docente'] ?? '',
                'hacer' => $dims['hacer_docente'] ?? '',
                'decidir' => $dims['decidir_docente'] ?? '',
                'ser_auto' => $dims['ser_autoeval'] ?? '',
                'decidir_auto' => $dims['decidir_autoeval'] ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'curso_id' => $request->curso_id,
                'materia_id' => $request->materia_id,
                'gestion_id' => $gestion->id_gestion,
                'estudiantes' => $estudiantes,
            ]
        ]);
    }
    // asguimiento para el profe
    public function estudiantesConSeguimiento(Request $request)
    {
        $cursoId = $request->query('curso_id');
        $gestionId = $request->query('gestion_id');

        if (!$cursoId || !$gestionId) {
            return response()->json(['error' => 'Faltan parámetros'], 400);
        }

        $inscripciones = CursoEstudianteGestion::with([
            'estudiante.persona_rol.persona',
            'curso.nivel_educativo',
        ])
        ->where('cursos_id_curso', $cursoId)
        ->where('gestiones_id_gestion', $gestionId)
        ->where('estado', 'inscrito')
        ->get();

        return response()->json([
            'success' => true,
            'data' => ['estudiantes' => $inscripciones]
        ]);
    }

   public function cursosSeguimiento()
{
    $user = auth('api')->user();
    $personaRol = $user->persona_rol;

    $profesor = Profesor::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)->first();

    if (!$profesor) {
        return response()->json(['error' => 'Profesor no encontrado'], 404);
    }

    $asignaciones = CursoProfesorMateriaGestion::with([
        'curso.nivel_educativo',
        'materia',
        'gestion',
    ])->where('profesores_id_profesor', $profesor->id_profesor)->get();

    $agrupado = [];

    foreach ($asignaciones as $asig) {
        $key = $asig->cursos_id_curso . '-' . $asig->gestiones_id_gestion;

        if (!isset($agrupado[$key])) {
            $agrupado[$key] = [
                'id_curso' => $asig->curso->id_curso,
                'curso_nombre' => $asig->curso->grado_curso . ' ' . $asig->curso->paralelo_curso,
                'nivel' => $asig->curso->nivel_educativo->nombre ?? '',
                'id_gestion' => $asig->gestion->id_gestion,
                'gestion' => $asig->gestion->gestion,
                'materias' => [],
                'estudiantes' => [],
            ];

            // Obtener estudiantes inscritos manualmente
            $inscritos = CursoEstudianteGestion::with('estudiante.persona_rol.persona')
                ->where('cursos_id_curso', $asig->cursos_id_curso)
                ->where('gestiones_id_gestion', $asig->gestiones_id_gestion)
                ->where('estado', 'inscrito')
                ->get();

            $agrupado[$key]['estudiantes'] = $inscritos->map(function ($ins) {
                $p = $ins->estudiante->persona_rol->persona;
                return [
                    'id_estudiante' => $ins->estudiante->id_estudiante,
                    'nombre_completo' => $p->apellidos_pat . ' ' . $p->apellidos_mat . ' ' . $p->nombres_persona,
                ];
            })->values();
        }

        // Agregar la materia
        $agrupado[$key]['materias'][] = [
            'id_materia' => $asig->materia->id_materia,
            'sigla' => $asig->materia->sigla_materia,
            'area' => $asig->materia->area_materia,
        ];
    }

    return response()->json([
        'success' => true,
        'data' => array_values($agrupado),
    ]);
}
public function registrarSeguimiento(Request $request)
{
    $request->validate([
        'estudiantes_id_estudiante' => 'required|exists:estudiantes,id_estudiante',
        'materias_id_materia' => 'required|exists:materias,id_materia',
        'cursos_id_curso' => 'required|exists:cursos,id_curso',
        'gestiones_id_gestion' => 'required|exists:gestiones,id_gestion',
        'fecha_reg_seg' => 'required|date|before_or_equal:today',
        'asistencia' => 'required|string',
        'participacion' => 'required|string',
        'disciplina' => 'required|string',
        'puntualidad' => 'required|string',
        'respeto' => 'required|string',
        'tolerancia' => 'required|string',
        'estado_animo' => 'required|string',
        'observaciones' => 'required|string|max:1000',
    ]);

    $seguimiento = new \App\Models\Seguimiento();
    $seguimiento->estudiantes_id_estudiante = $request->estudiantes_id_estudiante;
    $seguimiento->materias_id_materia = $request->materias_id_materia;
    $seguimiento->cursos_id_curso = $request->cursos_id_curso;
    $seguimiento->gestiones_id_gestion = $request->gestiones_id_gestion;
    $seguimiento->fecha_reg_seg = $request->fecha_reg_seg;
    $seguimiento->asistencia = $request->asistencia;
    $seguimiento->participacion = $request->participacion;
    $seguimiento->disciplina = $request->disciplina;
    $seguimiento->puntualidad = $request->puntualidad;
    $seguimiento->respeto = $request->respeto;
    $seguimiento->tolerancia = $request->tolerancia;
    $seguimiento->estado_animo = $request->estado_animo;
    $seguimiento->observaciones_seguimiento = $request->observaciones;
    $seguimiento->save();

    return response()->json([
        'success' => true,
        'message' => 'Seguimiento registrado correctamente.',
        'data' => $seguimiento,
    ]);
}

// PARA VER LISTAS DE ALUMNOS
    public function estudiantesPorCurso($idCurso)
{
    $user = Auth::user();
    $personaRol = $user->persona_rol;

    $profesor = Profesor::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)->first();
    if (!$profesor) {
        return response()->json(['success' => false, 'message' => 'Profesor no encontrado'], 404);
    }

    // Verifica si el curso fue asignado al profesor
    $asignado = CursoProfesorMateriaGestion::where('profesores_id_profesor', $profesor->id_profesor)
        ->where('cursos_id_curso', $idCurso)
        ->exists();

    if (!$asignado) {
        return response()->json(['success' => false, 'message' => 'Curso no asignado a este profesor'], 403);
    }

    $gestion = Gestion::where('estado_gestion', 'activa')->first();
    if (!$gestion) {
        return response()->json(['success' => false, 'message' => 'No hay gestión activa'], 500);
    }

    $estudiantes = CursoEstudianteGestion::with('estudiante.persona_rol.persona')
        ->where('cursos_id_curso', $idCurso)
        ->where('gestiones_id_gestion', $gestion->id_gestion)
        ->where('estado', 'inscrito')
        ->get()
        ->map(function ($ins) {
            $p = $ins->estudiante->persona_rol->persona;
            return [
                'id_estudiante' => $ins->estudiante->id_estudiante,
                'apellidos_pat' => $p->apellidos_pat,
                'apellidos_mat' => $p->apellidos_mat,
                'nombres'       => $p->nombres_persona,
            ];
        });

    return response()->json(['success' => true, 'data' => $estudiantes]);
}


}
