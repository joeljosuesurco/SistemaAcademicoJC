<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Documento;
use App\Models\PersonaRol;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class InscripcionProfesorController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'persona.nombres_persona' => 'required|string|max:100',
            'persona.apellidos_pat' => 'required|string|max:100',
            'persona.apellidos_mat' => 'required|string|max:100',
            'persona.fecha_nacimiento' => 'required|date',
            'persona.sexo_persona' => 'required|string',
            'persona.nacionalidad_persona' => 'required|string|max:100',
            'persona.direccion_persona' => 'nullable|string|max:200',
            'persona.celular_persona' => 'nullable|string|max:20',
            'persona.fotografia_persona' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'documento.carnet_identidad' => 'required|string|max:20|unique:documentos,carnet_identidad',
            'documento.certificado_nacimiento' => 'nullable|string|max:100',

            'profesor.especialidad_profesor' => 'required|string|max:100',
            'profesor.estado_profesor' => 'required|string|max:50',
            'profesor.titulo_provision_nacional' => 'nullable|string|max:100',
            'profesor.rda' => 'nullable|string|max:50',
            'profesor.cas' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $personaData = $request->input('persona');

            // Procesar fotografía
            if (
                $request->hasFile('persona.fotografia_persona') &&
                $request->file('persona.fotografia_persona')->isValid()
            ) {
                $foto = $request->file('persona.fotografia_persona');
                $nombreArchivo = uniqid('foto_') . '.' . $foto->getClientOriginalExtension();
                $foto->storeAs('public/fotos', $nombreArchivo);
                $personaData['fotografia_persona'] = $nombreArchivo;
            }

            $persona = Persona::create($personaData);

            // Documento
            $documento = new Documento($request->input('documento'));
            $documento->personas_id_persona = $persona->id_persona;
            $documento->save();

            // PersonaRol (profesor)
            $personaRol = PersonaRol::create([
                'personas_id_persona' => $persona->id_persona,
                'roles_id_rol' => 2, // ID del rol 'profesor'
            ]);

            // Profesor
            $profesorData = $request->input('profesor');
            $profesorData['persona_rol_id_persona_rol'] = $personaRol->id_persona_rol;
            $profesor = Profesor::create($profesorData);

            // Crear usuario automáticamente
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
                'persona_rol_id_persona_rol' => $personaRol->id_persona_rol,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profesor registrado correctamente.',
                'usuario_generado' => $username,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar profesor.',
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ], 500);
        }
    }
}
