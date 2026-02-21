<?php

use App\Http\Controllers\Api\Learn\LessonPlayerEventController;
use App\Http\Controllers\Api\OrganisationsController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Support\Facades\Route;

Route::prefix('learn/courses')->name('learn.courses.')->scopeBindings()->group(function () {
    Route::post('/{course}/lessons/{lesson}/player-event', LessonPlayerEventController::class)->name('lessons.player-event');
});

Route::middleware(['auth', 'verified', 'can:access-staff'])->group(function () {
    Route::get('/organisations', OrganisationsController::class)->name('organisations');
    Route::get('/users', UsersController::class)->name('users');
});
