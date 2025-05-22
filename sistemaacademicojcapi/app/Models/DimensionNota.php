<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DimensionNota extends Model
{
    protected $table = 'dimensiones_nota';
    protected $primaryKey = 'id_dimension_nota';
    public $timestamps = false;

    protected $fillable = [
        'nombre_dimension',
        'porcentaje',
        'valor_obtenido',
        'notas_id_nota',
    ];

    /** Asegura que lleguen como enteros */
    protected $casts = [
        'porcentaje'     => 'integer',
        'valor_obtenido' => 'integer',
    ];

    public function nota(): BelongsTo
    {
        return $this->belongsTo(Nota::class, 'notas_id_nota', 'id_nota');
    }
}

