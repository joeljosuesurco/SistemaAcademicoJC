<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoEstudianteGestion extends Model
{
    protected $table = 'curso_estudiante_gestion';
    public $timestamps = false;
    public $incrementing = false;

    protected $primaryKey = null; // Porque la PK es compuesta

    protected $fillable = [
        'cursos_id_curso',
        'gestiones_id_gestion',
        'estudiantes_id_estudiante',
    ];

    // Relaciones

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cursos_id_curso', 'id_curso');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiantes_id_estudiante', 'id_estudiante');
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(Gestion::class, 'gestiones_id_gestion', 'id_gestion');
    }
}
