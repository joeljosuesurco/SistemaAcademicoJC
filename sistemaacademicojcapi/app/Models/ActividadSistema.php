<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadSistema extends Model
{
    use HasFactory;

    protected $table = 'actividad_sistemas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'usuario_id',
        'accion',
        'modulo',
        'descripcion',
        'ip',
        'navegador',
    ];

    /**
     * Relación con el usuario que realizó la actividad
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id_user');
    }
}
