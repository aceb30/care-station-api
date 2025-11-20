<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Tasks\UserController;
use App\Http\Controllers\Tasks\TaskController;


Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'message' => 'Backend is running!']);
});



Route::middleware('auth:sanctum')->get('/user/{user}/photo', [UserController::class, 'getPhoto']);

Route::patch('/user/{user}/photo-test', [UserController::class, 'updatePhoto']);

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

    // Editar foto de perfil (solo usuario autenticado puede editar su propia foto)
    //Route::patch('/user/{user}/photo', [UserController::class, 'updatePhoto'])->name('user.updatePhoto');
});

// MOVER DENTRO DEL MIDDLEWARE DESPUÉS
Route::post('/readTasks', [TaskController::class, 'readTasks'])->name('readTasks');
Route::post('/readUpcomingTasks', [TaskController::class, 'readUpcomingTasks'])->name('readUpcomingTasks');
Route::post('/createTask', [TaskController::class, 'createTask'])->name('createTask');
Route::post('/deleteTask', [TaskController::class, 'deleteTask'])->name('deleteTask');
Route::post('/updateTask', [TaskController::class, 'updateTask'])->name('updateTask');
