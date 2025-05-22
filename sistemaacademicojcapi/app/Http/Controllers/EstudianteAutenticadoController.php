<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CursoEstudianteGestion;
use App\Models\Estudiante;
use Illuminate\Support\Facades\Auth;

class EstudianteAutenticadoController extends Controller
{
    public function datosEstudianteAutent()
    {
        $user = Auth::user();
        $personaRol = $user->persona_rol;

        $estudiante = Estudiante::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)
            ->with(['persona_rol.persona.documento', 'cursos' => function ($q) {
                $q->where('estado', 'inscrito')
                  ->with('curso.nivel_educativo', 'gestion');
            }])
            ->first();

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado.',
            ], 404);
        }

        $persona = $estudiante->persona_rol->persona ?? null;
        $documento = $persona->documento ?? null;
        $inscripcion = $estudiante->cursos->first();
        $curso = $inscripcion?->curso;

        return response()->json([
            'success' => true,
            'data' => [
                'id_estudiante' => $estudiante->id_estudiante,
                'rude' => $estudiante->rude,
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
                'libreta' => $estudiante->libreta_anterior,
                'curso' => [
                    'grado_curso' => $curso?->grado_curso,
                    'paralelo_curso' => $curso?->paralelo_curso,
                    'nivel' => $curso?->nivel_educativo?->nombre,
                    'id_curso' => $curso?->id_curso,
                ],
            ]
        ]);
    }

    public function horario()
    {
        $user = auth('api')->user();
        $personaRol = $user->persona_rol;

        $estudiante = \App\Models\Estudiante::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)->first();

        if (!$estudiante) {
            return response()->json(['success' => false, 'message' => 'Estudiante no encontrado.'], 404);
        }

        $gestion = \App\Models\Gestion::where('estado_gestion', 'activa')->first();

        if (!$gestion) {
            return response()->json(['success' => false, 'message' => 'No hay gestión activa.'], 404);
        }

        $inscripcion = \App\Models\CursoEstudianteGestion::where([
            'estudiantes_id_estudiante' => $estudiante->id_estudiante,
            'gestiones_id_gestion' => $gestion->id_gestion,
            'estado' => 'inscrito',
        ])->first();

        if (!$inscripcion) {
            return response()->json(['success' => false, 'message' => 'Estudiante no está inscrito.'], 404);
        }

        $horarios = \App\Models\Horario::with('materia')
            ->where('cursos_id_curso', $inscripcion->cursos_id_curso)
            ->where('gestiones_id_gestion', $gestion->id_gestion)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $horarios,
        ]);
    }
    // PARA NOTITAS
    public function notas(Request $request)
    {
        $request->validate([
            'periodo' => 'required|string',
            'numero_periodo' => 'required|integer|min:1|max:3',
        ]);

        $user = auth('api')->user();
        $personaRol = $user->persona_rol;

        $estudiante = \App\Models\Estudiante::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)->first();

        if (!$estudiante) {
            return response()->json(['success' => false, 'message' => 'Estudiante no encontrado.'], 404);
        }

        $inscripcion = \App\Models\CursoEstudianteGestion::with(['curso.nivel_educativo', 'gestion'])
            ->where('estudiantes_id_estudiante', $estudiante->id_estudiante)
            ->where('estado', 'inscrito')
            ->first();

        if (!$inscripcion) {
            return response()->json(['success' => false, 'message' => 'No está inscrito actualmente.'], 404);
        }

        $notas = \App\Models\Nota::with(['materia', 'dimensiones'])
            ->where('estudiantes_id_estudiante', $estudiante->id_estudiante)
            ->where('cursos_id_curso', $inscripcion->cursos_id_curso)
            ->where('gestiones_id_gestion', $inscripcion->gestiones_id_gestion)
            ->where('periodo', $request->periodo)
            ->where('numero_periodo', $request->numero_periodo)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'curso' => $inscripcion->curso?->grado_curso . ' ' . $inscripcion->curso?->paralelo_curso,
                'nivel' => $inscripcion->curso?->nivel_educativo?->nombre,
                'periodo' => $request->periodo,
                'numero_periodo' => $request->numero_periodo,
                'notas' => $notas,
            ]
        ]);
    }

    // para seguimiento
    public function seguimientos()
    {
        $user = auth('api')->user();
        $personaRol = $user->persona_rol;

        $estudiante = \App\Models\Estudiante::where('persona_rol_id_persona_rol', $personaRol->id_persona_rol)->first();

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado.',
            ], 404);
        }

        $seguimientos = \App\Models\Seguimiento::with(['materia', 'curso', 'gestion'])
            ->where('estudiantes_id_estudiante', $estudiante->id_estudiante)
            ->orderByDesc('fecha_reg_seg')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $seguimientos,
        ]);
    }



}
