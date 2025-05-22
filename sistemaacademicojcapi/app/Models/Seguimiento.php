<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seguimiento extends Model
{
    protected $table = 'seguimiento';
    protected $primaryKey = 'id_seguimiento';
    public $timestamps = false;

    protected $fillable = [
        'fecha_reg_seg',
        'asistencia',
        'participacion',
        'disciplina',
        'puntualidad',
        'respeto',
        'tolerancia',
        'estado_animo',
        'observaciones_seguimiento',
        'estudiantes_id_estudiante',
        'cursos_id_curso',
        'materias_id_materia',
        'gestiones_id_gestion',
    ];

    // Relaciones

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiantes_id_estudiante', 'id_estudiante');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cursos_id_curso', 'id_curso');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materias_id_materia', 'id_materia');
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(Gestion::class, 'gestiones_id_gestion', 'id_gestion');
    }
}
