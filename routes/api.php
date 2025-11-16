<?php


//Todo código será comentado hasta que tenga una mínima ídea de lo que estoy haciendo

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CareGroupController;
use App\Http\Controllers\Api\PrescriptionController;


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

    
    // Rutas para los Grupos de Cuidado
    Route::apiResource('care-groups', CareGroupController::class);

    // GET /api/care-groups/{careGroup}/tasks
    Route::apiResource('care-groups.tasks', TaskController::class)->scoped();

    // GET /api/patients/{patient}/prescriptions
    Route::apiResource('patients.prescriptions', PrescriptionController::class)->scoped()->shallow();
});

