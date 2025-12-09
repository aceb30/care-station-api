<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Medicine\ExamController;
use App\Http\Controllers\Tasks\UserController;
use App\Http\Controllers\Tasks\TaskController;
use App\Http\Controllers\CareGroupController;
use App\Http\Controllers\PatientController;

use App\Http\Controllers\Medicine\MedicationController;

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'message' => 'Backend is running!']);
});



//Route::middleware('auth:sanctum')->get('/user/{user}/photo', [UserController::class, 'getPhoto']);

//Route::patch('/user/{user}/photo-test', [UserController::class, 'updatePhoto']);

// Rutas públicas
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

//Route::get('/user/{user_id}/photo', [UserController::class, 'getPhoto']);

// Rutas protegidad que requieren token Sanctum
Route::middleware('auth:sanctum')->group(function () {

    //Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/user', function (Request $request){
        return $request->user();
    })->name('user');

    // Editar foto de perfil (solo usuario autenticado puede editar su propia foto)
    //Route::patch('/user/{user}/photo', [UserController::class, 'updatePhoto'])->name('user.updatePhoto');

    Route::apiResource('patients', PatientController::class);

    Route::get('/patients', [PatientController::class, 'index']);

    // Custom endpoints for patients
    Route::get('/patients/by-group/{care_group_id}', [PatientController::class, 'showByGroup']);

    
    Route::get('/my-groups', [CareGroupController::class, 'getMyGroups']);

    Route::post('/care-groups', [CareGroupController::class, 'store']);

    Route::get('/care-groups/get-members/{care_group_id}', [CareGroupController::class, 'getMembers']);

    // '/exams' y lo que le sigue. Si la vista es otra, cambiar el nombre.
    Route::apiResource('exams', ExamController::class);

    // '/medications' y lo que le sigue. Si la vista es otra, cambiar el nombre.
    Route::apiResource('medications', MedicationController::class);

    // '/prescriptions' y lo que le sigue. Si la vista es otra, cambiar el nombre.
    Route::apiResource('prescriptions', PrescriptionController::class);

    // Restful endpoints for tasks
    Route::apiResource('tasks', TaskController::class);

    // Custom endpoints for tasks
    Route::get('/tasks/by-group/{care_group_id}', [TaskController::class, 'indexByGroup']);
    Route::get('/tasks/upcoming-by-group/{care_group_id}', [TaskController::class, 'upcomingByGroup']);

    // Ruta para generar invitación (Solo Admin)
    Route::post('/care-groups/{id}/invitation', [CareGroupController::class, 'generateInvitation']);

    // Ruta para unirse por código
    Route::post('/care-groups/join-by-code', [CareGroupController::class, 'joinByCode']);
    
    // Ruta para unirse al grupo (Cualquier usuario)
    Route::post('/join-group', [CareGroupController::class, 'joinByCode']);

    Route::get('/medications', [MedicationController::class, 'index']); 
    Route::post('/medications', [MedicationController::class, 'store']);
    Route::get('/medications/{id}', [MedicationController::class, 'show']);
    Route::put('/medications/{id}', [MedicationController::class, 'update']);
    Route::delete('/medications/{id}', [MedicationController::class, 'destroy']);

});
