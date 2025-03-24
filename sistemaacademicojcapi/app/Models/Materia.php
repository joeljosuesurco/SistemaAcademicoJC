<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    protected $table = 'materias';
    protected $primaryKey = 'id_materia';
    public $timestamps = false;

    protected $fillable = [
        'area_materia',
        'nombre_materia',
        'sigla_materia',
        'estado_materia',
    ];

    // Relaciones

    public function cursoProfesorMateriaGestion(): HasMany
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
}
