<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Administrativo extends Model
{
    protected $table = 'administrativos';
    protected $primaryKey = 'id_administrativo';
    public $timestamps = false;

    protected $fillable = [
        'cargo_admi',
        'estado_admi',
        'persona_rol_id_persona_rol',
    ];

    // Relaciones

    public function persona_rol(): BelongsTo
    {
        return $this->belongsTo(PersonaRol::class, 'persona_rol_id_persona_rol', 'id_persona_rol');
    }
}
