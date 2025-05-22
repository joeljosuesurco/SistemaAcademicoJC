<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;

class DocumentoController extends Controller
{
    public function index()
    {
        $documentos = Documento::all();

        return response()->json([
            'success' => true,
            'message' => 'Lista de documentos',
            'data' => $documentos
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'carnet_identidad' => 'required|string|max:50',
            'certificado_nacimiento' => 'required|string|max:50',
            'personas_id_persona' => 'required|exists:personas,id_persona',
        ]);

        $documento = Documento::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Documento registrado correctamente.',
            'data' => $documento
        ], 201);
    }

    public function show($id)
    {
        $documento = Documento::find($id);

        if (!$documento) {
            return response()->json([
                'success' => false,
                'message' => 'Documento no encontrado.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle del documento',
            'data' => $documento
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $documento = Documento::find($id);

        if (!$documento) {
            return response()->json([
                'success' => false,
                'message' => 'Documento no encontrado.',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'carnet_identidad' => 'required|string|max:50',
            'certificado_nacimiento' => 'required|string|max:50',
            'personas_id_persona' => 'required|exists:personas,id_persona',
        ]);

        $documento->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Documento actualizado correctamente.',
            'data' => $documento
        ], 200);
    }

    public function destroy($id)
    {
        $documento = Documento::find($id);

        if (!$documento) {
            return response()->json([
                'success' => false,
                'message' => 'Documento no encontrado.',
                'data' => null
            ], 404);
        }

        $documento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Documento eliminado correctamente.',
            'data' => null
        ], 200);
    }
}
