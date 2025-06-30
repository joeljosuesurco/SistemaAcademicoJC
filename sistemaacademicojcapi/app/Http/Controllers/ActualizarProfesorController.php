<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\ActividadSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ActualizarProfesorController extends Controller
{
    public function update(Request $request, $id)
    {
        $profesor = Profesor::with('persona_rol.persona.documento')->find($id);

        if (!$profesor) {
            return response()->json([
                'success' => false,
                'message' => 'Profesor no encontrado.',
            ], 404);
        }

        $personaRol = $profesor->persona_rol;
        $persona = $personaRol->persona;
        $documento = $persona->documento;

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

            'documento.carnet_identidad' => 'required|string|max:20|unique:documentos,carnet_identidad,' . $documento->id_documento . ',id_documento',
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

            if (
                $request->hasFile('persona.fotografia_persona') &&
                $request->file('persona.fotografia_persona')->isValid()
            ) {
                $foto = $request->file('persona.fotografia_persona');
                $nombreArchivo = uniqid('foto_') . '.' . $foto->getClientOriginalExtension();
                $foto->storeAs('public/fotos', $nombreArchivo);
                $personaData['fotografia_persona'] = $nombreArchivo;
            }

            $persona->update($personaData);
            $documento->update($request->input('documento'));
            $profesor->update($request->input('profesor'));

            // Registrar en actividad del sistema
            ActividadSistema::create([
                'usuario_id' => Auth::id(),
                'accion' => 'actualización',
                'modulo' => 'profesores',
                'descripcion' => "Se actualizó el profesor con ID: $id",
                'ip' => $request->ip() ?? $request->server('REMOTE_ADDR'),
                'navegador' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profesor actualizado correctamente.',
                'data' => $profesor->load('persona_rol.persona.documento'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar profesor.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
