<?php

// use App\Http\Controllers\Dashboard;

use App\Http\Controllers\Admin\ActividadController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\VetDashboardController;
use App\Http\Controllers\GroomerDashboardController;
use App\Http\Controllers\SearchController;




Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::middleware(EnsureUserHasRole::class.':V')->group(function () {
    Route::get('/vet-dashboard', [VetDashboardController::class, 'index'])->name('vet.dashboard');
    
    Route::get('/vet-dashboard/data', [VetDashboardController::class, 'getDashboardData'])->name('vet.dashboard.data');
    
    // Capturar subrutas (cualquiera)
    Route::get('/vet-dashboard/{any}', [VetDashboardController::class, 'index'])->where('any', '.*');
});

// Dashboard Groomer (rol G)
Route::middleware(EnsureUserHasRole::class.':G')->group(function () {
    Route::get('/groomer-dashboard', [GroomerDashboardController::class, 'index'])->name('groomer.dashboard');
    Route::get('/groomer-dashboard/data', [GroomerDashboardController::class, 'getDashboardData'])->name('groomer.dashboard.data');
    Route::get('/groomer-dashboard/{any}', [GroomerDashboardController::class, 'index'])->where('any', '.*');
});

Route::middleware(EnsureUserHasRole::class.':A')->group(function () {


    // Página principal del panel -> /dashboard/home
    Route::get('/dashboard/home', [DashboardController::class, 'index'])->name('dashboard');
    // Redirección desde /dashboard a /dashboard/home
    Route::get('/dashboard', function(){
        return redirect('/dashboard/home');
    });
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios');
    // Importante: declarar la ruta JSON ANTES de /usuarios/{id} para evitar que 'json' coincida como {id}
    Route::get('/usuarios/json', [UserController::class, 'getUsuariosJson'])->name('usuarios.json');
    Route::get('/usuarios/{id}', [UserController::class, 'show'])->name('usuarios.show');
    Route::get('/usuarios/{id}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');
    Route::get('/citas', [App\Http\Controllers\CitaController::class, 'index'])->name('citas');
    // Endpoint JSON paginado para citas (hoy por defecto, soporte búsqueda en pasadas)
    Route::get('/citas/json', [App\Http\Controllers\CitaController::class, 'getCitasJson'])->name('citas.json');

    Route::get('/mascotas', [MascotaController::class, 'index'])->name('mascotas');
    // Primero la ruta JSON para que no la capture /mascotas/{id}
    Route::get('/mascotas/json', [MascotaController::class, 'getAllMascotas'])->name('mascotas.json');
    Route::get('/mascotas/{id}', [MascotaController::class, 'show'])->name('mascotas.show');
    Route::get('/mascotas/{id}/editar', [MascotaController::class, 'edit'])->name('mascotas.edit');
    Route::put('/mascotas/{id}', [MascotaController::class, 'update'])->name('mascotas.update');
    Route::delete('/mascotas/{id}', [MascotaController::class, 'destroy'])->name('mascotas.destroy');

    Route::get('/trabajadores', [TrabajadorController::class, 'index'])->name('trabajadores');
    // JSON antes de rutas dinámicas
    Route::get('/trabajadores/json', [TrabajadorController::class, 'getTrabajadoresJson'])->name('trabajadores.json');
    Route::get('/trabajadores/{id}', [TrabajadorController::class, 'show'])->name('trabajadores.show');
    Route::get('/trabajadores/{id}/editar', [TrabajadorController::class, 'edit'])->name('trabajadores.edit');
    Route::put('/trabajadores/{id}', [TrabajadorController::class, 'update'])->name('trabajadores.update');
    Route::delete('/trabajadores/{id}', [TrabajadorController::class, 'destroy'])->name('trabajadores.destroy');

    // registrar nuevo administrador
    Route::get('/trabajadores/crear', [TrabajadorController::class, 'create'])->name('trabajadores.create');
    Route::post('/trabajadores', [TrabajadorController::class, 'store'])->name('trabajadores.store');

    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes');
    Route::get('/reportes/data', [ReporteController::class, 'data'])->name('reportes.data');
    Route::get('/reportes/citas/export', [ReporteController::class, 'exportCitas'])->name('reportes.citas.export');
    Route::get('/reportes/citas/export/xlsx', [ReporteController::class, 'exportCitasXlsx'])->name('reportes.citas.export.xlsx');
    Route::get('/reportes/citas/export/pdf', [ReporteController::class, 'exportCitasPdf'])->name('reportes.citas.export.pdf');
    Route::get('/inventario', [App\Http\Controllers\InventarioController::class, 'index'])->name('inventario');
    // Listado de clínicas (selección)
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion');
    // Vista de configuración para una clínica específica
    Route::get('/configuracion/clinica/{clinica}', [ConfiguracionController::class, 'editarClinica'])->name('configuracion.clinica');
    // Crear nueva clínica
    Route::post('/configuracion/clinica', [ConfiguracionController::class, 'storeClinica'])->name('configuracion.clinica.store');
    // Agregar servicios predefinidos a una clínica
    Route::post('/configuracion/clinica/{clinica}/servicios', [ConfiguracionController::class, 'storeServiciosClinica'])->name('configuracion.clinica.servicios.store');
    // Listar servicios de una clínica (JSON)
    Route::get('/configuracion/clinica/{clinica}/servicios', [ConfiguracionController::class, 'serviciosClinica'])->name('configuracion.clinica.servicios.index');
    // Actualizar un servicio (precio, tiempo, nombre)
    Route::patch('/configuracion/servicio/{servicio}', [ConfiguracionController::class, 'updateServicio'])->name('configuracion.servicio.update');
    // Eliminar un servicio de una clínica
    Route::delete('/configuracion/servicio/{servicio}', [ConfiguracionController::class, 'eliminarServicio'])->name('configuracion.servicio.destroy');
    // Actualizar información básica de clínica
    Route::put('/configuracion/clinica/{clinica}', [ConfiguracionController::class, 'updateClinicaInfo'])->name('configuracion.clinica.update');
    // Actualizar horarios (requiere clinica_id en el formulario)
    Route::put('/configuracion/horarios', [ConfiguracionController::class, 'updateHorarios'])->name('configuracion.horarios.update');
    // Crear trabajador para clínica
    Route::post('/configuracion/clinica/{clinica}/trabajadores', [ConfiguracionController::class, 'storeTrabajadorClinica'])->name('configuracion.clinica.trabajadores.store');
    Route::post('/configuracion/clinica/{clinica}/trabajadores/asignar', [ConfiguracionController::class, 'asignarTrabajadorClinica'])->name('configuracion.clinica.trabajadores.asignar');
    // Remover trabajador (desasignar de clínica)
    Route::delete('/configuracion/clinica/{clinica}/trabajadores/{user}/remover', [ConfiguracionController::class, 'removerTrabajadorClinica'])->name('configuracion.clinica.trabajadores.remover');

    // Alias con prefijo /clinicas para URLs amigables
    Route::get('/clinicas', [ConfiguracionController::class, 'index'])->name('clinicas');
    Route::get('/clinicas/clinica/{clinica}', [ConfiguracionController::class, 'editarClinica'])->name('clinicas.clinica');
    Route::post('/clinicas/clinica', [ConfiguracionController::class, 'storeClinica'])->name('clinicas.clinica.store');
    // Servicios de clínica (listar y agregar)
    Route::get('/clinicas/clinica/{clinica}/servicios', [ConfiguracionController::class, 'serviciosClinica'])->name('clinicas.clinica.servicios.index');
    Route::post('/clinicas/clinica/{clinica}/servicios', [ConfiguracionController::class, 'storeServiciosClinica'])->name('clinicas.clinica.servicios.store');
    // Actualizaciones
    Route::put('/clinicas/clinica/{clinica}', [ConfiguracionController::class, 'updateClinicaInfo'])->name('clinicas.clinica.update');
    Route::put('/clinicas/horarios', [ConfiguracionController::class, 'updateHorarios'])->name('clinicas.horarios.update');
    // Trabajadores
    Route::post('/clinicas/clinica/{clinica}/trabajadores', [ConfiguracionController::class, 'storeTrabajadorClinica'])->name('clinicas.clinica.trabajadores.store');
    Route::post('/clinicas/clinica/{clinica}/trabajadores/asignar', [ConfiguracionController::class, 'asignarTrabajadorClinica'])->name('clinicas.clinica.trabajadores.asignar');
    Route::delete('/clinicas/clinica/{clinica}/trabajadores/{user}/remover', [ConfiguracionController::class, 'removerTrabajadorClinica'])->name('clinicas.clinica.trabajadores.remover');
    // Servicio individual (editar/eliminar)
    Route::patch('/clinicas/servicio/{servicio}', [ConfiguracionController::class, 'updateServicio'])->name('clinicas.servicio.update');
    Route::delete('/clinicas/servicio/{servicio}', [ConfiguracionController::class, 'eliminarServicio'])->name('clinicas.servicio.destroy');

    // Página de prueba para enviar notificaciones manuales
    Route::get('/test/noti', [\App\Http\Controllers\TestNotificationController::class, 'index'])->name('test.noti');
    Route::post('/test/noti', [\App\Http\Controllers\TestNotificationController::class, 'send'])->name('test.noti.send');

    //Route::get('/buscar', [SearchController::class, 'buscar'])->name('buscar');


    //obtener datos del dashboard
    Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');

    // Capturar cualquier subruta de dashboard para SPA y evitar 404 al refrescar,
    // EXCEPTO rutas específicas como /dashboard/data
    Route::get('/dashboard/{any}', [DashboardController::class, 'index'])
        ->where('any', '^(?!data$).+');


    //actividad
    Route::get('/actividades', [ActividadController::class, 'index'])->name('actividades');

});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/olvidar-password', function () {
    return view('auth.forgot-password');
})->name('password.request');



Route::get('/login', function () {
    return view('login');
})->name('login');



//ruta para la vista de agendar citas
Route::get('/agendar-cita', CitaController::class . '@index')->name('agendar.cita');



//politica de privacidad
Route::get('/politica-privacidad', function () {
    return view('politica-privacidad');
})->name('politica-privacidad');

//terminos y condiciones
Route::get('/terms', function(){
    return view('terms');
})->name('terms');



//eliminar cuenta
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


//ver perfil
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');



require __DIR__.'/auth.php';
