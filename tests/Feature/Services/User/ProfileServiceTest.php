<?php

use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use App\Services\User\ProfileService;

function mockRecipientService(): RecipientService
{
    $mock = Mockery::mock(RecipientService::class);
    $mock->shouldReceive('createRecipient')->andReturn('00000000-0000-0000-0000-000000000001');
    $mock->shouldReceive('updateRecipient');
    $mock->shouldReceive('unsubscribeRecipient');
    $mock->shouldReceive('resubscribeRecipient');

    return $mock;
}

describe('create', function () {
    it('creates a user with profile data', function () {
        $service = new ProfileService(recipientService: mockRecipientService());

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
        $service = new ProfileService(recipientService: mockRecipientService());

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
        $service = new ProfileService(recipientService: mockRecipientService());

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
        $service = new ProfileService(recipientService: mockRecipientService());

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
        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class, [mockRecipientService()])
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
        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class, [mockRecipientService()])
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
});

describe('update', function () {
    it('updates user profile data', function () {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $service = new ProfileService(recipientService: mockRecipientService());
        $updatedUser = $service->update(user: $user, data: [
            'first_name' => 'Jane',
            'organisation' => 'New Company',
        ]);

        expect($updatedUser->first_name)->toBe('Jane')
            ->and($updatedUser->last_name)->toBe('Doe')
            ->and($updatedUser->organisation)->toBe('New Company');
    });

    it('filters out non-profile fields during update', function () {
        $user = User::factory()->create();

        $service = new ProfileService(recipientService: mockRecipientService());
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
        $user = User::factory()->create([
            'email' => 'old@example.com',
        ]);

        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class, [mockRecipientService()])
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
        $user = User::factory()->create([
            'email' => 'same@example.com',
        ]);

        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class, [mockRecipientService()])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldNotReceive('onEmailChanged');

        $service->update(user: $user, data: [
            'first_name' => 'Updated',
            'email' => 'same@example.com',
        ]);
    });

    it('calls onMarketingPreferenceChanged hook when opting out', function () {
        $user = User::factory()->create([
            'marketing_opt_out_at' => null,
        ]);

        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class, [mockRecipientService()])
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
        $user = User::factory()->create([
            'marketing_opt_out_at' => now(),
        ]);

        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class, [mockRecipientService()])
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
        $optOutTime = now()->subDay();
        $user = User::factory()->create([
            'marketing_opt_out_at' => $optOutTime,
        ]);

        /** @var ProfileService&\Mockery\MockInterface $service */
        $service = Mockery::mock(ProfileService::class, [mockRecipientService()])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldNotReceive('onMarketingPreferenceChanged');

        $service->update(user: $user, data: [
            'first_name' => 'Updated',
        ]);

        expect($user->marketing_opt_out_at->toDateTimeString())->toBe($optOutTime->toDateTimeString());
    });
});

describe('recipient service integration', function () {
    it('creates subscriber when user is created and subscribed to marketing', function () {
        $subscriberUuid = '11111111-1111-1111-1111-111111111111';

        $recipientService = Mockery::mock(RecipientService::class);
        $recipientService->shouldReceive('createRecipient')
            ->once()
            ->andReturn($subscriberUuid);

        $service = new ProfileService(recipientService: $recipientService);

        $user = $service->create(data: [
            'first_name' => 'Subscriber',
            'last_name' => 'Test',
            'handle' => 'subscriber-test',
            'email' => 'subscriber@example.com',
        ]);

        expect($user->external_subscriber_uuid)->toBe($subscriberUuid);
    });

    it('does not create subscriber when user opts out at creation', function () {
        $recipientService = Mockery::mock(RecipientService::class);
        $recipientService->shouldNotReceive('createRecipient');

        $service = new ProfileService(recipientService: $recipientService);

        $user = $service->create(data: [
            'first_name' => 'OptedOut',
            'last_name' => 'Test',
            'handle' => 'opted-out-no-subscriber',
            'email' => 'opted-out-no-subscriber@example.com',
            'marketing_opt_out_at' => now(),
        ]);

        expect($user->external_subscriber_uuid)->toBeNull();
    });

    it('updates subscriber email when user email changes', function () {
        $existingUuid = '22222222-2222-2222-2222-222222222222';

        $user = User::factory()->create([
            'email' => 'old-email@example.com',
            'external_subscriber_uuid' => $existingUuid,
        ]);

        $recipientService = Mockery::mock(RecipientService::class);
        $recipientService->shouldReceive('updateRecipient')
            ->once()
            ->withArgs(fn (string $externalId, $data) => $externalId === $existingUuid && $data->email === 'new-email@example.com');

        $service = new ProfileService(recipientService: $recipientService);

        $service->update(user: $user, data: [
            'email' => 'new-email@example.com',
        ]);
    });

    it('does not update subscriber email when user has no subscriber uuid', function () {
        $user = User::factory()->create([
            'email' => 'old-email@example.com',
            'external_subscriber_uuid' => null,
        ]);

        $recipientService = Mockery::mock(RecipientService::class);
        $recipientService->shouldNotReceive('updateRecipient');

        $service = new ProfileService(recipientService: $recipientService);

        $service->update(user: $user, data: [
            'email' => 'new-email@example.com',
        ]);
    });

    it('unsubscribes user when opting out', function () {
        $existingUuid = '33333333-3333-3333-3333-333333333333';

        $user = User::factory()->create([
            'marketing_opt_out_at' => null,
            'external_subscriber_uuid' => $existingUuid,
        ]);

        $recipientService = Mockery::mock(RecipientService::class);
        $recipientService->shouldReceive('unsubscribeRecipient')
            ->once()
            ->with($existingUuid);

        $service = new ProfileService(recipientService: $recipientService);

        $service->update(user: $user, data: [
            'marketing_opt_out_at' => now(),
        ]);
    });

    it('resubscribes user when opting back in with existing subscriber', function () {
        $existingUuid = '44444444-4444-4444-4444-444444444444';

        $user = User::factory()->create([
            'marketing_opt_out_at' => now(),
            'external_subscriber_uuid' => $existingUuid,
        ]);

        $recipientService = Mockery::mock(RecipientService::class);
        $recipientService->shouldReceive('resubscribeRecipient')
            ->once()
            ->with($existingUuid);

        $service = new ProfileService(recipientService: $recipientService);

        $service->update(user: $user, data: [
            'marketing_opt_out_at' => null,
        ]);
    });

    it('creates subscriber when opting back in without existing subscriber', function () {
        $newSubscriberUuid = '55555555-5555-5555-5555-555555555555';

        $user = User::factory()->create([
            'marketing_opt_out_at' => now(),
            'external_subscriber_uuid' => null,
        ]);

        $recipientService = Mockery::mock(RecipientService::class);
        $recipientService->shouldReceive('createRecipient')
            ->once()
            ->andReturn($newSubscriberUuid);

        $service = new ProfileService(recipientService: $recipientService);

        $service->update(user: $user, data: [
            'marketing_opt_out_at' => null,
        ]);

        expect($user->external_subscriber_uuid)->toBe($newSubscriberUuid);
    });
});
