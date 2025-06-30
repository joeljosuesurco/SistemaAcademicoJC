<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Models\DimensionNota;
use App\Models\ActividadSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CrearNotaController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'estudiante_id'                  => 'required|exists:estudiantes,id_estudiante',
            'materia_id'                     => 'required|exists:materias,id_materia',
            'curso_id'                       => 'required|exists:cursos,id_curso',
            'gestion_id'                     => 'required|exists:gestiones,id_gestion',
            'periodo'                        => 'required|string|max:50',
            'numero_periodo'                 => 'required|integer|between:1,3',
            'nota_final'                     => 'nullable|numeric|min:0',
            'dimensiones'                    => 'nullable|array',
            'dimensiones.*.nombre_dimension' => 'required|string|max:50',
            'dimensiones.*.valor_obtenido'   => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $rawNota        = $request->input('nota_final');
        $nota_final_cap = $rawNota !== null ? min((int) $rawNota, 100) : null;

        $nota = Nota::where('estudiantes_id_estudiante', $request->estudiante_id)
            ->where('materias_id_materia',    $request->materia_id)
            ->where('cursos_id_curso',        $request->curso_id)
            ->where('gestiones_id_gestion',   $request->gestion_id)
            ->where('periodo',                $request->periodo)
            ->where('numero_periodo',         $request->numero_periodo)
            ->first();

        if ($nota) {
            $nota->nota_final = $nota_final_cap;
            $nota->save();

            $nota->dimensiones()->delete();
            if (!empty($request->dimensiones)) {
                foreach ($request->dimensiones as $dim) {
                    if ($dim['valor_obtenido'] !== null && $dim['valor_obtenido'] !== '') {
                        DimensionNota::create([
                            'notas_id_nota'    => $nota->id_nota,
                            'nombre_dimension' => $dim['nombre_dimension'],
                            'valor_obtenido'   => $dim['valor_obtenido'],
                            'porcentaje'       => 0,
                        ]);
                    }
                }
            }

            $nota->refresh()->load('dimensiones');

            // 🔒 Log de actualización
            ActividadSistema::create([
                'usuario_id' => $user->id_user,
                'accion' => 'actualizar',
                'modulo' => 'notas',
                'descripcion' => "Actualizó nota del estudiante {$request->estudiante_id}, materia {$request->materia_id}, periodo {$request->numero_periodo}",
                'ip' => $request->ip(),
                'navegador' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nota actualizada correctamente.',
                'data'    => $nota,
            ], 200);

        } else {
            $nota = new Nota([
                'estudiantes_id_estudiante' => $request->estudiante_id,
                'materias_id_materia'       => $request->materia_id,
                'cursos_id_curso'           => $request->curso_id,
                'gestiones_id_gestion'      => $request->gestion_id,
                'periodo'                   => $request->periodo,
                'numero_periodo'            => $request->numero_periodo,
            ]);
            $nota->nota_final = $nota_final_cap;
            $nota->save();

            if (!empty($request->dimensiones)) {
                foreach ($request->dimensiones as $dim) {
                    if ($dim['valor_obtenido'] !== null && $dim['valor_obtenido'] !== '') {
                        DimensionNota::create([
                            'notas_id_nota'    => $nota->id_nota,
                            'nombre_dimension' => $dim['nombre_dimension'],
                            'valor_obtenido'   => $dim['valor_obtenido'],
                            'porcentaje'       => 0,
                        ]);
                    }
                }
            }

            $nota->refresh()->load('dimensiones');

            // 🔒 Log de creación
            ActividadSistema::create([
                'usuario_id' => $user->id_user,
                'accion' => 'crear',
                'modulo' => 'notas',
                'descripcion' => "Registró nota para estudiante {$request->estudiante_id}, materia {$request->materia_id}, periodo {$request->numero_periodo}",
                'ip' => $request->ip(),
                'navegador' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nota registrada correctamente.',
                'data'    => $nota,
            ], 201);
        }
    }
}
