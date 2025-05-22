<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    protected $table = 'personas';
    protected $primaryKey = 'id_persona';
    public $timestamps = false;

    protected $fillable = [
        'nombres_persona',
        'apellidos_pat',
        'apellidos_mat',
        'sexo_persona',
        'direccion_persona',
        'nacionalidad_persona',
        'fecha_nacimiento',
        'celular_persona',
        'fotografia_persona',
    ];

    // Relaciones

    public function persona_roles(): HasMany
    {
        return $this->hasMany(PersonaRol::class, 'personas_id_persona', 'id_persona');
    }

    public function documento(): HasOne
    {
        return $this->hasOne(Documento::class, 'personas_id_persona', 'id_persona');
    }
}
