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
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\StudentSearchController;
use App\Http\Controllers\ListController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
 Route::get('/superadmin/categories', [SuperAdminController::class, 'indexCategories']);
Route::get('/superadmin/dashboard', [SuperAdminController::class, 'dashboard']);
Route::get('/list', [ListController::class, 'getLimitedList']);
Route::get('/analytics/searches', function () {
    return app(\App\Services\TypesenseService::class)->getAnalytics();
});

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {


    // ----- Super Admin -----
    // Route::middleware('superadmin')->group(function () {

        Route::post('/superadmin/categories', [SuperAdminController::class, 'storeCategory']);
       
        Route::get('/superadmin/admins', [SuperAdminController::class, 'indexAdmins']);
        Route::post('/superadmin/admins', [SuperAdminController::class, 'createAdmin']);
        Route::put('/superadmin/admins/{id}', [SuperAdminController::class, 'updateAdmin']);
        Route::delete('/superadmin/admins/{id}', [SuperAdminController::class, 'deleteAdmin']);


    // });

    // ----- Admin -----
    // Route::middleware('admin')->group(function () {
        // Gestion des étudiants
        Route::post('/admin/students', [AdminController::class, 'store']);
        Route::put('/admin/students/{id}', [AdminController::class, 'update']);
        Route::delete('/admin/students/{id}', [AdminController::class, 'destroy']);
        Route::get('search/students', [StudentSearchController::class, 'search']);
        // Projets créés par cet admin / ses étudiants
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::patch('/projects/{id}', [ProjectController::class, 'update']);
        Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

        // Évaluations : l’admin n’évalue que ses étudiants
        Route::post('/submissions/{submission}/evaluate', [EvaluationController::class, 'storeOrUpdate']);

        // Messages de projets dont il est responsable
    // });

    // ----- Student -----
    // Consultation


    Route::get('/students/{id}', [StudentController::class, 'show']);
 Route::get('/students', [StudentController::class, 'index']);
    // Soumissions de projets
    Route::post('/submissions', [SubmissionController::class, 'store']);
    Route::get('/results', [SubmissionController::class, 'index']);
    Route::get('/download/{path}', [SubmissionController::class, 'downloadFile'])
        ->where('path', '.*')
        ->name('download.submission');

    // Projets
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);

    // Project Messages / Chat
    Route::post('/projects/{project}/chat', [ChatController::class, 'sendMessage']);
    Route::post('/projects/messages', [ProjectMessageController::class, 'store']);

    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/projects/{project}/messages', [ProjectMessageController::class, 'show']);
    Route::get('/admin/messages', [ProjectMessageController::class, 'index']);
    Route::get('/students/index-typesense', [StudentController::class, 'indexStudentsInTypesense']);
});
  
