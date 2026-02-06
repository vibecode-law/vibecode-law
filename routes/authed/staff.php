<?php

use App\Http\Controllers\Staff\PracticeAreaController;
use App\Http\Controllers\Staff\PressCoverage\DestroyController as PressCoverageDestroyController;
use App\Http\Controllers\Staff\PressCoverage\IndexController as PressCoverageIndexController;
use App\Http\Controllers\Staff\PressCoverage\ReorderController as PressCoverageReorderController;
use App\Http\Controllers\Staff\PressCoverage\StoreController as PressCoverageStoreController;
use App\Http\Controllers\Staff\PressCoverage\UpdateController as PressCoverageUpdateController;
use App\Http\Controllers\Staff\ShowcaseModeration\ApproveController as ShowcaseModerationApproveController;
use App\Http\Controllers\Staff\ShowcaseModeration\ApproveDraftController as ShowcaseModerationApproveDraftController;
use App\Http\Controllers\Staff\ShowcaseModeration\IndexController as ShowcaseModerationIndexController;
use App\Http\Controllers\Staff\ShowcaseModeration\RejectController as ShowcaseModerationRejectController;
use App\Http\Controllers\Staff\ShowcaseModeration\RejectDraftController as ShowcaseModerationRejectDraftController;
use App\Http\Controllers\Staff\ShowcaseModeration\ToggleFeaturedController as ShowcaseModerationToggleFeaturedController;
use App\Http\Controllers\Staff\Testimonials\DestroyController as TestimonialDestroyController;
use App\Http\Controllers\Staff\Testimonials\IndexController as TestimonialIndexController;
use App\Http\Controllers\Staff\Testimonials\ReorderController as TestimonialReorderController;
use App\Http\Controllers\Staff\Testimonials\StoreController as TestimonialStoreController;
use App\Http\Controllers\Staff\Testimonials\UpdateController as TestimonialUpdateController;
use App\Http\Controllers\Staff\UserManagement\CreateController as UserCreateController;
use App\Http\Controllers\Staff\UserManagement\DestroyController as UserDestroyController;
use App\Http\Controllers\Staff\UserManagement\EditController as UserEditController;
use App\Http\Controllers\Staff\UserManagement\IndexController as UserIndexController;
use App\Http\Controllers\Staff\UserManagement\SendPasswordResetController as UserSendPasswordResetController;
use App\Http\Controllers\Staff\UserManagement\StoreController as UserStoreController;
use App\Http\Controllers\Staff\UserManagement\ToggleSubmissionsController as UserToggleSubmissionsController;
use App\Http\Controllers\Staff\UserManagement\UpdateController as UserUpdateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['can:access-staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::prefix('practice-areas')->name('practice-areas.')->group(function () {
        Route::get('/', [PracticeAreaController::class, 'index'])->name('index');
        Route::post('/', [PracticeAreaController::class, 'store'])->name('store');
        Route::put('/{practiceArea}', [PracticeAreaController::class, 'update'])->name('update');
    });

    Route::prefix('showcase-moderation')->name('showcase-moderation.')->group(function () {
        Route::get('/', ShowcaseModerationIndexController::class)->name('index');
        Route::post('/{showcase}/approve', ShowcaseModerationApproveController::class)->name('approve');
        Route::post('/{showcase}/reject', ShowcaseModerationRejectController::class)->name('reject');
        Route::post('/{showcase}/toggle-featured', ShowcaseModerationToggleFeaturedController::class)->name('toggle-featured');

        // Draft moderation
        Route::post('/drafts/{draft}/approve', ShowcaseModerationApproveDraftController::class)->name('drafts.approve');
        Route::post('/drafts/{draft}/reject', ShowcaseModerationRejectDraftController::class)->name('drafts.reject');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', UserIndexController::class)->name('index');
        Route::get('/create', UserCreateController::class)->name('create');
        Route::post('/', UserStoreController::class)->name('store');
        Route::get('/{user}/edit', UserEditController::class)->name('edit');
        Route::patch('/{user}', UserUpdateController::class)->name('update');
        Route::delete('/{user}', UserDestroyController::class)->name('destroy');
        Route::post('/{user}/toggle-submissions', UserToggleSubmissionsController::class)->name('toggle-submissions');
        Route::post('/{user}/send-password-reset', UserSendPasswordResetController::class)->name('send-password-reset');
    });

    Route::prefix('testimonials')->name('testimonials.')->group(function () {
        Route::get('/', TestimonialIndexController::class)->name('index');
        Route::post('/', TestimonialStoreController::class)->name('store');
        Route::post('/reorder', TestimonialReorderController::class)->name('reorder');
        Route::match(['put', 'patch'], '/{testimonial}', TestimonialUpdateController::class)->name('update');
        Route::delete('/{testimonial}', TestimonialDestroyController::class)->name('destroy');
    });

    Route::prefix('press-coverage')->name('press-coverage.')->group(function () {
        Route::get('/', PressCoverageIndexController::class)->name('index');
        Route::post('/', PressCoverageStoreController::class)->name('store');
        Route::post('/reorder', PressCoverageReorderController::class)->name('reorder');
        Route::match(['put', 'patch'], '/{pressCoverage}', PressCoverageUpdateController::class)->name('update');
        Route::delete('/{pressCoverage}', PressCoverageDestroyController::class)->name('destroy');
    });
});
