<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;


use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/assign-role', [AdminController::class, 'assignRole']);
});
Route::middleware(['auth:sanctum', 'role:project_manager'])->group(function () {
    Route::apiResource('projects', ProjectController::class)->except(['show', 'index']);
});
Route::middleware(['auth:sanctum', 'role:team_lead'])->group(function () {
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
});
Route::middleware(['auth:sanctum', 'role:developer'])->group(function () {
    Route::put('/tasks/{task}', [TaskController::class, 'update']); 
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});