<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VisitController;
use App\Http\Controllers\Admin\VisitStatusController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\GuestRegistrationController;
use Illuminate\Support\Facades\Route;

// Public guest routes
Route::get('/', [GuestRegistrationController::class, 'create'])->name('public.registration.create');
Route::post('/', [GuestRegistrationController::class, 'store'])
    ->middleware('throttle:15,1')
    ->name('public.registration.store');
Route::get('/kunjungan/{publicId}/sukses', [GuestRegistrationController::class, 'success'])->name('public.registration.success');

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
});
