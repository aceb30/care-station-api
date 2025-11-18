<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Tasks\UserController;


Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'message' => 'Backend is running!']);
});


Route::get('/user/{id}/photo', [UserController::class, 'getPhoto']);

// Rutas públicas
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

//Route::get('/user/{user_id}/photo', [UserController::class, 'getPhoto']);

// Rutas protegidad que requieren token Sanctum
Route::middleware('auth:sanctum')->group(function () {

    //Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    //Dejamos este para tener de ejemplo
    Route::get('/user', function (Request $request){
        return $request->user();
    })->name('user');
});

