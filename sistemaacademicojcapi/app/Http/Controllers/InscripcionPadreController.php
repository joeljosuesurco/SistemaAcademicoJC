<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Documento;
use App\Models\PersonaRol;
use App\Models\Padre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InscripcionPadreController extends Controller
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

            'padre.estado_padre' => 'required|string|max:50',
            'padre.profesion_padre' => 'nullable|string|max:100',
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

            $documento = new Documento($request->input('documento'));
            $documento->personas_id_persona = $persona->id_persona;
            $documento->save();

            $personaRol = PersonaRol::create([
                'personas_id_persona' => $persona->id_persona,
                'roles_id_rol' => 3, // ID del rol PADRE
            ]);

            $padreData = $request->input('padre');
            $padreData['persona_rol_id_persona_rol'] = $personaRol->id_persona_rol;
            $padre = Padre::create($padreData);

            // ✅ Crear usuario
            $baseUsername = strtolower(
                preg_replace('/\s+/', '', $persona->nombres_persona . $persona->apellidos_pat)
            );
            $username = $baseUsername;
            $contador = 1;

            while (\App\Models\User::where('name_user', $username)->exists()) {
                $username = $baseUsername . $contador;
                $contador++;
            }

            \App\Models\User::create([
                'name_user' => $username,
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'state_user' => 'activo',
                'persona_rol_id_persona_rol' => $personaRol->id_persona_rol,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Padre registrado correctamente.',
                'usuario_generado' => $username,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar padre.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
