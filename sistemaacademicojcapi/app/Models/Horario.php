<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    protected $table = 'horarios';
    protected $primaryKey = 'id_horario';
    public $timestamps = false;

    protected $fillable = [
        'dia',
        'hora_inicio',
        'hora_fin',
        'materias_id_materia',
        'cursos_id_curso',
        'gestiones_id_gestion',
    ];

    // Relaciones

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materias_id_materia', 'id_materia');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cursos_id_curso', 'id_curso');
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(Gestion::class, 'gestiones_id_gestion', 'id_gestion');
    }
}
