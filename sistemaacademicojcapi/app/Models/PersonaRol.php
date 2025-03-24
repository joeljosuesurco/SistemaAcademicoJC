<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PersonaRol extends Model
{
    protected $table = 'persona_rol';
    protected $primaryKey = 'id_persona_rol';
    public $timestamps = false;

    protected $fillable = [
        'roles_id_rol',
        'personas_id_persona',
    ];

    // Relaciones

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'personas_id_persona', 'id_persona');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'roles_id_rol', 'id_rol');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'persona_rol_id_persona_rol', 'id_persona_rol');
    }
}
