<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Jobs\MarketingEmail\CreateExternalSubscriberJob;
use App\Jobs\MarketingEmail\UpdateExternalSubscriberJob;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertOk();
    }

    public function test_email_can_be_verified()
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('home', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_is_not_verified_with_invalid_user_id(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => 123, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verified_user_is_redirected_to_dashboard_from_verification_prompt(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_already_verified_user_visiting_verification_link_is_redirected_without_firing_event_again(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($verificationUrl)
            ->assertRedirect(route('home', absolute: false).'?verified=1');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        Event::assertNotDispatched(Verified::class);
    }

    public function test_verification_dispatches_create_external_subscriber_job_when_subscribed_to_marketing(): void
    {
        Queue::fake();

        $user = User::factory()->unverified()->create([
            'marketing_opt_out_at' => null,
            'external_subscriber_uuid' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($verificationUrl);

        Queue::assertPushed(CreateExternalSubscriberJob::class, function (CreateExternalSubscriberJob $job) use ($user) {
            return $job->user->is($user);
        });
    }

    public function test_verification_dispatches_update_external_subscriber_job_when_subscriber_exists(): void
    {
        Queue::fake();

        $user = User::factory()->unverified()->create([
            'marketing_opt_out_at' => null,
            'external_subscriber_uuid' => '11111111-1111-1111-1111-111111111111',
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($verificationUrl);

        Queue::assertPushed(UpdateExternalSubscriberJob::class, function (UpdateExternalSubscriberJob $job) use ($user) {
            return $job->user->is($user);
        });
        Queue::assertNotPushed(CreateExternalSubscriberJob::class);
    }

    public function test_verification_does_not_dispatch_create_job_when_opted_out_of_marketing(): void
    {
        Queue::fake();

        $user = User::factory()->unverified()->create([
            'marketing_opt_out_at' => now(),
            'external_subscriber_uuid' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($verificationUrl);

        Queue::assertNotPushed(CreateExternalSubscriberJob::class);
        Queue::assertNotPushed(UpdateExternalSubscriberJob::class);
    }
}
