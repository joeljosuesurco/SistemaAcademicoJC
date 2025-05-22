<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    protected $primaryKey = 'id_notificacion';
    public $timestamps = false;

    protected $fillable = [
        'titulo_notificacion',
        'mensaje_notificacion',
        'fecha_notificacion',
        'estado_notificacion',
        'tipo_notificacion',
        'users_id_user',
    ];

    // Usuario que creó la notificación
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id_user', 'id_user');
    }

    // Relación con tabla intermedia para ver usuarios asignados (directo con campos pivot)
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'notificacion_usuario',
            'notificaciones_id_notificacion',
            'users_id_user',
            'id_notificacion',
            'id_user'
        )->withPivot('leido')->withTimestamps();
    }

    // Si deseas acceder a las filas de la tabla intermedia como modelos
    public function notificacionUsuarios(): HasMany
    {
        return $this->hasMany(NotificacionUsuario::class, 'notificaciones_id_notificacion', 'id_notificacion');
    }
}
