<?php

use App\Http\Controllers\Course\Public\CourseIndexController;
use App\Http\Controllers\Course\Public\CourseShowController;
use App\Http\Controllers\Course\Public\LessonShowController;
use Illuminate\Support\Facades\Route;

Route::prefix('learn/courses')->name('learn.courses.')->group(function () {
    Route::get('/', CourseIndexController::class)->name('index');
    Route::get('/{course}', CourseShowController::class)->name('show');
    Route::get('/{course}/lessons/{lesson}', LessonShowController::class)->name('lessons.show')->scopeBindings();
});
