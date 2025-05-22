<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estudiante extends Model
{
    protected $table = 'estudiantes';
    protected $primaryKey = 'id_estudiante';
    public $timestamps = false;

    protected $fillable = [
        'persona_rol_id_persona_rol',
        'obsev_estudiante',
        'libreta_anterior',
        'rude',
    ];

    // Relaciones
    public function persona_rol(): BelongsTo
    {
        return $this->belongsTo(PersonaRol::class, 'persona_rol_id_persona_rol', 'id_persona_rol');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'estudiantes_id_estudiante', 'id_estudiante');
    }

    public function seguimiento(): HasMany
    {
        return $this->hasMany(Seguimiento::class, 'estudiantes_id_estudiante', 'id_estudiante');
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(CursoEstudianteGestion::class, 'estudiantes_id_estudiante', 'id_estudiante');
    }

    public function padres(): HasMany
    {
        return $this->hasMany(PadreEstudiante::class, 'estudiantes_id_estudiante', 'id_estudiante');
    }

    // Alias de compatibilidad temporal
    public function personaRol(): BelongsTo
    {
        return $this->persona_rol();
    }
}
