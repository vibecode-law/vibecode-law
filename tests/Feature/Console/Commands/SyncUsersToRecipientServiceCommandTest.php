<?php

use App\Jobs\MarketingEmail\CreateExternalSubscriberJob;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Config::set('marketing.has_showcase_tag_uuid', 'showcase-tag-uuid');
    Config::set('marketing.is_user_tag_uuid', 'is-user-tag-uuid');
});

it('fails if users already have subscriber uuids', function () {
    User::factory()->create([
        'email_verified_at' => now(),
        'external_subscriber_uuid' => 'existing-uuid',
    ]);

    $this->artisan('app:sync-marketing-recipients')
        ->assertFailed()
        ->expectsOutputToContain('Cannot sync');

    Queue::assertNothingPushed();
});

it('dispatches jobs for verified users without marketing opt out', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'marketing_opt_out_at' => null,
        'external_subscriber_uuid' => null,
    ]);

    $this->artisan('app:sync-marketing-recipients')
        ->assertSuccessful();

    Queue::assertPushed(
        job: CreateExternalSubscriberJob::class,
        callback: fn (CreateExternalSubscriberJob $job) => $job->user->is($user)
            && $job->tags === ['is-user-tag-uuid']
            && $job->skipConfirmation === true,
    );
});

it('does not dispatch jobs for unverified users', function () {
    User::factory()->create([
        'email_verified_at' => null,
        'marketing_opt_out_at' => null,
        'external_subscriber_uuid' => null,
    ]);

    $this->artisan('app:sync-marketing-recipients')
        ->assertSuccessful()
        ->expectsOutputToContain('No users to sync');

    Queue::assertNothingPushed();
});

it('does not dispatch jobs for users who opted out of marketing', function () {
    User::factory()->create([
        'email_verified_at' => now(),
        'marketing_opt_out_at' => now(),
        'external_subscriber_uuid' => null,
    ]);

    $this->artisan('app:sync-marketing-recipients')
        ->assertSuccessful()
        ->expectsOutputToContain('No users to sync');

    Queue::assertNothingPushed();
});

it('includes showcase tag for users with showcases', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'marketing_opt_out_at' => null,
        'external_subscriber_uuid' => null,
    ]);

    Showcase::factory()->for($user)->create();

    $this->artisan('app:sync-marketing-recipients')
        ->assertSuccessful();

    Queue::assertPushed(
        job: CreateExternalSubscriberJob::class,
        callback: fn (CreateExternalSubscriberJob $job) => $job->user->is($user)
            && $job->tags === ['is-user-tag-uuid', 'showcase-tag-uuid']
            && $job->skipConfirmation === true,
    );
});

it('does not include showcase tag when tag uuid is not configured', function () {
    Config::set('marketing.has_showcase_tag_uuid', null);

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'marketing_opt_out_at' => null,
        'external_subscriber_uuid' => null,
    ]);

    Showcase::factory()->for($user)->create();

    $this->artisan('app:sync-marketing-recipients')
        ->assertSuccessful();

    Queue::assertPushed(
        job: CreateExternalSubscriberJob::class,
        callback: fn (CreateExternalSubscriberJob $job) => $job->user->is($user)
            && $job->tags === ['is-user-tag-uuid']
            && $job->skipConfirmation === true,
    );
});

it('does not include is_user tag when is_user tag uuid is not configured', function () {
    Config::set('marketing.is_user_tag_uuid', null);

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'marketing_opt_out_at' => null,
        'external_subscriber_uuid' => null,
    ]);

    $this->artisan('app:sync-marketing-recipients')
        ->assertSuccessful();

    Queue::assertPushed(
        job: CreateExternalSubscriberJob::class,
        callback: fn (CreateExternalSubscriberJob $job) => $job->user->is($user)
            && $job->tags === []
            && $job->skipConfirmation === true,
    );
});

it('dispatches jobs for multiple eligible users', function () {
    User::factory()->count(3)->create([
        'email_verified_at' => now(),
        'marketing_opt_out_at' => null,
        'external_subscriber_uuid' => null,
    ]);

    $this->artisan('app:sync-marketing-recipients')
        ->assertSuccessful()
        ->expectsOutputToContain('Dispatched sync jobs for 3 user(s)');

    Queue::assertPushed(CreateExternalSubscriberJob::class, 3);
});
