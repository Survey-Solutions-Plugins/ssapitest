<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\InterviewController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes - Survey Solutions API v1
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    
    // Authentication routes (public)
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        
        // Workspaces routes
        Route::get('/workspaces', [WorkspaceController::class, 'index']);
        Route::post('/workspaces', [WorkspaceController::class, 'store']);
        Route::get('/workspaces/{name}', [WorkspaceController::class, 'show']);
        Route::patch('/workspaces/{name}', [WorkspaceController::class, 'update']);
        Route::delete('/workspaces/{name}', [WorkspaceController::class, 'destroy']);
        Route::get('/workspaces/status/{name}', [WorkspaceController::class, 'status']);
        Route::post('/workspaces/{name}/disable', [WorkspaceController::class, 'disable']);
        Route::post('/workspaces/{name}/enable', [WorkspaceController::class, 'enable']);
        
        // Assignments routes
        Route::get('/assignments', [AssignmentController::class, 'index']);
        Route::post('/assignments', [AssignmentController::class, 'store']);
        Route::get('/assignments/{id}', [AssignmentController::class, 'show']);
        Route::patch('/assignments/{id}/archive', [AssignmentController::class, 'archive']);
        Route::patch('/assignments/{id}/assign', [AssignmentController::class, 'assign']);
        Route::patch('/assignments/{id}/changeQuantity', [AssignmentController::class, 'changeQuantity']);
        Route::patch('/assignments/{id}/close', [AssignmentController::class, 'close']);
        Route::post('/assignments/{id}/close', [AssignmentController::class, 'close']);
        Route::get('/assignments/{id}/recordAudio', [AssignmentController::class, 'getRecordAudio']);
        Route::patch('/assignments/{id}/recordAudio', [AssignmentController::class, 'setRecordAudio']);
        
        // Interviews routes
        Route::get('/interviews', [InterviewController::class, 'index']);
        Route::get('/interviews/{id}', [InterviewController::class, 'show']);
        Route::delete('/interviews/{id}', [InterviewController::class, 'destroy']);
        Route::patch('/interviews/{id}/approve', [InterviewController::class, 'approve']);
        Route::patch('/interviews/{id}/assign', [InterviewController::class, 'assign']);
        Route::patch('/interviews/{id}/assignsupervisor', [InterviewController::class, 'assignSupervisor']);
        Route::patch('/interviews/{id}/hqapprove', [InterviewController::class, 'hqApprove']);
        Route::patch('/interviews/{id}/hqreject', [InterviewController::class, 'hqReject']);
        Route::patch('/interviews/{id}/reject', [InterviewController::class, 'reject']);
        Route::get('/interviews/{id}/stats', [InterviewController::class, 'stats']);
        
        // Users, Supervisors, and Interviewers routes
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::patch('/users/{id}/archive', [UserController::class, 'archive']);
        Route::patch('/users/{id}/unarchive', [UserController::class, 'unarchive']);
        
        Route::get('/supervisors', [UserController::class, 'supervisors']);
        Route::get('/supervisors/{id}', [UserController::class, 'supervisor']);
        Route::get('/supervisors/{supervisorId}/interviewers', [UserController::class, 'supervisorInterviewers']);
    
        Route::get('/interviewers', [UserController::class, 'interviewers']);
        Route::get('/interviewers/{id}', [UserController::class, 'interviewer']);
    }); // End of auth:sanctum middleware group
});