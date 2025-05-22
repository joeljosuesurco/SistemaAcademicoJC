<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\NivelEducativo;
use Illuminate\Http\Request;

class EstudianteInfoController extends Controller
{
    public function index()
    {
        $estudiantes = Estudiante::with([
            'persona_rol.persona.documento',       // ✅ snake_case
            'persona_rol.rol',                     // ✅
            'cursos.curso.nivel_educativo',        // ✅ ya estandarizado antes
            'cursos.gestion',
            'padres.padre.persona_rol.persona'     // ✅ en caso de migrar también Padre
        ])->get();

        $niveles = NivelEducativo::all();

        return response()->json([
            'estudiantes' => $estudiantes,
            'niveles_educativos' => $niveles
        ]);
    }

    public function show($id)
    {
        $estudiante = Estudiante::with([
            'persona_rol.persona.documento',
            'persona_rol.rol',
            'cursos.curso.nivel_educativo',
            'cursos.gestion',
            'padres.padre.persona_rol.persona'
        ])->find($id);

        if (!$estudiante) {
            return response()->json(['error' => 'Estudiante no encontrado.'], 404);
        }

        $niveles = NivelEducativo::all();

        return response()->json([
            'estudiante' => $estudiante,
            'niveles_educativos' => $niveles
        ]);
    }
}

