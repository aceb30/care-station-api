<?php


//Todo código será comentado hasta que tenga una mínima ídea de lo que estoy haciendo

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;


// Rutas públicas
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Rutas protegidad que requieren token Sanctum
Route::middleware('auth:sanctum')->group(function () {

    //Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    //Dejamos este para tener de ejemplo
    Route::get('/user', function (Request $request){
        return $request->user();
    })->name('user');
});

