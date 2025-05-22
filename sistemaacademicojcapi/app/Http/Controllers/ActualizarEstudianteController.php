<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Estudiante;
use App\Models\Documento;
use App\Models\CursoEstudianteGestion;

class ActualizarEstudianteController extends Controller
{
    public function update(Request $request, $id)
    {
        $request->validate([
            'persona.nombres_persona' => 'required|string|max:100',
            'persona.apellidos_pat' => 'required|string|max:100',
            'persona.apellidos_mat' => 'required|string|max:100',
            'persona.sexo_persona' => 'required|string',
            'persona.fecha_nacimiento' => 'required|date',
            'persona.direccion_persona' => 'required|string',
            'persona.nacionalidad_persona' => 'required|string',
            'persona.celular_persona' => 'nullable|string|max:20',
            'persona.fotografia_persona' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'documento.carnet_identidad' => 'required|string|max:50',
            'documento.certificado_nacimiento' => 'required|string|max:50',

            'estudiante.obsev_estudiante' => 'nullable|string',
            'estudiante.rude' => 'required|string|max:50|unique:estudiantes,rude,' . $id . ',id_estudiante',
            'estudiante.libreta_anterior' => 'nullable|string|max:50',

            'remover_inscripcion' => 'sometimes|boolean',
            'rehabilitar' => 'sometimes|boolean',
            'curso_id' => 'required_with_all:remover_inscripcion,rehabilitar|integer|exists:cursos,id_curso',
            'gestion_id' => 'required_with_all:remover_inscripcion,rehabilitar|integer|exists:gestiones,id_gestion',
        ]);

        $estudiante = Estudiante::with('persona_rol.persona.documento')->findOrFail($id);
        $persona = $estudiante->persona_rol->persona;

        $personaData = $request->input('persona');

        // 📷 Si hay foto nueva, procesar y guardar
        if (
            $request->hasFile('persona.fotografia_persona') &&
            $request->file('persona.fotografia_persona')->isValid()
        ) {
            $foto = $request->file('persona.fotografia_persona');
            $nombreArchivo = uniqid('foto_') . '.' . $foto->getClientOriginalExtension();
            $foto->storeAs('public/fotos', $nombreArchivo);

            $personaData['fotografia_persona'] = $nombreArchivo;

            // (opcional) eliminar archivo anterior si no es nulo
            // if ($persona->fotografia_persona) {
            //     Storage::delete('public/fotos/' . $persona->fotografia_persona);
            // }
        }

        $persona->update($personaData);

        if ($persona->documento) {
            $persona->documento->update($request->documento);
        } else {
            Documento::create(array_merge(
                $request->documento,
                ['personas_id_persona' => $persona->id_persona]
            ));
        }

        $estudiante->update($request->estudiante);

        if ($request->remover_inscripcion && $request->has(['curso_id', 'gestion_id'])) {
            CursoEstudianteGestion::where([
                ['estudiantes_id_estudiante', $id],
                ['cursos_id_curso', $request->curso_id],
                ['gestiones_id_gestion', $request->gestion_id],
            ])->update(['estado' => 'no_inscrito']);
        }

        if ($request->rehabilitar && $request->has(['curso_id', 'gestion_id'])) {
            CursoEstudianteGestion::where([
                ['estudiantes_id_estudiante', $id],
                ['cursos_id_curso', $request->curso_id],
                ['gestiones_id_gestion', $request->gestion_id],
                ['estado', 'no_inscrito']
            ])->update(['estado' => 'inscrito']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Estudiante actualizado correctamente.',
            'data' => $estudiante
        ], 200);
    }
}
