<?php

namespace App\Providers;

use App\Models\PressCoverage;
use App\Models\Showcase\Showcase;
use App\Models\Testimonial;
use App\Models\User;
use App\Policies\PressCoveragePolicy;
use App\Policies\Showcase\ShowcasePolicy;
use App\Policies\TestimonialPolicy;
use App\Policies\UserPolicy;
use App\Services\Content\ContentNavigationService;
use App\Services\Content\ContentService;
use App\Services\Markdown\MarkdownService;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use App\Services\MarketingEmail\Recipients\MailcoachRecipientService;
use App\Services\MarketingEmail\Recipients\NullRecipientService;
use App\Services\VideoHost\Contracts\VideoHostService;
use App\Services\VideoHost\MuxVideoHostService;
use App\Services\VideoHost\NullVideoHostService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Fix incorrect alias from spatie/laravel-mailcoach-sdk package
        AliasLoader::getInstance()->alias(
            alias: 'Mailcoach',
            class: \Spatie\MailcoachSdk\Facades\Mailcoach::class,
        );

        $this->app->singleton(MarkdownService::class);
        $this->app->singleton(ContentService::class);
        $this->app->singleton(ContentNavigationService::class);

        $this->app->bind(RecipientService::class, function () {
            if (Config::get('marketing.enabled') === false || app()->runningUnitTests()) {
                return new NullRecipientService;
            }

            return new MailcoachRecipientService;
        });

        $this->app->bind(VideoHostService::class, function () {
            if (Config::get('video.enabled') === false || app()->runningUnitTests()) {
                return new NullVideoHostService;
            }

            return new MuxVideoHostService(
                tokenId: Config::get('video.mux.token_id'),
                tokenSecret: Config::get('video.mux.token_secret'),
                signingKeyId: Config::get('video.mux.signing_key_id'),
                signingPrivateKey: Config::get('video.mux.signing_private_key'),
            );
        });
    }

    public function boot(): void
    {
        Model::shouldBeStrict();

        Gate::before(function (User $user, string $ability) {
            if ($user->is_admin) {
                return true;
            }
        });

        Gate::define('access-staff', function (User $user) {
            return $user->hasRole('Moderator');
        });

        Gate::policy(Showcase::class, ShowcasePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Testimonial::class, TestimonialPolicy::class);
        Gate::policy(PressCoverage::class, PressCoveragePolicy::class);
    }
}
