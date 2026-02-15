<?php

use App\Http\Controllers\Course\Public\CourseEnrollController;
use App\Http\Controllers\Course\Public\LessonCompleteController;
use Illuminate\Support\Facades\Route;

Route::prefix('learn/courses')->name('learn.courses.')->group(function () {
    Route::post('/{course}/enroll', CourseEnrollController::class)->name('enroll');
    Route::post('/{course}/lessons/{lesson}/complete', LessonCompleteController::class)->name('lessons.complete')->scopeBindings();
});
