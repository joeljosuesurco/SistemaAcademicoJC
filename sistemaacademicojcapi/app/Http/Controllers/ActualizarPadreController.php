<?php

namespace App\Http\Controllers;

use App\Models\Padre;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\ActividadSistema;

class ActualizarPadreController extends Controller
{
    public function update(Request $request, $id)
    {
        $padre = Padre::with('persona_rol.persona.documento')->find($id);

        if (!$padre) {
            return response()->json([
                'success' => false,
                'message' => 'Padre no encontrado.',
            ], 404);
        }

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

            'documento.carnet_identidad' => 'required|string|max:20|unique:documentos,carnet_identidad,' . $padre->persona_rol->persona->documento->id_documento . ',id_documento',
            'documento.certificado_nacimiento' => 'nullable|string|max:100',

            'padre.estado_padre' => 'required|string|max:50',
            'padre.profesion_padre' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $persona = $padre->persona_rol->persona;
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
            $persona->documento->update($request->input('documento'));
            $padre->update($request->input('padre'));

            // Registrar log de actividad
            ActividadSistema::create([
                'usuario_id' => Auth::id(),
                'accion' => 'actualización',
                'modulo' => 'padres',
                'descripcion' => "Se actualizó el padre con ID: $id",
                'ip' => $request->ip() ?? $request->server('REMOTE_ADDR'),
                'navegador' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Padre actualizado correctamente.',
                'data' => $padre->fresh('persona_rol.persona.documento'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar padre.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

