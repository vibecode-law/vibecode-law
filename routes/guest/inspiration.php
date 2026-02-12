<?php

use App\Http\Controllers\Challenge\Public\ChallengeIndexController;
use App\Http\Controllers\Challenge\Public\ChallengeShowController;

Route::prefix('inspiration')->name('inspiration.')->group(function () {
    Route::get('/', ChallengeIndexController::class)->name('index');
    Route::get('/challenges/{challenge:slug}', ChallengeShowController::class)->name('challenges.show');
});
