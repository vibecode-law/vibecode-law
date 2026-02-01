<?php

use App\Http\Controllers\Auth\CompleteProfileController;
use App\Http\Controllers\Auth\LinkedinAuthCallbackController;
use App\Http\Controllers\Auth\LinkedinAuthRedirectController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->name('auth.login.linkedin.')->group(function () {
    Route::get('/auth/login/linkedin/redirect', LinkedinAuthRedirectController::class)->name('redirect');
    Route::get('/auth/login/linkedin/callback', LinkedinAuthCallbackController::class)->name('callback');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/auth/complete-profile', [CompleteProfileController::class, 'show'])->name('auth.complete-profile');
    Route::post('/auth/complete-profile', [CompleteProfileController::class, 'store'])->name('auth.complete-profile.store');
    Route::post('/auth/complete-profile/skip', [CompleteProfileController::class, 'skip'])->name('auth.complete-profile.skip');
});
