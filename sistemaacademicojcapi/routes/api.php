<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    UserController, EstudianteController, ProfesorController, CursoController,
    MateriaController, GestionController, HorarioController, NotaController,
    SeguimientoController, PersonaController, PersonaRolController, RolController,
    PadreController, DocumentoController, NotificacionController, AuthController
};
use App\Http\Controllers\EstudianteInfoController;
use App\Http\Controllers\InscripcionEstudianteController;
use App\Http\Controllers\InscripcionCursoController;
use App\Http\Controllers\ActualizarEstudianteController;
use App\Http\Controllers\CambioCursoController;
use App\Http\Controllers\CursoProfesorMateriaGestionController;
use App\Http\Controllers\ProfesorInfoController;
use App\Http\Controllers\InscripcionProfesorController;
use App\Http\Controllers\ActualizarProfesorController;
use App\Http\Controllers\PadreInfoController;
use App\Http\Controllers\ActualizarPadreController;
use App\Http\Controllers\AsignarHijosPadreController;
use App\Http\Controllers\AsignarNotificacionController;
use App\Http\Controllers\NotificacionUsuarioController;
use App\Http\Controllers\InscripcionPadreController;
use App\Http\Controllers\ListarNotasController;
use App\Http\Controllers\CrearNotaController;
use App\Http\Controllers\ProfesorAutenticadoController;
use App\Http\Controllers\PadreAutenticadoController;
use App\Http\Controllers\EstudianteAutenticadoController;
use App\Http\Controllers\RegistrarNotaCursoController;
use App\Http\Controllers\ReporteNotasController;
use App\Http\Controllers\NivelEducativoController;
use App\Http\Controllers\ReporteSeguimientoController;

// Rutas públicas
Route::post('login', [AuthController::class, 'login']);
Route::get('/notificaciones/visibles', [NotificacionController::class, 'visibles']); // 👈 colócala FUERA del grupo protegido

// Rutas protegidas con JWT
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('perfil', [AuthController::class, 'me']);
    //
    Route::get('perfil-detallado', [UserController::class, 'perfil']); Route::put('perfil', [UserController::class, 'updatePerfil']);
    //

    // CRUD protegidos
    Route::apiResource('users', UserController::class);
    Route::apiResource('estudiantes', EstudianteController::class);
    Route::apiResource('profesores', ProfesorController::class);
   // CURSITOS
    Route::apiResource('cursos', CursoController::class);
    Route::get('/cursos/{id}/estudiantes', [CursoController::class, 'estudiantesInscritos']);
    Route::get('/cursos/{id}/horario', [CursoController::class, 'horarioPorCurso']);
    Route::get('/cursos/{id}/horario', [CursoController::class, 'horarioActual']);
    Route::put('/cursos/{id}/inhabilitar', [CursoController::class, 'inhabilitar']);
    Route::get('/admin/cursos', [CursoController::class, 'indexAdmin']);
    Route::put('/cursos/{id}/reactivar', [CursoController::class, 'reactivar']);

    Route::get('/nivel-educativo', [NivelEducativoController::class, 'index']);

    Route::apiResource('materias', MateriaController::class);
    Route::put('/materias/{id}/inhabilitar', [MateriaController::class, 'inhabilitar']);
    Route::put('/materias/{id}/reactivar', [MateriaController::class, 'reactivar']);
    Route::get('/materias/{id}', [MateriaController::class, 'show']);
    Route::get('/admin/materias', [MateriaController::class, 'indexAdmin']);

    Route::apiResource('gestiones', GestionController::class);
    Route::apiResource('horarios', HorarioController::class);
    //Route::apiResource('notas', NotaController::class);

    Route::get('/adsolo/cursos-con-materias', [RegistrarNotaCursoController::class, 'cursosConMaterias']);
    Route::get('/adsolo/notas/estudiantes', [RegistrarNotaCursoController::class, 'estudiantesConNotas']);
    Route::post('/adsolo/notas', [RegistrarNotaCursoController::class, 'registrarNota']);


    Route::apiResource('seguimientos', SeguimientoController::class);
    Route::apiResource('personas', PersonaController::class);
    Route::apiResource('persona-roles', PersonaRolController::class);
    Route::apiResource('roles', RolController::class);
    Route::apiResource('padres', PadreController::class);
    Route::apiResource('documentos', DocumentoController::class);

    Route::apiResource('notificaciones', NotificacionController::class);
    //Route::get('/notificaciones/visibles', [NotificacionController::class, 'visibles']);

    Route::apiResource('asignaciones', CursoProfesorMateriaGestionController::class);
    Route::get('/info/estudiantes', [EstudianteInfoController::class, 'index']);
    Route::get('/info/estudiantes/{id}', [EstudianteInfoController::class, 'show']);
    Route::get('/info/profesores', [ProfesorInfoController::class, 'index']);
    Route::get('/info/profesores/{id}', [ProfesorInfoController::class, 'show']);

    Route::put('/profesores/{id}/inhabilitar', [ProfesorController::class, 'inhabilitar']);
    Route::put('/profesores/{id}/reactivar', [ProfesorController::class, 'reactivar']);
    Route::get('/admin/profesores-completo', [ProfesorController::class, 'todos']);

    Route::post('/inscribir-estudiante', [InscripcionEstudianteController::class, 'store']);
    Route::post('/inscribir-curso', [InscripcionCursoController::class, 'store']);
    Route::put('/actualizar-estudiante/{id}', [ActualizarEstudianteController::class, 'update']);
    Route::post('/actualizar-estudiante/{id}', [ActualizarEstudianteController::class, 'update']);

    Route::post('/cambio-curso', [CambioCursoController::class, 'cambiar']);
    Route::post('/revertir-cambio-curso', [CambioCursoController::class, 'revertir']);
    Route::get('/asignaciones', [CursoProfesorMateriaGestionController::class, 'index']);

    Route::post('/asignaciones', [CursoProfesorMateriaGestionController::class, 'store']);
    Route::get('/asignaciones/{id}', [CursoProfesorMateriaGestionController::class, 'show']);
    Route::put('/asignaciones/{id}', [CursoProfesorMateriaGestionController::class, 'update']);
    Route::delete('/asignaciones/{id}', [CursoProfesorMateriaGestionController::class, 'destroy']);
    Route::post('/registro-profesor', [InscripcionProfesorController::class, 'store']);
    Route::put('/actualizar-profesor/{id}', [ActualizarProfesorController::class, 'update']);
    Route::get('/info/padres', [PadreInfoController::class, 'index']);
    Route::put('/actualizar-padre/{id}', [ActualizarPadreController::class, 'update']);
    // Rutas para asignar hijos a un padre
    Route::post('/padres/{padre}/asignar-hijos', [AsignarHijosPadreController::class, 'store']);
    Route::get('/padres/{padre}/hijos-disponibles', [AsignarHijosPadreController::class, 'disponibles']);
    Route::delete('/padres/{padre}/quitar-hijo/{estudiante}', [AsignarHijosPadreController::class, 'destroy']);
    Route::get('/padres/{id}/hijos', [AsignarHijosPadreController::class, 'hijosAsignados']);

    // Notificaciones asignadas al usuario autenticado
    Route::get('/mis-notificaciones', [NotificacionUsuarioController::class, 'index']);

    // Marcar como leída una notificación
    Route::put('/notificaciones/{id}/marcar-leido', [NotificacionUsuarioController::class, 'marcarLeido']);

    // Asignar una notificación a uno o varios usuarios
    Route::post('/asignar-notificacion', [NotificacionUsuarioController::class, 'store']);

    Route::get('/notificacion-usuario/{userId}/no-leidas', [NotificacionUsuarioController::class, 'notificacionesNoLeidas']);

    Route::get('/notificacion-usuario/{userId}/ultimos', [NotificacionUsuarioController::class, 'ultimos']);


    Route::get('/usuarios-por-grupo/{grupo}', [NotificacionUsuarioController::class, 'usuariosPorGrupo']);

    Route::post('/registro-padre', [InscripcionPadreController::class, 'store']);

    //para rendimiento

    Route::get('/estudiantes/{estudiante}/seguimientos', [SeguimientoController::class, 'indexByEstudiante']);

     // Ya deberías tener algo así para el resource
    Route::apiResource('seguimiento', SeguimientoController::class)
     ->except(['create','edit']);

    // Ruta para el formulario de creación (create-form)
    Route::get('seguimiento/create-form/{estudianteId}',
    [SeguimientoController::class, 'createForm']);

    //notas

    Route::get('/notas', [ListarNotasController::class, 'index']);
    Route::get('/materias/nivel/{nivel_id}', [MateriaController::class, 'porNivel']);
    Route::post('/notas', [CrearNotaController::class, 'store']);
    Route::get('/notas/estudiante/{id}/gestion/{gestion}/periodo/{periodo}', [ListarNotasController::class, 'notasPorEstudiante']);

    //profe autenticado
    Route::middleware('auth:api')->prefix('profesor-auth')->group(function () {
    Route::get('/cursos', [ProfesorAutenticadoController::class, 'cursos']);
    Route::get('/horarios', [ProfesorAutenticadoController::class, 'horarios']);
    Route::get('/notas', [ProfesorAutenticadoController::class, 'notas']);
    Route::get('/crear-notas', [ProfesorAutenticadoController::class, 'estudiantesParaNota']);
    Route::get('/estudiantes-seguimiento', [ProfesorAutenticadoController::class, 'estudiantesConSeguimiento']);
    Route::get('/cursos-seguimiento', [ProfesorAutenticadoController::class, 'cursosSeguimiento']);
    Route::post('/seguimientos', [ProfesorAutenticadoController::class, 'registrarSeguimiento']); // ← FALTA ESTA
    Route::get('/estudiantes-curso/{id}', [ProfesorAutenticadoController::class, 'estudiantesPorCurso']);

    });
    //papa autengticado
    Route::middleware('auth:api')->prefix('padre-auth')->group(function () {
    Route::get('/hijos', [PadreAutenticadoController::class, 'hijos']);
    Route::get('/horario/{idEstudiante}', [PadreAutenticadoController::class, 'horario']);
    Route::get('/notas/{id}', [PadreAutenticadoController::class, 'notasPorEstudiante']);
    Route::get('/seguimientos/{idEstudiante}', [PadreAutenticadoController::class, 'seguimientos']);

    });

    //estudiante autenticado
    Route::middleware('auth:api')->prefix('estudiante-auth')->group(function () {
    Route::get('/datos', [EstudianteAutenticadoController::class, 'datosEstudianteAutent']);
    Route::get('/horario', [EstudianteAutenticadoController::class, 'horario']);
    Route::get('/notas', [EstudianteAutenticadoController::class, 'notas']);
    Route::get('/seguimientos', [EstudianteAutenticadoController::class, 'seguimientos']);
    });

    //reportes
    Route::get('reportes/curso/{id}/estudiantes', [CursoController::class, 'reporteEstudiantesPDF']);
    Route::get('reportes/boleta/{estudianteId}/periodo/{periodoNumero}', [ReporteNotasController::class, 'boletaPDF']);
    Route::middleware('auth:api')->post('reportes/boleta', [ReporteNotasController::class, 'boletaDesdeFrontend']);
    Route::middleware('auth:api')->post('reportes/notas-profesor', [ReporteNotasController::class, 'notasProfesorPDF']);
    Route::post('reportes/estudiantes-curso', [ReporteNotasController::class, 'estudiantesCursoPDF']);
    Route::post('/actualizar-profesor/{id}', [ActualizarProfesorController::class, 'update']);
    Route::put('/actualizar-profesor/{id}', [ActualizarProfesorController::class, 'update']);
    Route::post('/actualizar-profesor/{id}', [ActualizarProfesorController::class, 'update']);
    Route::put('/actualizar-padre/{id}', [ActualizarPadreController::class, 'update']);
    Route::post('/actualizar-padre/{id}', [ActualizarPadreController::class, 'update']);

    //reportes estadisticos
    Route::prefix('reportes/rendimiento')->controller(ReporteSeguimientoController::class)->group(function () {
    Route::get('/materias', 'rendimientoPorMateria');
    Route::get('/cursos', 'rendimientoPorCurso');
    Route::get('/estudiantes/{curso_id}/{materia_id}', 'rendimientoPorEstudiantes');
    });

});
