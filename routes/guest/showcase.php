<?php

use App\Http\Controllers\Showcase\Public\ShowcaseIndexController;
use App\Http\Controllers\Showcase\Public\ShowcaseMonthIndexController;
use App\Http\Controllers\Showcase\Public\ShowcasePracticeAreaIndexController;

// Public showcase routes (specific paths only - catch-all {showcase} is in web.php after auth routes)
Route::prefix('showcase')->name('showcase.')->group(function () {
    Route::get('/', ShowcaseIndexController::class)->name('index');
    Route::get('/practice-area/{practiceArea:slug}', ShowcasePracticeAreaIndexController::class)->name('practice-area');
    Route::get('/month/{month}', ShowcaseMonthIndexController::class)->name('month');
});
