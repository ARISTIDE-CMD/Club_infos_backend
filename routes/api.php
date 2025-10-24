<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ProjectMessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes for authentication
Route::post('/login', [AuthController::class, 'login']);

// Routes that require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Admin routes
    Route::post('/admin/students', [AdminController::class, 'store']);
    Route::delete('/admin/students/{id}', [AdminController::class, 'destroy']);
    Route::put('/admin/students/{id}', [AdminController::class, 'update']);

    // Student and Admin routes
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}', [StudentController::class, 'show']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Project routes
    Route::get('/projects', [ProjectController::class, 'index']);
     Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::get('/results', [SubmissionController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::patch('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

    // Submission routes (for students)
    Route::post('/submissions', [SubmissionController::class, 'store']); // New route for student project submissions
    Route::get('/download/{filename}', [SubmissionController::class, 'downloadFile']);
     Route::post('/submissions/{submission}/evaluate', [EvaluationController::class, 'storeOrUpdate']);

     //Appi pour chater
        
    Route::post('/projects/{project}/chat', [ChatController::class, 'sendMessage']);
     //.....
     Route::post('/projects/messages', [ProjectMessageController::class, 'store']);
    });
Route::get('/download/{path}', [SubmissionController::class, 'downloadFile'])
->where('path', '.*') // Ceci dit à Laravel d'accepter TOUS les caractères (y compris les slashes) dans le paramètre {path}
    ->name('download.submission');
    Route::get('/students/{id}', [StudentController::class, 'show']);
    Route::get('/projects/{project}/messages', [ProjectMessageController::class, 'show']);
    Route::get('/admin/messages', [ProjectMessageController::class, 'index']);
    //  Route::get('/projects/{project}/chat', [ChatController::class, 'showProjectChat']);
