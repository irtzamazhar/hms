<?php

use App\Http\Controllers\Api\AppointmentApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\DoctorApiController;
use App\Http\Controllers\Api\IpdApiController;
use App\Http\Controllers\Api\LabApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\OpdApiController;
use App\Http\Controllers\Api\PatientApiController;
use Illuminate\Support\Facades\Route;

// ── Authentication ─────────────────────────────────────
Route::prefix('auth')->group(function () {
    // Rate-limit credential submission to blunt brute-force / credential stuffing.
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// ── Protected Routes ───────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    // Patients
    Route::get('/patients', [PatientApiController::class, 'index']);
    Route::post('/patients', [PatientApiController::class, 'store']);
    Route::get('/patients/{patient}', [PatientApiController::class, 'show']);
    Route::put('/patients/{patient}', [PatientApiController::class, 'update']);

    // Appointments
    Route::get('/appointments', [AppointmentApiController::class, 'index']);
    Route::post('/appointments', [AppointmentApiController::class, 'store']);
    Route::get('/appointments/{appointment}', [AppointmentApiController::class, 'show']);
    Route::patch('/appointments/{appointment}/status', [AppointmentApiController::class, 'updateStatus']);

    // OPD Visits
    Route::get('/opd', [OpdApiController::class, 'index']);
    Route::post('/opd', [OpdApiController::class, 'store']);
    Route::get('/opd/{opd}', [OpdApiController::class, 'show']);

    // IPD Admissions
    Route::get('/ipd', [IpdApiController::class, 'index']);
    Route::post('/ipd', [IpdApiController::class, 'store']);
    Route::get('/ipd/{ipd}', [IpdApiController::class, 'show']);

    // Doctors
    Route::get('/doctors', [DoctorApiController::class, 'index']);

    // Laboratory
    Route::get('/lab', [LabApiController::class, 'index']);
    Route::get('/lab/{lab}', [LabApiController::class, 'show']);

    // Notifications
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationApiController::class, 'unreadCount']);
    Route::patch('/notifications/{id}/read', [NotificationApiController::class, 'markRead']);
    Route::post('/notifications/mark-all-read', [NotificationApiController::class, 'markAllRead']);
});
