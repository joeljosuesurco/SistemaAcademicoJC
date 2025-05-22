<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NivelEducativo extends Model
{
    protected $table = 'nivel_educativos';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'codigo',
    ];

    // Relaciones

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'nivel_educativo_id');
    }

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class, 'nivel_educativo_id');
    }
}
