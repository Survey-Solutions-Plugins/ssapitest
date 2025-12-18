<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ManageController;

/*
|--------------------------------------------------------------------------
| Survey Solutions Web Interface Routes
|--------------------------------------------------------------------------
*/

// Home page - Headquarters login
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/login', [HomeController::class, 'login'])->name('login');
Route::post('/logout', [HomeController::class, 'logout'])->name('logout');

// Dashboard - Survey Solutions interface
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/{module}', [DashboardController::class, 'index'])
	->whereIn('module', ['overview', 'workspaces', 'assignments', 'interviews', 'users'])
	->name('dashboard.module');

// Minimal create actions (POST) against connected HQ
Route::post('/dashboard/workspaces', [ManageController::class, 'createWorkspace'])->name('dashboard.workspaces.create');
Route::patch('/dashboard/workspaces/{name}', [ManageController::class, 'updateWorkspace'])->name('dashboard.workspaces.update');
Route::post('/dashboard/workspaces/{name}/disable', [ManageController::class, 'disableWorkspace'])->name('dashboard.workspaces.disable');
Route::post('/dashboard/workspaces/{name}/enable', [ManageController::class, 'enableWorkspace'])->name('dashboard.workspaces.enable');
Route::post('/dashboard/users', [ManageController::class, 'createUser'])->name('dashboard.users.create');
