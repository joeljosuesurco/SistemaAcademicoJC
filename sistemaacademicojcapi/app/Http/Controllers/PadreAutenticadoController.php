<?php

namespace App\Http\Controllers;

use App\Models\Padre;
use App\Models\PadreEstudiante;
use App\Models\CursoEstudianteGestion;
use App\Models\Gestion;
use App\Models\Horario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Seguimiento;


class PadreAutenticadoController extends Controller
{
    // Devuelve los hijos asignados al padre autenticado con su curso

    public function hijos()
    {
        $user = auth('api')->user();
        $personaRol = $user->persona_rol;

        $padre = \App\Models\Padre::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)
            ->with(['estudiantes.estudiante.persona_rol.persona.documento', 'estudiantes.estudiante.cursos' => function ($q) {
                $q->where('estado', 'inscrito')->with('curso.nivel_educativo', 'gestion');
            }])
            ->first();

        if (!$padre) {
            return response()->json([
                'success' => false,
                'message' => 'Padre no encontrado.',
            ], 404);
        }

        $hijos = [];

        foreach ($padre->estudiantes as $relacion) {
            $est = $relacion->estudiante;
            $persona = $est->persona_rol->persona ?? null;
            $documento = $persona->documento ?? null;
            $inscripcion = $est->cursos->first();
            $curso = $inscripcion?->curso;

            $hijos[] = [
                'id_estudiante' => $est->id_estudiante,
                'rude' => $est->rude, // ✅ corregido aquí
                'nombre_completo' => "{$persona->apellidos_pat} {$persona->apellidos_mat} {$persona->nombres_persona}",
                'apellidos_pat' => $persona->apellidos_pat,
                'apellidos_mat' => $persona->apellidos_mat,
                'nombres' => $persona->nombres_persona,
                'fecha_nacimiento' => $persona->fecha_nacimiento,
                'sexo' => $persona->sexo_persona,
                'nacionalidad' => $persona->nacionalidad_persona,
                'direccion' => $persona->direccion_persona,
                'celular' => $persona->celular_persona,
                'foto' => $persona->fotografia_persona,
                'ci' => $documento?->carnet_identidad,
                'cert_nac' => $documento?->certificado_nacimiento,
                'libreta' => $est->libreta_anterior,
                'curso' => [
                    'grado_curso' => $curso?->grado_curso,
                    'paralelo_curso' => $curso?->paralelo_curso,
                    'nivel' => $curso?->nivel_educativo?->nombre,
                    'id_curso' => $curso?->id_curso,
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $hijos,
        ]);
    }





    // Devuelve el horario del curso actual del hijo si pertenece al padre
    public function horario($idEstudiante)
    {
        $user = Auth::user();
        $personaRol = $user->persona_rol;

        $padre = Padre::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)->first();

        $relacion = PadreEstudiante::where('padres_id_padre', $padre->id_padre)
            ->where('estudiantes_id_estudiante', $idEstudiante)
            ->exists();

        if (!$relacion) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $gestion = Gestion::where('estado_gestion', 'activa')->first();

        $inscripcion = CursoEstudianteGestion::where([
            'estudiantes_id_estudiante' => $idEstudiante,
            'gestiones_id_gestion' => $gestion->id_gestion,
            'estado' => 'inscrito',
        ])->first();

        if (!$inscripcion) {
            return response()->json(['success' => false, 'message' => 'El estudiante no está inscrito'], 404);
        }

        $horarios = Horario::with('materia')
            ->where('cursos_id_curso', $inscripcion->cursos_id_curso)
            ->where('gestiones_id_gestion', $gestion->id_gestion)
            ->get();

        return response()->json(['success' => true, 'data' => $horarios]);
    }

    // notas para el papa
        public function notasPorEstudiante(Request $request, $idEstudiante)
    {
        $request->validate([
            'periodo' => 'required|string',
            'numero_periodo' => 'required|integer|min:1|max:3',
        ]);

        $user = auth('api')->user();
        $personaRol = $user->persona_rol;

        // Verificar que el estudiante esté asignado a este padre
        $padre = \App\Models\Padre::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)
            ->with(['estudiantes.estudiante'])
            ->first();

        $esHijo = $padre?->estudiantes->contains(fn($e) => $e->estudiante->id_estudiante == $idEstudiante);

        if (!$esHijo) {
            return response()->json(['success' => false, 'message' => 'Este estudiante no está asignado a usted.'], 403);
        }

        // Obtener la inscripción activa
        $inscripcion = \App\Models\CursoEstudianteGestion::with(['curso.nivel_educativo', 'gestion'])
            ->where('estudiantes_id_estudiante', $idEstudiante)
            ->where('estado', 'inscrito')
            ->first();

        if (!$inscripcion) {
            return response()->json(['success' => false, 'message' => 'Estudiante no inscrito actualmente.'], 404);
        }

        $cursoId = $inscripcion->cursos_id_curso;
        $gestionId = $inscripcion->gestiones_id_gestion;

        // Obtener notas con dimensiones y materias
        $notas = \App\Models\Nota::with(['materia', 'dimensiones'])
            ->where('estudiantes_id_estudiante', $idEstudiante)
            ->where('cursos_id_curso', $cursoId)
            ->where('gestiones_id_gestion', $gestionId)
            ->where('periodo', $request->periodo)
            ->where('numero_periodo', $request->numero_periodo)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'estudiante_id' => $idEstudiante,
                'curso_id' => $cursoId,
                'gestion_id' => $gestionId,
                'periodo' => $request->periodo,
                'numero_periodo' => $request->numero_periodo,
                'notas' => $notas,
            ],
        ]);
    }

    //sehuimiento para el papa

    public function seguimientos($idEstudiante)
    {
        $user = Auth::user();
        $personaRol = $user->persona_rol;

        // Obtener el padre autenticado
        $padre = Padre::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)->first();

        if (!$padre) {
            return response()->json([
                'success' => false,
                'message' => 'Padre no encontrado.',
            ], 404);
        }

        // Verificar que el estudiante esté vinculado al padre
        $esHijo = PadreEstudiante::where('padres_id_padre', $padre->id_padre)
            ->where('estudiantes_id_estudiante', $idEstudiante)
            ->exists();

        if (!$esHijo) {
            return response()->json([
                'success' => false,
                'message' => 'Este estudiante no está asignado a usted.',
            ], 403);
        }

        // Obtener seguimientos del estudiante
        $seguimientos = Seguimiento::with(['materia', 'curso', 'gestion'])
            ->where('estudiantes_id_estudiante', $idEstudiante)
            ->orderByDesc('fecha_reg_seg')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $seguimientos,
        ]);
    }



}
