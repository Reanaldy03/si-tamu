<?php

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
    Route::get('/dashboard', function () {
        return response()->json([
            'message' => 'Selamat datang di dashboard SI-TAMU',
            'user' => auth()->user()?->only(['id', 'public_id', 'name', 'email']),
        ]);
    })->name('dashboard');
});
