<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    // Relaciones

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id_user', 'id_user');
    }
}
