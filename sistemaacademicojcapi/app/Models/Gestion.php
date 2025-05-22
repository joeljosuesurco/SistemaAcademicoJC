<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gestion extends Model
{
    protected $table = 'gestiones';
    protected $primaryKey = 'id_gestion';
    public $timestamps = false;

    protected $fillable = [
        'nombre_gestion',
        'gestion',
        'inicio_gestion',
        'fin_gestion',
        'estado_gestion',
    ];

    // Relaciones

    public function curso_estudiante(): HasMany
    {
        return $this->hasMany(CursoEstudianteGestion::class, 'gestiones_id_gestion', 'id_gestion');
    }

    public function curso_profesor_materia(): HasMany
    {
        return $this->hasMany(CursoProfesorMateriaGestion::class, 'gestiones_id_gestion', 'id_gestion');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'gestiones_id_gestion', 'id_gestion');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'gestiones_id_gestion', 'id_gestion');
    }

    public function seguimiento(): HasMany
    {
        return $this->hasMany(Seguimiento::class, 'gestiones_id_gestion', 'id_gestion');
    }
}
