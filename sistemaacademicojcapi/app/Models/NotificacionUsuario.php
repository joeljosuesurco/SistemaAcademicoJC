<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionUsuario extends Model
{
    protected $table = 'notificacion_usuario';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'notificaciones_id_notificacion',
        'users_id_user',
        'leido',
    ];

    protected $casts = [
        'leido' => 'boolean',
    ];

    // Relaciones

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id_user', 'id_user');
    }

    public function notificacion(): BelongsTo
    {
        return $this->belongsTo(Notificacion::class, 'notificaciones_id_notificacion', 'id_notificacion');
    }
}

