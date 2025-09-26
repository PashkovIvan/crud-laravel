<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Notification routes
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'store']);
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    
    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard/statistics', [DashboardController::class, 'statistics']);
        
        Route::apiResource('tasks', TaskController::class);
        Route::patch('tasks/{task}/completed', [TaskController::class, 'markCompleted']);
        Route::patch('tasks/{task}/in-progress', [TaskController::class, 'markInProgress']);
        Route::patch('tasks/{task}/pending', [TaskController::class, 'markPending']);
        Route::patch('tasks/{task}/assign', [TaskController::class, 'assign']);
    });
});
