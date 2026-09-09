<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\OrganizationSettingsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitCheckInController;
use App\Http\Controllers\Admin\VisitController;
use App\Http\Controllers\Admin\VisitStatusController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\GuestFeedbackController;
use App\Http\Controllers\Public\GuestRegistrationController;
use Illuminate\Support\Facades\Route;

// Public guest routes
Route::get('/', [GuestRegistrationController::class, 'create'])->name('public.registration.create');
Route::post('/', [GuestRegistrationController::class, 'store'])
    ->middleware('throttle:15,1')
    ->name('public.registration.store');
Route::get('/kunjungan/{publicId}/sukses', [GuestRegistrationController::class, 'success'])->name('public.registration.success');
Route::get('/kunjungan/{publicId}/cetak', [GuestRegistrationController::class, 'printTicket'])->name('public.registration.ticket');

// Public guest satisfaction survey
Route::get('/visit/{publicId}/feedback', [GuestFeedbackController::class, 'create'])->name('public.feedback.create');
Route::post('/visit/{publicId}/feedback', [GuestFeedbackController::class, 'store'])->name('public.feedback.store');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Back-office protected routes
Route::middleware(['auth', 'active.user'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
    Route::get('/visits/{visit}', [VisitController::class, 'show'])->name('visits.show');
    Route::patch('/visits/{visit}/status', [VisitStatusController::class, 'update'])->name('visits.status.update');

    // QR Check-In Scanner
    Route::get('/checkin/scan', [VisitCheckInController::class, 'showScanner'])->name('checkin.scan');
    Route::post('/checkin/process', [VisitCheckInController::class, 'processCheckIn'])->name('checkin.process');

    // Guest Feedback & IKM Analytics
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');

    // Departments Management
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::patch('departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])->name('departments.toggle-status');

    // User Management
    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Audit Logs (Read-only)
    Route::get('audits', [AuditController::class, 'index'])->name('audits.index');
    Route::get('audits/{audit}', [AuditController::class, 'show'])->name('audits.show');

    // Organization Settings
    Route::get('settings', [OrganizationSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [OrganizationSettingsController::class, 'update'])->name('settings.update');

    // Reports & Ephemeral Exports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('reports/exports/{export}/download', [ReportController::class, 'download'])->name('reports.download');
});
