<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('shares app name with all requests', function () {
    get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('name', config('app.name'))
        );
});

test('shares app url with all requests', function () {
    get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('appUrl', config('app.url'))
        );
});

test('shares default meta description with all requests', function () {
    get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('defaultMetaDescription', config('content.default_meta_description'))
        );
});

test('shares legal pages with all requests', function () {
    get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('legalPages', 3)
            ->has('legalPages.0', fn (AssertableInertia $legalPage) => $legalPage
                ->where('title', 'Community Guidelines')
                ->where('route', route('legal.show', 'community-guidelines'))
            )
            ->has('legalPages.1', fn (AssertableInertia $legalPage) => $legalPage
                ->where('title', 'Terms of Use')
                ->where('route', route('legal.show', 'terms-of-use'))
            )
            ->has('legalPages.2', fn (AssertableInertia $legalPage) => $legalPage
                ->where('title', 'Privacy Notice')
                ->where('route', route('legal.show', 'privacy-notice'))
            )
        );
});

test('shares transformImages as true when image transform base url is configured', function () {
    Config::set('services.image-transform.base_url', 'https://example.gumlet.io');

    get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('transformImages', true)
        );
});

test('shares transformImages as false when image transform base url is not configured', function () {
    Config::set('services.image-transform.base_url', null);

    get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('transformImages', false)
        );
});

describe('flash messages', function () {
    test('flash is empty object by default', function () {
        get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('flash', [])
            );
    });

    test('flash contains session flash data', function () {
        get('/')
            ->assertOk();

        session()->flash('flash', ['success' => 'Operation completed']);

        get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('flash.success', 'Operation completed')
            );
    });
});

describe('guest users', function () {
    test('auth user is null for guests', function () {
        get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth.user', null)
            );
    });

    test('auth permissions is empty array for guests', function () {
        get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth.permissions', [])
            );
    });
});

describe('authenticated users', function () {
    test('auth user contains all expected properties and no others', function () {
        /** @var User $user */
        $user = User::factory()->create([
            'organisation' => 'Test Org',
            'job_title' => 'Developer',
            'linkedin_url' => 'https://linkedin.com/in/test',
            'bio' => 'Test bio',
        ]);

        actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth.user', [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'handle' => $user->handle,
                    'organisation' => $user->organisation,
                    'job_title' => $user->job_title,
                    'avatar' => $user->avatar,
                    'linkedin_url' => $user->linkedin_url,
                    'bio' => $user->bio,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at->toIso8601String(),
                    'marketing_opt_out_at' => $user->marketing_opt_out_at,
                ])
            );
    });

    test('regular user has empty permissions array', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth.permissions', [])
            );
    });

    test('user with direct permissions receives those permissions', function () {
        $permission = Permission::firstOrCreate(['name' => 'staff.access']);

        /** @var User */
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth.permissions', ['staff.access'])
            );
    });

    test('user with role receives role permissions', function () {
        /** @var User */
        $user = User::factory()->moderator()->create();

        actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth.permissions', fn ($permissions) => collect($permissions)->contains('staff.access')
                    && collect($permissions)->contains('showcase.approve-reject')
                    && collect($permissions)->contains('showcase.feature')
                )
            );
    });

    test('admin user receives wildcard permission', function () {
        /** @var User */
        $user = User::factory()->admin()->create();

        actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth.permissions', ['*'])
            );
    });
});
