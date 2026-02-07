<?php

use App\Http\Controllers\About\AboutIndexController;
use App\Http\Controllers\About\AboutShowController;
use App\Http\Controllers\About\CommunityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Legal\LegalShowController;
use App\Http\Controllers\Newsletter\NewsletterIndexController;
use App\Http\Controllers\Newsletter\NewsletterSignupController;
use App\Http\Controllers\Resources\ResourcesIndexController;
use App\Http\Controllers\Resources\ResourcesShowController;
use App\Http\Controllers\Showcase\Public\ShowcaseShowController;
use App\Http\Controllers\WallOfLove\WallOfLoveController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';
require __DIR__.'/authed/user-area.php';

Route::get('/', HomeController::class)->name('home');
Route::get('/legal/{slug}', LegalShowController::class)->name('legal.show');

Route::get('/about', AboutIndexController::class)->name('about.index');
Route::get('/about/the-community', CommunityController::class)->name('about.community');
Route::get('/about/{slug}', AboutShowController::class)->name('about.show');

Route::get('/resources', ResourcesIndexController::class)->name('resources.index');
Route::get('/resources/{slug}', ResourcesShowController::class)->name('resources.show');

Route::get('/newsletter', NewsletterIndexController::class)->name('newsletter.index');
Route::post('/newsletter/signup', NewsletterSignupController::class)->name('newsletter.signup');

Route::get('/wall-of-love', WallOfLoveController::class)->name('wall-of-love');

require __DIR__.'/guest/showcase.php';
require __DIR__.'/guest/user.php';

Route::middleware(['auth', 'verified'])->group(function () {
    require __DIR__.'/authed/showcase.php';
    require __DIR__.'/authed/staff.php';
});

// Showcase catch-all route (must be after auth routes to avoid conflicts with /showcase/create etc.)
Route::get('/showcase/{showcase}', ShowcaseShowController::class)->name('showcase.show');

// Webhooks
Route::webhooks('webhooks/mailcoach', 'mailcoach');
