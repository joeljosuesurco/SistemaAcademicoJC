<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nota extends Model
{
    protected $table = 'notas';
    protected $primaryKey = 'id_nota';
    public $timestamps = false;

    protected $fillable = [
        'periodo',
        'numero_periodo',
        'nota_final',
        'observacion',
        'estudiantes_id_estudiante',
        'materias_id_materia',
        'cursos_id_curso',
        'gestiones_id_gestion',
    ];

    /**
     * Casteos de atributos
     * 'nota_final' siempre como entero
     */
    protected $casts = [
        'nota_final' => 'integer',
    ];

    // Se elimina la propiedad $appends para no exponer nota_final_calculada
    // protected $appends = ['nota_final_calculada'];

    // Relaciones
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiantes_id_estudiante', 'id_estudiante');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materias_id_materia', 'id_materia');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cursos_id_curso', 'id_curso');
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(Gestion::class, 'gestiones_id_gestion', 'id_gestion');
    }

    public function dimensiones(): HasMany
    {
        return $this->hasMany(DimensionNota::class, 'notas_id_nota', 'id_nota');
    }

    // Si existía el accessor, puedes comentarlo o eliminarlo:
    /*
    public function getNotaFinalCalculadaAttribute(): int
    {
        return $this->dimensiones->sum(fn($dim) => (int) $dim->valor_obtenido);
    }
    */
}
