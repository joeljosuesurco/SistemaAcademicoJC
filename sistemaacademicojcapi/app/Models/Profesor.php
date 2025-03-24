<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profesor extends Model
{
    protected $table = 'profesores';
    protected $primaryKey = 'id_profesor';
    public $timestamps = false;

    protected $fillable = [
        'especialidad_profesor',
        'estado_profesor',
        'persona_rol_id_persona_rol',
    ];

    // Relaciones

    public function personaRol(): BelongsTo
    {
        return $this->belongsTo(PersonaRol::class, 'persona_rol_id_persona_rol', 'id_persona_rol');
    }

    public function cursoProfesorMateriaGestion(): HasMany
    {
        return $this->hasMany(CursoProfesorMateriaGestion::class, 'profesores_id_profesor', 'id_profesor');
    }
}
