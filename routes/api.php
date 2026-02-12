<?php

use App\Http\Controllers\Api\OrganisationsController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:access-staff'])->group(function () {
    Route::get('/organisations', OrganisationsController::class)->name('organisations');
    Route::get('/users', UsersController::class)->name('users');
});
