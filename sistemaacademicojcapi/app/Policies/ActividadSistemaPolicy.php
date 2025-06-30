<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ActividadSistema;

class ActividadSistemaPolicy
{
    /**
     * Determina si el usuario puede ver los logs del sistema
     */
    public function viewAny(User $user): bool
    {
        return optional($user->persona_rol?->rol)->nombre === 'administrativo';
    }

    /**
     * Para registrar logs manuales si se desea (no obligatorio)
     */
    public function create(User $user): bool
    {
        return optional($user->persona_rol?->rol)->nombre === 'administrativo';
    }
}
