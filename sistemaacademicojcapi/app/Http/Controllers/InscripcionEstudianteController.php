<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona;
use App\Models\PersonaRol;
use App\Models\Estudiante;
use App\Models\Documento;
use App\Models\CursoEstudianteGestion;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InscripcionEstudianteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            // Persona
            'persona.nombres_persona' => 'required|string|max:100',
            'persona.apellidos_pat' => 'required|string|max:100',
            'persona.apellidos_mat' => 'required|string|max:100',
            'persona.sexo_persona' => 'required|string',
            'persona.fecha_nacimiento' => 'required|date',
            'persona.direccion_persona' => 'required|string',
            'persona.nacionalidad_persona' => 'required|string',
            'persona.celular_persona' => 'nullable|string|max:20',
            'persona.fotografia_persona' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Documento
            'documento.carnet_identidad' => 'required|string|max:50',
            'documento.certificado_nacimiento' => 'required|string|max:50',

            // Estudiante
            'estudiante.obsev_estudiante' => 'nullable|string',
            'estudiante.rude' => 'required|string|max:50|unique:estudiantes,rude',
            'estudiante.libreta_anterior' => 'nullable|string|max:50',

            // Inscripción
            'inscripcion.cursos_id_curso' => 'nullable|exists:cursos,id_curso',
            'inscripcion.gestiones_id_gestion' => 'nullable|exists:gestiones,id_gestion',
        ]);

        // ✅ Procesar la imagen si es válida
        $personaData = $request->persona;

        if (
            isset($_FILES['persona']['name']['fotografia_persona']) &&
            $request->file('persona.fotografia_persona') &&
            $request->file('persona.fotografia_persona')->isValid()
        ) {
            $foto = $request->file('persona.fotografia_persona');
            $nombreArchivo = uniqid('foto_') . '.' . $foto->getClientOriginalExtension();
            $foto->storeAs('public/fotos', $nombreArchivo);
            $personaData['fotografia_persona'] = $nombreArchivo;
        }

        // 1. Crear persona con datos definitivos
        $persona = Persona::create($personaData);

        // 2. Rol estudiante
        $rolEstudiante = Rol::where('nombre', 'estudiante')->firstOrFail();

        // 3. PersonaRol
        $personaRol = PersonaRol::create([
            'roles_id_rol' => $rolEstudiante->id_rol,
            'personas_id_persona' => $persona->id_persona
        ]);

        // 4. Estudiante
        $estudianteData = $request->estudiante;
        $estudianteData['persona_rol_id_persona_rol'] = $personaRol->id_persona_rol;
        $estudiante = Estudiante::create($estudianteData);

        // 5. Documentos
        Documento::create([
            'carnet_identidad' => $request->documento['carnet_identidad'],
            'certificado_nacimiento' => $request->documento['certificado_nacimiento'],
            'personas_id_persona' => $persona->id_persona
        ]);

        // 6. Inscripción (opcional)
        if (
            $request->filled('inscripcion.cursos_id_curso') &&
            $request->filled('inscripcion.gestiones_id_gestion')
        ) {
            CursoEstudianteGestion::create([
                'estudiantes_id_estudiante' => $estudiante->id_estudiante,
                'cursos_id_curso' => $request->inscripcion['cursos_id_curso'],
                'gestiones_id_gestion' => $request->inscripcion['gestiones_id_gestion'],
                'estado' => 'inscrito'
            ]);
        }

        // 7. Usuario
        $baseUsername = strtolower(
            preg_replace('/\s+/', '', $persona->nombres_persona . $persona->apellidos_pat)
        );
        $username = $baseUsername;
        $contador = 1;

        while (User::where('name_user', $username)->exists()) {
            $username = $baseUsername . $contador;
            $contador++;
        }

        User::create([
            'name_user' => $username,
            'password' => Hash::make('admin123'),
            'state_user' => 'activo',
            'persona_rol_id_persona_rol' => $personaRol->id_persona_rol
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estudiante registrado correctamente.',
            'data' => [
                'estudiante' => $estudiante
            ]
        ], 201);
    }

}
