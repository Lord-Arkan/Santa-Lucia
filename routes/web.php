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
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('module:dashboard');

    Route::get('/history', fn () => Inertia::render('ModulePlaceholder', [
        'title' => 'Historial',
        'description' => 'El modulo de historial clinico esta disponible para su proxima implementacion.',
    ]))->name('history.index')->middleware('module:history');

    Route::get('/reports', fn () => Inertia::render('ModulePlaceholder', [
        'title' => 'Reportes',
        'description' => 'El modulo de reportes esta disponible para su proxima implementacion.',
    ]))->name('reports.index')->middleware('module:reports');

    Route::resource('users', UserManagementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['users' => 'user'])
        ->middleware('module:configuration');

    Route::patch('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
        ->name('users.toggleStatus')
        ->middleware('module:configuration');

    Route::patch('patients/{patient}/toggle-status', [PatientManagementController::class, 'toggleStatus'])
        ->name('patients.toggleStatus')
        ->middleware('module:patients');

    Route::resource('patients', PatientManagementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['patients' => 'patient'])
        ->middleware('module:patients');

    Route::patch('doctors/{doctor}/toggle-status', [DoctorManagementController::class, 'toggleStatus'])
        ->name('doctors.toggleStatus')
        ->middleware('module:doctors');

    Route::resource('doctors', DoctorManagementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['doctors' => 'doctor'])
        ->middleware('module:doctors');

    Route::patch('services/{service}/toggle-status', [\App\Http\Controllers\ServiceManagementController::class, 'toggleStatus'])
        ->name('services.toggleStatus')
        ->middleware('module:services');

    Route::resource('services', \App\Http\Controllers\ServiceManagementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['services' => 'service'])
        ->middleware('module:services');

    Route::patch('doctor-schedules/{schedule}/toggle-status', [\App\Http\Controllers\DoctorScheduleManagementController::class, 'toggleStatus'])
        ->name('doctor-schedules.toggleStatus')
        ->middleware('module:doctor_schedules');

    Route::resource('doctor-schedules', \App\Http\Controllers\DoctorScheduleManagementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['doctor-schedules' => 'schedule'])
        ->middleware('module:doctor_schedules');

    // Appointments (Citas)
    Route::get('appointments/slots', [\App\Http\Controllers\AppointmentManagementController::class, 'slots'])
        ->name('appointments.slots')
        ->middleware('module:appointments');

    Route::patch('appointments/{appointment}/update-status', [\App\Http\Controllers\AppointmentManagementController::class, 'updateStatus'])
        ->name('appointments.updateStatus')
        ->middleware('module:appointments');

    Route::post('appointments/patients', [\App\Http\Controllers\AppointmentManagementController::class, 'storePatient'])
        ->name('appointments.patients.store')
        ->middleware('module:appointments');

    Route::resource('appointments', \App\Http\Controllers\AppointmentManagementController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy'])
        ->parameters(['appointments' => 'appointment'])
        ->middleware('module:appointments');
});
