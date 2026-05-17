<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserManagementController;

Route::redirect('/', '/login');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('usuarios', UserManagementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['usuarios' => 'user'])
        ->middleware('role:administrador,jefe_area');
});
