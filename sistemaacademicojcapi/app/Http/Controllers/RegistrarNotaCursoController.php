<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Gestion;
use App\Models\Nota;
use App\Models\CursoProfesorMateriaGestion;
use Illuminate\Http\Request;
use App\Models\CursoEstudianteGestion;

class RegistrarNotaCursoController extends Controller
{
    public function cursosConMaterias()
    {
        $gestion = Gestion::where('estado_gestion', 'activa')->first();

        if (!$gestion) {
            return response()->json(['success' => false, 'message' => 'No hay gestión activa.'], 404);
        }

        $cursos = Curso::with('nivel_educativo')->get();

        $data = $cursos->map(function ($curso) use ($gestion) {
            $materias = CursoProfesorMateriaGestion::with('materia')
                ->where('cursos_id_curso', $curso->id_curso)
                ->where('gestiones_id_gestion', $gestion->id_gestion)
                ->get()
                ->pluck('materia');

            return [
                'id_curso' => $curso->id_curso,
                'grado_curso' => $curso->grado_curso,
                'paralelo_curso' => $curso->paralelo_curso,
                'aula_curso' => $curso->aula_curso,
                'turno_curso' => $curso->turno_curso,
                'descripcion' => $curso->descripcion,
                'nivel_educativo' => $curso->nivel_educativo,
                'gestion' => $gestion->gestion,
                'id_gestion' => $gestion->id_gestion,
                'materias' => $materias,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function estudiantesConNotas(Request $request)
    {
        $request->validate([
            'curso_id' => 'required|exists:cursos,id_curso',
            'materia_id' => 'required|exists:materias,id_materia',
            'periodo' => 'required|string',
            'numero_periodo' => 'required|integer',
        ]);

        $gestion = Gestion::where('estado_gestion', 'activa')->first();

        $estudiantes = CursoEstudianteGestion::with([
            'estudiante.persona_rol.persona',
            'estudiante.notas' => function ($query) use ($request, $gestion) {
                $query->where('materias_id_materia', $request->materia_id)
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

        $data = $estudiantes->map(function ($e) {
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
            'data' => $data,
        ]);
    }



    public function registrarNota(Request $request)
    {
        $request->validate([
            'estudiante_id' => 'required|integer',
            'materia_id' => 'required|integer',
            'curso_id' => 'required|integer',
            'gestion_id' => 'required|integer',
            'periodo' => 'required|string',
            'numero_periodo' => 'required|integer',
            'dimensiones' => 'required|array'
        ]);

        $nota = Nota::updateOrCreate(
            [
                'estudiantes_id_estudiante' => $request->estudiante_id,
                'materias_id_materia' => $request->materia_id,
                'cursos_id_curso' => $request->curso_id,
                'gestiones_id_gestion' => $request->gestion_id,
                'periodo' => $request->periodo,
                'numero_periodo' => $request->numero_periodo,
            ]
        );

        $nota->dimensiones()->delete();
        foreach ($request->dimensiones as $dimension) {
            $nota->dimensiones()->create([
                'nombre_dimension' => $dimension['nombre_dimension'],
                'valor_obtenido' => $dimension['valor_obtenido'],
                'porcentaje' => 0, // 👈 esto evita el error en MySQL
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Nota registrada correctamente.']);
    }



}
