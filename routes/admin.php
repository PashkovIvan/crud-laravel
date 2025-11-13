<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/dashboard/statistics', [DashboardController::class, 'statistics'])->name('admin.dashboard.statistics');
    
    Route::apiResource('tasks', TaskController::class)->names([
        'index' => 'admin.tasks.index',
        'store' => 'admin.tasks.store',
        'show' => 'admin.tasks.show',
        'update' => 'admin.tasks.update',
        'destroy' => 'admin.tasks.destroy',
    ]);
    Route::patch('tasks/{task}/completed', [TaskController::class, 'markCompleted'])->name('admin.tasks.completed');
    Route::patch('tasks/{task}/in-progress', [TaskController::class, 'markInProgress'])->name('admin.tasks.in-progress');
    Route::patch('tasks/{task}/pending', [TaskController::class, 'markPending'])->name('admin.tasks.pending');
    Route::patch('tasks/{task}/assign', [TaskController::class, 'assign'])->name('admin.tasks.assign');
});
