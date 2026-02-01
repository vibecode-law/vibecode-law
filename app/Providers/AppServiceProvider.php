<?php

namespace App\Providers;

use App\Models\Showcase\Showcase;
use App\Models\User;
use App\Policies\Showcase\ShowcasePolicy;
use App\Policies\UserPolicy;
use App\Services\Content\ContentNavigationService;
use App\Services\Content\ContentService;
use App\Services\Markdown\MarkdownService;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use App\Services\MarketingEmail\Recipients\MailcoachRecipientService;
use App\Services\MarketingEmail\Recipients\NullRecipientService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MarkdownService::class);
        $this->app->singleton(ContentService::class);
        $this->app->singleton(ContentNavigationService::class);

        $this->app->bind(RecipientService::class, function () {
            $listUuid = Config::get('marketing.main_list_uuid');

            if (empty($listUuid) === true || app()->runningUnitTests()) {
                return new NullRecipientService;
            }

            return new MailcoachRecipientService;
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
    }
}
