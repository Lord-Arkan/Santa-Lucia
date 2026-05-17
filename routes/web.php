<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\PatientManagementController;
use App\Http\Controllers\DoctorManagementController;

Route::redirect('/', '/login');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('users', UserManagementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['users' => 'user'])
        ->middleware('role:administrador,jefe_area');

    Route::patch('patients/{patient}/toggle-status', [PatientManagementController::class, 'toggleStatus'])
        ->name('patients.toggleStatus')
        ->middleware('role:administrador,jefe_area');

    Route::resource('patients', PatientManagementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['patients' => 'patient'])
        ->middleware('role:administrador,jefe_area');

    Route::patch('doctors/{doctor}/toggle-status', [DoctorManagementController::class, 'toggleStatus'])
        ->name('doctors.toggleStatus')
        ->middleware('role:administrador,jefe_area');

    Route::resource('doctors', DoctorManagementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['doctors' => 'doctor'])
        ->middleware('role:administrador,jefe_area');
});
