<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Padre extends Model
{
    protected $table = 'padres';
    protected $primaryKey = 'id_padre';
    public $timestamps = false;

    protected $fillable = [
        'persona_rol_id_persona_rol',
        'estado_padre',
        'profesion_padre',
    ];

    // Relaciones

    public function persona_rol(): BelongsTo
    {
        return $this->belongsTo(PersonaRol::class, 'persona_rol_id_persona_rol', 'id_persona_rol');
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(PadreEstudiante::class, 'padres_id_padre', 'id_padre');
    }
}
