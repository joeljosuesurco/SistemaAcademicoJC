<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nota;
use Illuminate\Support\Facades\Auth;

class HistorialNotasController extends Controller
{
    public function notasPorGestion(Request $request)
    {
        $user = Auth::user();
        $personaRol = $user->personaRol;

        $gestion = $request->query('gestion');
        $periodo = $request->query('periodo');
        $numeroPeriodo = $request->query('numero_periodo');
        $estudianteId = $request->query('estudiante_id');

        // Si no se pasa, asumimos estudiante autenticado
        if (!$estudianteId) {
            if (!$personaRol || !$personaRol->estudiante) {
                return response()->json(['error' => 'Este usuario no es estudiante.'], 403);
            }
            $estudianteId = $personaRol->estudiante->id_estudiante;
        }

        $notasQuery = Nota::with(['materia', 'curso.nivel_educativo', 'dimensiones'])
            ->where('estudiantes_id_estudiante', $estudianteId)
            ->whereHas('gestion', function ($q) use ($gestion) {
                $q->where('gestion', $gestion);
            })
            ->where('periodo', $periodo);

        if ($numeroPeriodo) {
            $notasQuery->where('numero_periodo', $numeroPeriodo);
        }

        $notas = $notasQuery->get();

        return response()->json([
            'success' => true,
            'data' => $notas
        ]);
    }
}
