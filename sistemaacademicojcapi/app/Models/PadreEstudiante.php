<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PadreEstudiante extends Model
{
    protected $table = 'padre_estudiante';
    public $timestamps = false;
    public $incrementing = false;

    protected $primaryKey = null; // Clave primaria compuesta

    protected $fillable = [
        'padres_id_padre',
        'estudiantes_id_estudiante',
    ];

    // Relaciones

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Padre::class, 'padres_id_padre', 'id_padre');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiantes_id_estudiante', 'id_estudiante');
    }
}
