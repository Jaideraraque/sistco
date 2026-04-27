<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SistcoController;

// ── Rutas públicas (sin autenticación) ──
Route::get('/soporte-publico',  [SistcoController::class, 'soportePublico'])->name('soporte.publico');
Route::post('/soporte-publico', [SistcoController::class, 'enviarSoportePublico'])->name('soporte.publico.enviar');

// ── Ruta raíz ──
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ── Dashboard y Análisis — Requieren rol asignado ──
Route::middleware(['auth', 'verified', 'rol:gerente,administrativo,admin_sistema'])->group(function () {
    Route::get('/dashboard', [SistcoController::class, 'dashboard'])->name('dashboard');
    Route::get('/analisis',  [SistcoController::class, 'analisis'])->name('analisis');
});

// ── Soporte — Solo autenticado, sin importar rol ──
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/soporte',  [SistcoController::class, 'soporte'])->name('soporte');
    Route::post('/soporte', [SistcoController::class, 'enviarSoporte'])->name('soporte.enviar');
});

// ── Clasificación y Asistente — Todos los roles ──
Route::middleware(['auth', 'verified', 'rol:gerente,administrativo,admin_sistema'])->group(function () {
    Route::get('/clasificacion',       [SistcoController::class, 'clasificacion'])->name('clasificacion');
    Route::get('/asistente',           [SistcoController::class, 'asistente'])->name('asistente');
    Route::post('/asistente/consulta', [SistcoController::class, 'asistenteConsulta'])->name('asistente.consulta');
});

// ── Carga de datos — Administrativo y Admin Sistema ──
Route::middleware(['auth', 'verified', 'rol:administrativo,admin_sistema'])->group(function () {
    Route::get('/carga-datos', [SistcoController::class, 'cargaDatos'])->name('carga-datos');
});

// ── Módulos estratégicos — Gerente y Admin Sistema ──
Route::middleware(['auth', 'verified', 'rol:gerente,admin_sistema'])->group(function () {
    Route::get('/ingresos',    [SistcoController::class, 'ingresos'])->name('ingresos');
    Route::get('/segmentacion',[SistcoController::class, 'segmentacion'])->name('segmentacion');
    Route::get('/reportes',    [SistcoController::class, 'reportes'])->name('reportes');
});

// ── Administración — Solo Admin Sistema ──
Route::middleware(['auth', 'verified', 'rol:admin_sistema'])->group(function () {
    Route::get('/administracion',          [SistcoController::class, 'administracion'])->name('administracion');
    Route::post('/usuarios',               [SistcoController::class, 'crearUsuario'])->name('usuarios.crear');
    Route::put('/usuarios/{id}/rol',       [SistcoController::class, 'cambiarRol'])->name('usuarios.rol');
    Route::put('/usuarios/{id}/toggle',    [SistcoController::class, 'toggleUsuario'])->name('usuarios.toggle');
    Route::delete('/usuarios/{id}',        [SistcoController::class, 'eliminarUsuario'])->name('usuarios.eliminar');
    Route::put('/usuarios/{id}/editar',    [SistcoController::class, 'editarUsuario'])->name('usuarios.editar');
    Route::put('/perfil/actualizar',       [SistcoController::class, 'actualizarPerfil'])->name('perfil.actualizar');
    Route::post('/perfil/foto',            [SistcoController::class, 'actualizarFoto'])->name('perfil.foto');
    Route::put('/perfil/foto',             [SistcoController::class, 'actualizarFoto']);
    Route::post('/carga-datos/procesar',   [SistcoController::class, 'procesarCarga'])->name('carga-datos.procesar');
    Route::get('/reportes/pdf',            [SistcoController::class, 'exportarPDF'])->name('reportes.pdf');
    Route::get('/reportes/excel',          [SistcoController::class, 'exportarExcel'])->name('reportes.excel');
});