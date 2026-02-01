<?php

use App\Jobs\MarketingEmail\CreateExternalSubscriberJob;
use App\Jobs\MarketingEmail\ResubscribeToMarketingJob;
use App\Jobs\MarketingEmail\UnsubscribeFromMarketingJob;
use App\Jobs\MarketingEmail\UpdateExternalSubscriberJob;
use App\Models\User;
use App\Services\User\ProfileService;
use Illuminate\Support\Facades\Queue;

describe('create', function () {
    it('creates a user with profile data', function () {
        Queue::fake();

        $service = new ProfileService;

        $user = $service->create(data: [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
            'organisation' => 'Acme Inc',
            'job_title' => 'Developer',
            'linkedin_url' => 'https://www.linkedin.com/in/johndoe',
            'bio' => 'A developer',
        ]);

        expect($user)->toBeInstanceOf(User::class)
            ->and($user->first_name)->toBe('John')
            ->and($user->last_name)->toBe('Doe')
            ->and($user->handle)->toBe('john-doe')
            ->and($user->email)->toBe('john@example.com')
            ->and($user->organisation)->toBe('Acme Inc')
            ->and($user->job_title)->toBe('Developer')
            ->and($user->linkedin_url)->toBe('https://www.linkedin.com/in/johndoe')
            ->and($user->bio)->toBe('A developer');
    });

    it('filters out non-profile fields', function () {
        Queue::fake();

        $service = new ProfileService;

        $user = $service->create(data: [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
            'password' => 'should-be-ignored',
            'team_type' => 'should-be-ignored',
            'avatar_path' => 'should-be-ignored',
        ]);

        expect($user->password)->toBeNull()
            ->and($user->team_type)->toBeNull()
            ->and($user->avatar_path)->toBeNull();
    });

    it('creates user with marketing opt-out when set', function () {
        Queue::fake();

        $service = new ProfileService;

        $user = $service->create(data: [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe-opted-out',
            'email' => 'john-opted-out@example.com',
            'marketing_opt_out_at' => now(),
        ]);

        expect($user->marketing_opt_out_at)->not->toBeNull()
            ->and($user->isSubscribedToMarketing())->toBeFalse();
    });

    it('creates user subscribed to marketing by default', function () {
        Queue::fake();

        $service = new ProfileService;

        $user = $service->create(data: [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe-subscribed',
            'email' => 'john-subscribed@example.com',
        ]);

        expect($user->marketing_opt_out_at)->toBeNull()
            ->and($user->isSubscribedToMarketing())->toBeTrue();
    });

    it('calls onEmailSet hook when user is created', function () {
        Queue::fake();

        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('onEmailSet')
            ->once()
            ->withArgs(fn (User $user) => $user->email === 'hook-test@example.com');

        $service->create(data: [
            'first_name' => 'Hook',
            'last_name' => 'Test',
            'handle' => 'hook-test-email',
            'email' => 'hook-test@example.com',
        ]);
    });

    it('does not call onMarketingPreferenceSet hook when user opted out', function () {
        Queue::fake();

        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('onEmailSet')->once();
        $service->shouldNotReceive('onMarketingPreferenceSet');

        $service->create(data: [
            'first_name' => 'OptedOut',
            'last_name' => 'Test',
            'handle' => 'opted-out-test',
            'email' => 'opted-out-test@example.com',
            'marketing_opt_out_at' => now(),
        ]);
    });

    it('creates user without email verified by default', function () {
        Queue::fake();

        $service = new ProfileService;

        $user = $service->create(data: [
            'first_name' => 'Unverified',
            'last_name' => 'User',
            'handle' => 'unverified-user',
            'email' => 'unverified@example.com',
        ]);

        expect($user->email_verified_at)->toBeNull();
    });

    it('creates user with email verified when emailVerified is true', function () {
        Queue::fake();

        $service = new ProfileService;

        $user = $service->create(
            data: [
                'first_name' => 'Verified',
                'last_name' => 'User',
                'handle' => 'verified-user',
                'email' => 'verified@example.com',
            ],
            emailVerified: true,
        );

        expect($user->email_verified_at)->not->toBeNull();
    });
});

describe('update', function () {
    it('updates user profile data', function () {
        Queue::fake();

        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $service = new ProfileService;
        $updatedUser = $service->update(user: $user, data: [
            'first_name' => 'Jane',
            'organisation' => 'New Company',
        ]);

        expect($updatedUser->first_name)->toBe('Jane')
            ->and($updatedUser->last_name)->toBe('Doe')
            ->and($updatedUser->organisation)->toBe('New Company');
    });

    it('filters out non-profile fields during update', function () {
        Queue::fake();

        $user = User::factory()->create();

        $service = new ProfileService;
        $service->update(user: $user, data: [
            'first_name' => 'Updated',
            'password' => 'should-not-change',
            'team_type' => 'CoreTeam',
        ]);

        $user->refresh();

        expect($user->first_name)->toBe('Updated')
            ->and($user->team_type)->toBeNull();
    });

    it('calls onEmailChanged hook when email changes', function () {
        Queue::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
        ]);

        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('onEmailChanged')
            ->once()
            ->withArgs(fn (User $u) => $u->email === 'new@example.com');

        $service->update(user: $user, data: [
            'email' => 'new@example.com',
        ]);

        expect($user->email)->toBe('new@example.com');
    });

    it('does not call onEmailChanged hook when email stays the same', function () {
        Queue::fake();

        $user = User::factory()->create([
            'email' => 'same@example.com',
        ]);

        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldNotReceive('onEmailChanged');

        $service->update(user: $user, data: [
            'first_name' => 'Updated',
            'email' => 'same@example.com',
        ]);
    });

    it('calls onMarketingPreferenceChanged hook when opting out', function () {
        Queue::fake();

        $user = User::factory()->create([
            'marketing_opt_out_at' => null,
        ]);

        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('onMarketingPreferenceChanged')
            ->once()
            ->withArgs(fn (User $u, bool $wasSubscribed) => $wasSubscribed === true && $u->isSubscribedToMarketing() === false);

        $service->update(user: $user, data: [
            'marketing_opt_out_at' => now(),
        ]);

        expect($user->isSubscribedToMarketing())->toBeFalse();
    });

    it('calls onMarketingPreferenceChanged hook when opting back in', function () {
        Queue::fake();

        $user = User::factory()->create([
            'marketing_opt_out_at' => now(),
        ]);

        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('onMarketingPreferenceChanged')
            ->once()
            ->withArgs(fn (User $u, bool $wasSubscribed) => $wasSubscribed === false && $u->isSubscribedToMarketing() === true);

        $service->update(user: $user, data: [
            'marketing_opt_out_at' => null,
        ]);

        expect($user->isSubscribedToMarketing())->toBeTrue();
    });

    it('does not call onMarketingPreferenceChanged when preference unchanged', function () {
        Queue::fake();

        $optOutTime = now()->subDay();
        $user = User::factory()->create([
            'marketing_opt_out_at' => $optOutTime,
        ]);

        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldNotReceive('onMarketingPreferenceChanged');

        $service->update(user: $user, data: [
            'first_name' => 'Updated',
        ]);

        expect($user->marketing_opt_out_at->toDateTimeString())->toBe($optOutTime->toDateTimeString());
    });
});

describe('job dispatching', function () {
    describe('with verified email', function () {
        it('dispatches CreateExternalSubscriberJob when user is created and subscribed to marketing', function () {
            Queue::fake();

            $service = new ProfileService;

            $user = $service->create(
                data: [
                    'first_name' => 'Subscriber',
                    'last_name' => 'Test',
                    'handle' => 'subscriber-test',
                    'email' => 'subscriber@example.com',
                ],
                emailVerified: true,
            );

            Queue::assertPushed(CreateExternalSubscriberJob::class, function (CreateExternalSubscriberJob $job) use ($user) {
                return $job->user->is($user);
            });
        });

        it('does not dispatch CreateExternalSubscriberJob when user opts out at creation', function () {
            Queue::fake();

            $service = new ProfileService;

            $service->create(
                data: [
                    'first_name' => 'OptedOut',
                    'last_name' => 'Test',
                    'handle' => 'opted-out-no-subscriber',
                    'email' => 'opted-out-no-subscriber@example.com',
                    'marketing_opt_out_at' => now(),
                ],
                emailVerified: true,
            );

            Queue::assertNotPushed(CreateExternalSubscriberJob::class);
        });

        it('dispatches UpdateExternalSubscriberJob when user email changes', function () {
            Queue::fake();

            $user = User::factory()->create([
                'email' => 'old-email@example.com',
                'email_verified_at' => now(),
                'external_subscriber_uuid' => '22222222-2222-2222-2222-222222222222',
            ]);

            $service = new ProfileService;

            $service->update(user: $user, data: [
                'email' => 'new-email@example.com',
            ]);

            Queue::assertPushed(UpdateExternalSubscriberJob::class, function (UpdateExternalSubscriberJob $job) use ($user) {
                return $job->user->is($user);
            });
        });

        it('dispatches UpdateExternalSubscriberJob even when user has no subscriber uuid', function () {
            Queue::fake();

            $user = User::factory()->create([
                'email' => 'old-email@example.com',
                'email_verified_at' => now(),
                'external_subscriber_uuid' => null,
            ]);

            $service = new ProfileService;

            $service->update(user: $user, data: [
                'email' => 'new-email@example.com',
            ]);

            Queue::assertPushed(UpdateExternalSubscriberJob::class, function (UpdateExternalSubscriberJob $job) use ($user) {
                return $job->user->is($user);
            });
        });

        it('dispatches UnsubscribeFromMarketingJob when opting out', function () {
            Queue::fake();

            $user = User::factory()->create([
                'email_verified_at' => now(),
                'marketing_opt_out_at' => null,
                'external_subscriber_uuid' => '33333333-3333-3333-3333-333333333333',
            ]);

            $service = new ProfileService;

            $service->update(user: $user, data: [
                'marketing_opt_out_at' => now(),
            ]);

            Queue::assertPushed(UnsubscribeFromMarketingJob::class, function (UnsubscribeFromMarketingJob $job) use ($user) {
                return $job->user->is($user);
            });
        });

        it('dispatches ResubscribeToMarketingJob when opting back in with existing subscriber', function () {
            Queue::fake();

            $user = User::factory()->create([
                'email_verified_at' => now(),
                'marketing_opt_out_at' => now(),
                'external_subscriber_uuid' => '44444444-4444-4444-4444-444444444444',
            ]);

            $service = new ProfileService;

            $service->update(user: $user, data: [
                'marketing_opt_out_at' => null,
            ]);

            Queue::assertPushed(ResubscribeToMarketingJob::class, function (ResubscribeToMarketingJob $job) use ($user) {
                return $job->user->is($user);
            });
        });

        it('dispatches CreateExternalSubscriberJob when opting back in without existing subscriber', function () {
            Queue::fake();

            $user = User::factory()->create([
                'email_verified_at' => now(),
                'marketing_opt_out_at' => now(),
                'external_subscriber_uuid' => null,
            ]);

            $service = new ProfileService;

            $service->update(user: $user, data: [
                'marketing_opt_out_at' => null,
            ]);

            Queue::assertPushed(CreateExternalSubscriberJob::class, function (CreateExternalSubscriberJob $job) use ($user) {
                return $job->user->is($user);
            });
        });
    });

    describe('with unverified email', function () {
        it('does not dispatch CreateExternalSubscriberJob when user is created', function () {
            Queue::fake();

            $service = new ProfileService;

            $service->create(data: [
                'first_name' => 'Unverified',
                'last_name' => 'Test',
                'handle' => 'unverified-subscriber',
                'email' => 'unverified-subscriber@example.com',
            ]);

            Queue::assertNotPushed(CreateExternalSubscriberJob::class);
        });

        it('does not dispatch UpdateExternalSubscriberJob when email changes', function () {
            Queue::fake();

            $user = User::factory()->create([
                'email' => 'old-email@example.com',
                'email_verified_at' => null,
                'external_subscriber_uuid' => '22222222-2222-2222-2222-222222222222',
            ]);

            $service = new ProfileService;

            $service->update(user: $user, data: [
                'email' => 'new-email@example.com',
            ]);

            Queue::assertNotPushed(UpdateExternalSubscriberJob::class);
        });

        it('still dispatches UnsubscribeFromMarketingJob when opting out', function () {
            Queue::fake();

            $user = User::factory()->create([
                'email_verified_at' => null,
                'marketing_opt_out_at' => null,
                'external_subscriber_uuid' => '33333333-3333-3333-3333-333333333333',
            ]);

            $service = new ProfileService;

            $service->update(user: $user, data: [
                'marketing_opt_out_at' => now(),
            ]);

            Queue::assertPushed(UnsubscribeFromMarketingJob::class, function (UnsubscribeFromMarketingJob $job) use ($user) {
                return $job->user->is($user);
            });
        });

        it('still dispatches ResubscribeToMarketingJob when opting back in', function () {
            Queue::fake();

            $user = User::factory()->create([
                'email_verified_at' => null,
                'marketing_opt_out_at' => now(),
                'external_subscriber_uuid' => '44444444-4444-4444-4444-444444444444',
            ]);

            $service = new ProfileService;

            $service->update(user: $user, data: [
                'marketing_opt_out_at' => null,
            ]);

            Queue::assertPushed(ResubscribeToMarketingJob::class, function (ResubscribeToMarketingJob $job) use ($user) {
                return $job->user->is($user);
            });
        });

        it('does not dispatch CreateExternalSubscriberJob when opting back in without existing subscriber', function () {
            Queue::fake();

            $user = User::factory()->create([
                'email_verified_at' => null,
                'marketing_opt_out_at' => now(),
                'external_subscriber_uuid' => null,
            ]);

            $service = new ProfileService;

            $service->update(user: $user, data: [
                'marketing_opt_out_at' => null,
            ]);

            Queue::assertNotPushed(CreateExternalSubscriberJob::class);
        });
    });
});
