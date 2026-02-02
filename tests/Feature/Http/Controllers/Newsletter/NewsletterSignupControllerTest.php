<?php

use App\Jobs\MarketingEmail\CreateGuestSubscriberJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('allows guests to signup for the newsletter', function () {
    Queue::fake();

    post(route('newsletter.signup'), [
        'email' => 'guest@example.com',
    ])
        ->assertRedirect()
        ->assertSessionHas('flash.newsletter_success');

    Queue::assertPushed(CreateGuestSubscriberJob::class, function (CreateGuestSubscriberJob $job) {
        return $job->email === 'guest@example.com';
    });
});

it('dispatches the job with the correct email', function () {
    Queue::fake();

    post(route('newsletter.signup'), [
        'email' => 'test@example.org',
    ])->assertRedirect();

    Queue::assertPushed(CreateGuestSubscriberJob::class, function (CreateGuestSubscriberJob $job) {
        return $job->email === 'test@example.org';
    });
});

describe('auth', function () {
    it('rejects authenticated users with a helpful message', function () {
        Queue::fake();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user)
            ->post(route('newsletter.signup'), [
                'email' => 'another@example.com',
            ])
            ->assertSessionHasErrors([
                'email' => 'You are already logged in. Please manage your newsletter preferences in your profile settings.',
            ]);

        Queue::assertNotPushed(CreateGuestSubscriberJob::class);
    });

    it('rejects emails already linked to a user account', function () {
        Queue::fake();

        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        post(route('newsletter.signup'), [
            'email' => $existingUser->email,
        ])
            ->assertSessionHasErrors([
                'email' => 'This email is already linked to an account. Please login to manage your newsletter preferences in your profile settings.',
            ]);

        Queue::assertNotPushed(CreateGuestSubscriberJob::class);
    });
});

describe('validation', function () {
    it('requires an email', function () {
        Queue::fake();

        post(route('newsletter.signup'), [])
            ->assertSessionHasErrors('email');

        Queue::assertNotPushed(CreateGuestSubscriberJob::class);
    });

    it('requires a valid email format', function () {
        Queue::fake();

        post(route('newsletter.signup'), [
            'email' => 'not-a-valid-email',
        ])
            ->assertSessionHasErrors('email');

        Queue::assertNotPushed(CreateGuestSubscriberJob::class);
    });

    it('rejects emails that exceed max length', function () {
        Queue::fake();

        $longEmail = str_repeat('a', 250).'@example.com';

        post(route('newsletter.signup'), [
            'email' => $longEmail,
        ])
            ->assertSessionHasErrors('email');

        Queue::assertNotPushed(CreateGuestSubscriberJob::class);
    });

    it('validates invalid data', function (array $data, string $invalid) {
        Queue::fake();

        post(route('newsletter.signup'), $data)
            ->assertSessionHasErrors($invalid);

        Queue::assertNotPushed(CreateGuestSubscriberJob::class);
    })->with([
        'missing email' => [[], 'email'],
        'invalid email format' => [['email' => 'invalid'], 'email'],
        'email too long' => [['email' => str_repeat('a', 250).'@example.com'], 'email'],
    ]);
});
