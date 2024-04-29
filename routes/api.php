<?php

use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\patientController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\Cors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/test', function (Request $request) {
    // echo 'Hello World!';
    return response()->json(['message' => 'Hello World!']);
});

// pacientes

Route::get(
    '/patients',
    [patientController::class, 'index']
);

Route::get('/patients/{id}', [patientController::class, 'show']);


Route::post('/patients', [patientController::class, 'store']);

Route::put('/patients/{id}', [patientController::class, 'update']);

Route::patch('/patients/{id}', [patientController::class, 'updatePartial']);

Route::delete('/patients/{id}', [patientController::class, 'destroy']);

// doctores

Route::get(
    '/doctors',
    [DoctorController::class, 'index']
);

Route::get('/doctors/{id}', [DoctorController::class, 'show']);


Route::post('/doctors', [DoctorController::class, 'store']);

Route::put('/doctors/{id}', [DoctorController::class, 'update']);

Route::patch('/doctors/{id}', [DoctorController::class, 'updatePartial']);

Route::delete('/doctors/{id}', [DoctorController::class, 'destroy']);

// Route::get('/doctors', function (Request $request) {
//     // echo 'Hello World!';
//     return response()->json(['doctors' => 'All World!']);
// });


Route::get(
    '/users',
    [UserController::class, 'users']
);

Route::get(
    '/users/{id}',
    [UserController::class, 'show']
);

Route::middleware('auth:sanctum')->patch(
    '/users/{id}',
    [UserController::class, 'updatePartial']
);

Route::get(
    '/roles',
    [UserController::class, 'getRoles']
)->middleware('auth:sanctum');


// Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'users']);


// auth

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post('refresh', [UserController::class, 'refreshToken']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
