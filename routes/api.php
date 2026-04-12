<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AboutController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\CvController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GraphicDesignController;
use Illuminate\Http\Request; 

// ==================== ROUTES PUBLIQUES ====================

// Login
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Routes publiques
Route::get('/graphic-designs', [GraphicDesignController::class, 'index']);
Route::get('/graphic-designs/categories', [GraphicDesignController::class, 'categories']);
Route::get('/graphic-designs/{id}', [GraphicDesignController::class, 'show']);


// Infos perso
Route::get('/about', [AboutController::class, 'index']);

// Projets (lecture seule)
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);

// Commentaires (création uniquement)
Route::post('/projects/{projectId}/comments', [CommentController::class, 'store']);
Route::get('/projects/{projectId}/comments', [CommentController::class, 'getProjectComments']);

// CV
Route::get('/cv/download', [CvController::class, 'download']);
Route::get('/cv/check', [CvController::class, 'check']);

// ==================== ROUTES PROTÉGÉES (Admin) ====================

Route::middleware('auth:sanctum')->group(function () {
    
    // CRUD Projets
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']); // POST pour multipart/form-data
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    
    // Gestion Commentaires
    Route::get('/admin/comments', [CommentController::class, 'index']);
    Route::post('/admin/comments/{id}/approve', [CommentController::class, 'approve']);
    Route::delete('/admin/comments/{id}', [CommentController::class, 'destroy']);
    
    // Gestion CV
    Route::post('/admin/cv/upload', [CvController::class, 'upload']);
    Route::get('/admin/cv/history', [CvController::class, 'index']);
    
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

     // Graphic Designs
    Route::post('/graphic-designs', [GraphicDesignController::class, 'store']);
    Route::put('/graphic-designs/{id}', [GraphicDesignController::class, 'update']);
    Route::delete('/graphic-designs/{id}', [GraphicDesignController::class, 'destroy']);
});

