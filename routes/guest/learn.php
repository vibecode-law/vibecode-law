<?php

use App\Http\Controllers\Learn\CourseShowController;
use App\Http\Controllers\Learn\GuideShowController;
use App\Http\Controllers\Learn\LearnIndexController;
use App\Http\Controllers\Learn\LessonShowController;
use Illuminate\Support\Facades\Route;

Route::prefix('learn')->name('learn.')->group(function () {
    Route::get('/', LearnIndexController::class)->name('index');

    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/{course}', CourseShowController::class)->name('show');
        Route::get('/{course}/lessons/{lesson}', LessonShowController::class)->name('lessons.show')->scopeBindings();
    });

    Route::get('/guides/{slug}', GuideShowController::class)->name('guides.show');
});
