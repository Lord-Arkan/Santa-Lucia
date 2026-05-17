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
    \App\Http\Middleware\EnsureUserIsActive::class,
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('users', UserManagementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['users' => 'user'])
        ->middleware('role:administrador,jefe_area');

    Route::patch('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
        ->name('users.toggleStatus')
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

    Route::patch('services/{service}/toggle-status', [\App\Http\Controllers\ServiceManagementController::class, 'toggleStatus'])
        ->name('services.toggleStatus')
        ->middleware('role:administrador,jefe_area');

    Route::resource('services', \App\Http\Controllers\ServiceManagementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['services' => 'service'])
        ->middleware('role:administrador,jefe_area');

    Route::patch('doctor-schedules/{schedule}/toggle-status', [\App\Http\Controllers\DoctorScheduleManagementController::class, 'toggleStatus'])
        ->name('doctor-schedules.toggleStatus')
        ->middleware('role:administrador,doctor');

    Route::resource('doctor-schedules', \App\Http\Controllers\DoctorScheduleManagementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['doctor-schedules' => 'schedule'])
        ->middleware('role:administrador,doctor');

    // Appointments (Citas)
    Route::get('appointments/slots', [\App\Http\Controllers\AppointmentManagementController::class, 'slots'])
        ->name('appointments.slots')
        ->middleware('role:administrador,asistente,doctor');

    Route::patch('appointments/{appointment}/update-status', [\App\Http\Controllers\AppointmentManagementController::class, 'updateStatus'])
        ->name('appointments.updateStatus')
        ->middleware('role:administrador,doctor');

    Route::post('appointments/patients', [\App\Http\Controllers\AppointmentManagementController::class, 'storePatient'])
        ->name('appointments.patients.store')
        ->middleware('role:administrador,asistente,doctor');

    Route::resource('appointments', \App\Http\Controllers\AppointmentManagementController::class)
        ->only(['index', 'create', 'store', 'destroy'])
        ->parameters(['appointments' => 'appointment'])
        ->middleware('role:administrador,asistente,doctor');
});
