<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nota;
use App\Models\Estudiante;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteNotasController extends Controller
{
    public function boletaPDF($estudianteId, $periodoNumero)
    {
        $estudiante = Estudiante::with('persona_rol.persona')->findOrFail($estudianteId);

        $notas = Nota::with('materia')
            ->where('estudiantes_id_estudiante', $estudianteId)
            ->where('numero_periodo', $periodoNumero)
            ->get();

        $materias = [];

        foreach ($notas as $nota) {
            $materias[] = [
                'materia' => $nota->materia->area_materia . ' (' . $nota->materia->sigla_materia . ')',
                'nota_final' => $nota->nota_final,
                'periodo' => $nota->numero_periodo,
            ];
        }

        $pdf = Pdf::loadView('reportes.boleta_notas', [
            'estudiante' => $estudiante,
            'materias' => $materias,
            'periodo' => $periodoNumero,
        ]);

        return $pdf->download("Boleta_Estudiante_{$estudianteId}_Trimestre{$periodoNumero}.pdf");
    }

    public function boletaDesdeFrontend(Request $request)
    {
        $estudiante = $request->input('estudiante');
        $materias = $request->input('materias');
        $periodo = $request->input('periodo');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.boleta_notas', [
            'estudiante' => (object) $estudiante,
            'materias' => $materias,
            'periodo' => $periodo,
        ]);

        return $pdf->download("Boleta_Estudiante_{$estudiante['id']}_Trimestre{$periodo}.pdf");
    }

    public function notasProfesorPDF(Request $request)
    {
        $curso = (object) $request->input('curso');
        $materia = (object) $request->input('materia');
        $periodo = $request->input('periodo');
        $notas = $request->input('notas');

        $pdf = Pdf::loadView('reportes.notas_profesor', [
            'curso' => $curso,
            'materia' => $materia,
            'periodo' => $periodo,
            'notas' => $notas,
        ]);

        $fileName = "Reporte_Notas_{$curso->nombre}_{$materia->sigla}_P{$periodo}.pdf";
        return $pdf->download($fileName);
    }

    public function estudiantesCursoPDF(Request $request)
    {
        $curso = (object) $request->input('curso');
        $estudiantes = $request->input('estudiantes');

        $pdf = Pdf::loadView('reportes.estudiantes_profesor', [
            'curso' => $curso,
            'estudiantes' => $estudiantes,
        ]);

        $nombreCurso = strtoupper($curso->grado_curso . '_' . $curso->paralelo_curso);
        return $pdf->download("Estudiantes_Curso_{$nombreCurso}.pdf");
    }
}
