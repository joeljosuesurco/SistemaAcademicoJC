<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteSeguimientoController extends Controller
{
    public function rendimientoPorMateria()
    {
        $datos = DB::table('seguimiento')
            ->join('materias', 'seguimiento.materias_id_materia', '=', 'materias.id_materia')
            ->join('nivel_educativos', 'materias.nivel_educativo_id', '=', 'nivel_educativos.id')
            ->select(
                'materias.id_materia',
                'materias.sigla_materia',
                'materias.area_materia',
                'nivel_educativos.nombre as nivel_educativo_nombre',
                DB::raw("AVG(CASE WHEN asistencia = 'Sí' THEN 5 ELSE 0 END) as asistencia"),
                DB::raw("AVG(participacion) as participacion"),
                DB::raw("AVG(disciplina) as disciplina"),
                DB::raw("AVG(puntualidad) as puntualidad"),
                DB::raw("AVG(CASE WHEN respeto = 'Sí' THEN 5 ELSE 0 END) as respeto"),
                DB::raw("AVG(CASE WHEN tolerancia = 'Sí' THEN 5 ELSE 0 END) as tolerancia"),
                DB::raw("AVG(CASE
                    WHEN estado_animo = 'Muy bien' THEN 5
                    WHEN estado_animo = 'Bien' THEN 4
                    WHEN estado_animo = 'Neutro' THEN 3
                    WHEN estado_animo = 'Estresado' THEN 2
                    WHEN estado_animo = 'Malo' THEN 1
                    ELSE 0 END) as estado_animo")
            )
            ->groupBy(
                'materias.id_materia',
                'materias.sigla_materia',
                'materias.area_materia',
                'nivel_educativos.nombre'
            )
            ->orderBy('materias.sigla_materia')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Rendimiento promedio por materia',
            'data' => $datos,
        ]);
    }

    public function rendimientoPorCurso()
    {
        $datos = DB::table('seguimiento')
            ->join('cursos', 'seguimiento.cursos_id_curso', '=', 'cursos.id_curso')
            ->select(
                'cursos.id_curso',
                'cursos.grado_curso',
                'cursos.paralelo_curso',
                DB::raw("AVG(CASE WHEN asistencia = 'Sí' THEN 5 ELSE 0 END) as asistencia"),
                DB::raw("AVG(participacion) as participacion"),
                DB::raw("AVG(disciplina) as disciplina"),
                DB::raw("AVG(puntualidad) as puntualidad"),
                DB::raw("AVG(CASE WHEN respeto = 'Sí' THEN 5 ELSE 0 END) as respeto"),
                DB::raw("AVG(CASE WHEN tolerancia = 'Sí' THEN 5 ELSE 0 END) as tolerancia"),
                DB::raw("AVG(CASE
                    WHEN estado_animo = 'Muy bien' THEN 5
                    WHEN estado_animo = 'Bien' THEN 4
                    WHEN estado_animo = 'Neutro' THEN 3
                    WHEN estado_animo = 'Estresado' THEN 2
                    WHEN estado_animo = 'Malo' THEN 1
                    ELSE 0 END) as estado_animo")
            )
            ->groupBy('cursos.id_curso', 'cursos.grado_curso', 'cursos.paralelo_curso')
            ->orderBy('cursos.grado_curso')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Rendimiento promedio por curso',
            'data' => $datos,
        ]);
    }

    public function rendimientoPorEstudiantes($curso_id, $materia_id)
    {
        $datos = DB::table('seguimiento')
            ->join('estudiantes', 'seguimiento.estudiantes_id_estudiante', '=', 'estudiantes.id_estudiante')
            ->join('persona_rol', 'estudiantes.persona_rol_id_persona_rol', '=', 'persona_rol.id_persona_rol')
            ->join('personas', 'persona_rol.personas_id_persona', '=', 'personas.id_persona')
            ->where('seguimiento.cursos_id_curso', $curso_id)
            ->where('seguimiento.materias_id_materia', $materia_id)
            ->select(
                'estudiantes.id_estudiante',
                DB::raw("CONCAT(personas.apellidos_pat, ' ', personas.apellidos_mat, ' ', personas.nombres_persona) as nombre_completo"),
                DB::raw("AVG(CASE WHEN asistencia = 'Sí' THEN 5 ELSE 0 END) as asistencia"),
                DB::raw("AVG(participacion) as participacion"),
                DB::raw("AVG(disciplina) as disciplina"),
                DB::raw("AVG(puntualidad) as puntualidad"),
                DB::raw("AVG(CASE WHEN respeto = 'Sí' THEN 5 ELSE 0 END) as respeto"),
                DB::raw("AVG(CASE WHEN tolerancia = 'Sí' THEN 5 ELSE 0 END) as tolerancia"),
                DB::raw("AVG(CASE
                    WHEN estado_animo = 'Muy bien' THEN 5
                    WHEN estado_animo = 'Bien' THEN 4
                    WHEN estado_animo = 'Neutro' THEN 3
                    WHEN estado_animo = 'Estresado' THEN 2
                    WHEN estado_animo = 'Malo' THEN 1
                    ELSE 0 END) as estado_animo")
            )
            ->groupBy('estudiantes.id_estudiante', 'personas.apellidos_pat', 'personas.apellidos_mat', 'personas.nombres_persona')
            ->orderBy('nombre_completo')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Rendimiento promedio por estudiante',
            'data' => $datos,
        ]);
    }
}
