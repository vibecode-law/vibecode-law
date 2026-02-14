<?php

use App\Jobs\MarketingEmail\AddIsUserTagToSubscriberJob;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Config::set('marketing.is_user_tag_uuid', 'is-user-tag-uuid');
});

it('fails when is_user_tag_uuid config is not set', function () {
    Config::set('marketing.is_user_tag_uuid', null);

    $this->artisan('app:add-is-user-tag-to-subscribers')
        ->assertFailed()
        ->expectsOutputToContain('is not set');

    Queue::assertNothingPushed();
});

it('succeeds with info message when no subscribers exist', function () {
    $this->artisan('app:add-is-user-tag-to-subscribers')
        ->assertSuccessful()
        ->expectsOutputToContain('No subscribers to update');

    Queue::assertNothingPushed();
});

it('skips users without an external subscriber uuid', function () {
    User::factory()->create([
        'external_subscriber_uuid' => null,
    ]);

    $this->artisan('app:add-is-user-tag-to-subscribers')
        ->assertSuccessful()
        ->expectsOutputToContain('No subscribers to update');

    Queue::assertNothingPushed();
});

it('dispatches job for existing subscriber', function () {
    $user = User::factory()->create([
        'external_subscriber_uuid' => 'existing-uuid',
    ]);

    $this->artisan('app:add-is-user-tag-to-subscribers')
        ->assertSuccessful()
        ->expectsOutputToContain('Dispatched is_user tag jobs for 1 subscriber(s)');

    Queue::assertPushed(
        job: AddIsUserTagToSubscriberJob::class,
        callback: fn (AddIsUserTagToSubscriberJob $job) => $job->user->is($user),
    );
});

it('dispatches jobs for multiple subscribers', function () {
    User::factory()->count(3)->create([
        'external_subscriber_uuid' => 'uuid',
    ]);

    $this->artisan('app:add-is-user-tag-to-subscribers')
        ->assertSuccessful()
        ->expectsOutputToContain('Dispatched is_user tag jobs for 3 subscriber(s)');

    Queue::assertPushed(AddIsUserTagToSubscriberJob::class, 3);
});
