<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    UserController, EstudianteController, ProfesorController, CursoController,
    MateriaController, GestionController, HorarioController, NotaController,
    SeguimientoController, PersonaController, PersonaRolController, RolController,
    PadreController, DocumentoController, NotificacionController, AuthController
};

// Rutas públicas
Route::post('login', [AuthController::class, 'login']);

// Rutas protegidas con JWT
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('perfil', [AuthController::class, 'me']);

    // CRUD protegidos
    Route::apiResource('users', UserController::class);
    Route::apiResource('estudiantes', EstudianteController::class);
    Route::apiResource('profesores', ProfesorController::class);
    Route::apiResource('cursos', CursoController::class);
    Route::apiResource('materias', MateriaController::class);
    Route::apiResource('gestiones', GestionController::class);
    Route::apiResource('horarios', HorarioController::class);
    Route::apiResource('notas', NotaController::class);
    Route::apiResource('seguimientos', SeguimientoController::class);
    Route::apiResource('personas', PersonaController::class);
    Route::apiResource('persona-roles', PersonaRolController::class);
    Route::apiResource('roles', RolController::class);
    Route::apiResource('padres', PadreController::class);
    Route::apiResource('documentos', DocumentoController::class);
    Route::apiResource('notificaciones', NotificacionController::class);
});
