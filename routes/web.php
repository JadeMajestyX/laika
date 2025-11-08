<?php

// use App\Http\Controllers\Dashboard;

use App\Http\Controllers\Admin\ActividadController;
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

Route::middleware(EnsureUserHasRole::class.':A')->group(function () {


    // Página principal del panel -> /dashboard/home
    Route::get('/dashboard/home', [DashboardController::class, 'index'])->name('dashboard');
    // Redirección desde /dashboard a /dashboard/home
    Route::get('/dashboard', function(){
        return redirect('/dashboard/home');
    });
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios');
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
    Route::get('/trabajadores/{id}', [TrabajadorController::class, 'show'])->name('trabajadores.show');
    Route::get('/trabajadores/{id}/editar', [TrabajadorController::class, 'edit'])->name('trabajadores.edit');
    Route::put('/trabajadores/{id}', [TrabajadorController::class, 'update'])->name('trabajadores.update');
    Route::delete('/trabajadores/{id}', [TrabajadorController::class, 'destroy'])->name('trabajadores.destroy');

    // registrar nuevo administrador
    Route::get('/trabajadores/crear', [TrabajadorController::class, 'create'])->name('trabajadores.create');
    Route::post('/trabajadores', [TrabajadorController::class, 'store'])->name('trabajadores.store');

    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes');
    Route::get('/inventario', [App\Http\Controllers\InventarioController::class, 'index'])->name('inventario');
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion');

    //Route::get('/buscar', [SearchController::class, 'buscar'])->name('buscar');


    //obtener datos del dashboard
    Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');

    // Capturar cualquier subruta de dashboard para SPA y evitar 404 al refrescar
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
