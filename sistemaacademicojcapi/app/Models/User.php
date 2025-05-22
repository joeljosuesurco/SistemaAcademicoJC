<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'name_user',
        'password',
        'state_user',
        'persona_rol_id_persona_rol',
    ];

    // Relaciones

    public function persona_rol(): BelongsTo
    {
        return $this->belongsTo(PersonaRol::class, 'persona_rol_id_persona_rol', 'id_persona_rol');
    }

    // Notificaciones que este usuario ha creado (si aplica)
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'users_id_user', 'id_user');
    }

    // Notificaciones asignadas a este usuario vía tabla intermedia
    public function notificacionesRecibidas(): HasMany
    {
        return $this->hasMany(NotificacionUsuario::class, 'users_id_user', 'id_user');
    }

    // Acceso directo a las notificaciones asignadas, con campo `leido`
    public function notificacionesPersonalizadas(): BelongsToMany
    {
        return $this->belongsToMany(
            Notificacion::class,
            'notificacion_usuario',
            'users_id_user',
            'notificaciones_id_notificacion',
            'id_user',
            'id_notificacion'
        )->withPivot('leido')->withTimestamps();
    }

    // JWT

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
