<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Materia extends Model
{
    protected $table = 'materias';
    protected $primaryKey = 'id_materia';
    public $timestamps = false;

    protected $fillable = [
        'campo_materia',
        'area_materia',
        //'nombre_materia',
        'sigla_materia',
        //'estado_materia',
        'nivel_educativo_id',
    ];

    // Relaciones

    public function curso_profesor_materia_gestion(): HasMany
    {
        return $this->hasMany(CursoProfesorMateriaGestion::class, 'materias_id_materia', 'id_materia');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'materias_id_materia', 'id_materia');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'materias_id_materia', 'id_materia');
    }

    public function seguimiento(): HasMany
    {
        return $this->hasMany(Seguimiento::class, 'materias_id_materia', 'id_materia');
    }

    public function nivel_educativo(): BelongsTo
    {
        return $this->belongsTo(NivelEducativo::class, 'nivel_educativo_id');
    }
}
