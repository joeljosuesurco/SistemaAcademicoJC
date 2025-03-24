<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    protected $table = 'cursos';
    protected $primaryKey = 'id_curso';
    public $timestamps = false;

    protected $fillable = [
        'grado_curso',
        'paralelo_curso',
        'nivel_curso',
        'aula_curso',
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
}
