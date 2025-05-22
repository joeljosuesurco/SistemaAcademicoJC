<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    protected $table = 'documentos';
    protected $primaryKey = 'id_documento';
    public $timestamps = false;

    protected $fillable = [
        'carnet_identidad',
        'certificado_nacimiento',
        'personas_id_persona',
    ];

    // Relaciones

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'personas_id_persona', 'id_persona');
    }
}
