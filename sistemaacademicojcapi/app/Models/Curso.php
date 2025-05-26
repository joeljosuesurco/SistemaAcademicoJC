<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\NivelEducativo;

class Curso extends Model
{
    protected $table = 'cursos';
    protected $primaryKey = 'id_curso';
    public $timestamps = false;

    protected $fillable = [
        'grado_curso',
        'paralelo_curso',
        'turno_curso',
        'aula_curso',
        'descripcion',
        'nivel_educativo_id',  // Nuevo campo
        'estado', // 👈 Añadir este campo
    ];

    // Relaciones

    public function estudiantes(): HasMany
    {
        return $this->hasMany(CursoEstudianteGestion::class, 'cursos_id_curso', 'id_curso');
    }

    public function profesoresMaterias(): HasMany
    {
        return $this->hasMany(CursoProfesorMateriaGestion::class, 'cursos_id_curso', 'id_curso');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'cursos_id_curso', 'id_curso');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'cursos_id_curso', 'id_curso');
    }

    public function seguimiento(): HasMany
    {
        return $this->hasMany(Seguimiento::class, 'cursos_id_curso', 'id_curso');
    }

    /////////////////////////////////////////////
    public function nivel_educativo(): BelongsTo
    {
        return $this->belongsTo(NivelEducativo::class, 'nivel_educativo_id');
    }
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
