<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

describe('auth', function () {
    it('requires authentication for show', function () {
        get('/auth/complete-profile')
            ->assertRedirect('/login');
    });

    it('requires authentication for store', function () {
        post('/auth/complete-profile')
            ->assertRedirect('/login');
    });

    it('requires authentication for skip', function () {
        post('/auth/complete-profile/skip')
            ->assertRedirect('/login');
    });
});

describe('show', function () {
    it('renders the complete profile page', function () {
        /** @var User $user */
        $user = User::factory()->create();

        actingAs($user)
            ->get('/auth/complete-profile')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('auth/complete-profile'));
    });

    it('passes intended url to the view', function () {
        /** @var User $user */
        $user = User::factory()->create();

        actingAs($user)
            ->get('/auth/complete-profile?intended=/showcases')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('auth/complete-profile')
                ->where('intended', '/showcases')
            );
    });

    it('passes null intended when not provided', function () {
        /** @var User $user */
        $user = User::factory()->create();

        actingAs($user)
            ->get('/auth/complete-profile')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('auth/complete-profile')
                ->where('intended', null)
            );
    });
});

describe('store', function () {
    it('updates profile fields', function () {
        /** @var User $user */
        $user = User::factory()->create([
            'job_title' => null,
            'organisation' => null,
            'linkedin_url' => null,
            'bio' => null,
        ]);

        actingAs($user)
            ->post('/auth/complete-profile', [
                'job_title' => 'Software Engineer',
                'organisation' => 'Acme Corp',
                'linkedin_url' => 'https://www.linkedin.com/in/john-doe',
                'bio' => 'A software engineer who loves Laravel.',
            ])
            ->assertRedirect('/');

        assertDatabaseHas('users', [
            'id' => $user->id,
            'job_title' => 'Software Engineer',
            'organisation' => 'Acme Corp',
            'linkedin_url' => 'https://www.linkedin.com/in/john-doe',
            'bio' => 'A software engineer who loves Laravel.',
        ]);
    });

    it('sets marketing_opt_out_at when marketing opt out is checked', function () {
        /** @var User $user */
        $user = User::factory()->create([
            'marketing_opt_out_at' => null,
        ]);

        actingAs($user)
            ->post('/auth/complete-profile', [
                'marketing_opt_out' => true,
            ])
            ->assertRedirect('/');

        $user->refresh();

        expect($user->marketing_opt_out_at)->not->toBeNull();
    });

    it('does not set marketing_opt_out_at when opt out is not checked', function () {
        /** @var User $user */
        $user = User::factory()->create([
            'marketing_opt_out_at' => null,
        ]);

        actingAs($user)
            ->post('/auth/complete-profile', [
                'marketing_opt_out' => false,
            ])
            ->assertRedirect('/');

        $user->refresh();

        expect($user->marketing_opt_out_at)->toBeNull();
    });

    it('allows empty profile fields', function () {
        /** @var User $user */
        $user = User::factory()->create();

        actingAs($user)
            ->post('/auth/complete-profile', [])
            ->assertRedirect('/');
    });

    it('redirects to intended destination after completion', function () {
        /** @var User $user */
        $user = User::factory()->create();

        actingAs($user)
            ->post('/auth/complete-profile', [
                'job_title' => 'Developer',
                'intended' => '/showcases',
            ])
            ->assertRedirect('/showcases');
    });

    it('redirects to home when no intended destination', function () {
        /** @var User $user */
        $user = User::factory()->create();

        actingAs($user)
            ->post('/auth/complete-profile', [
                'job_title' => 'Developer',
            ])
            ->assertRedirect('/');
    });

    it('validates linkedin url format', function ($data, $invalid) {
        /** @var User $user */
        $user = User::factory()->create();

        actingAs($user)
            ->post('/auth/complete-profile', $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'invalid url' => [
            ['linkedin_url' => 'not-a-url'],
            ['linkedin_url'],
        ],
        'non-linkedin url' => [
            ['linkedin_url' => 'https://www.google.com/in/john-doe'],
            ['linkedin_url'],
        ],
        'linkedin without /in/' => [
            ['linkedin_url' => 'https://www.linkedin.com/company/acme'],
            ['linkedin_url'],
        ],
    ]);

    it('accepts valid linkedin urls', function ($linkedin_url) {
        /** @var User $user */
        $user = User::factory()->create();

        actingAs($user)
            ->post('/auth/complete-profile', [
                'linkedin_url' => $linkedin_url,
            ])
            ->assertSessionDoesntHaveErrors('linkedin_url');
    })->with([
        'https://www.linkedin.com/in/john-doe',
        'https://uk.linkedin.com/in/john-doe',
        'https://de.linkedin.com/in/john-doe-123456',
    ]);
});

describe('skip', function () {
    it('redirects without saving any data', function () {
        /** @var User $user */
        $user = User::factory()->create([
            'job_title' => null,
            'organisation' => null,
        ]);

        actingAs($user)
            ->post('/auth/complete-profile/skip')
            ->assertRedirect('/');

        $user->refresh();

        expect($user->job_title)->toBeNull();
        expect($user->organisation)->toBeNull();
    });

    it('redirects to intended destination', function () {
        /** @var User $user */
        $user = User::factory()->create();

        actingAs($user)
            ->post('/auth/complete-profile/skip', [
                'intended' => '/showcases',
            ])
            ->assertRedirect('/showcases');
    });

    it('redirects to home when no intended destination', function () {
        /** @var User $user */
        $user = User::factory()->create();

        actingAs($user)
            ->post('/auth/complete-profile/skip')
            ->assertRedirect('/');
    });
});
