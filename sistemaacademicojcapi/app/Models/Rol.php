<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    // Relaciones

    public function persona_roles(): HasMany
    {
        return $this->hasMany(PersonaRol::class, 'roles_id_rol', 'id_rol');
    }
}
