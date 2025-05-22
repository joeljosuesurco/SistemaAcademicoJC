<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoProfesorMateriaGestion extends Model
{
    protected $table = 'curso_profesor_materia_gestion';
    protected $primaryKey = 'id';         // ✅ Nuevo campo como clave primaria
    public $incrementing = true;          // ✅ Habilitamos autoincremento
    public $timestamps = false;

    protected $fillable = [
        'cursos_id_curso',
        'profesores_id_profesor',
        'materias_id_materia',
        'gestiones_id_gestion',
    ];

    // Relaciones

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cursos_id_curso', 'id_curso');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'profesores_id_profesor', 'id_profesor');
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
