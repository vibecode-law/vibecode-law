<?php

use App\Enums\ShowcaseStatus;
use App\Models\Showcase\Showcase;
use App\Models\User;
use App\Notifications\Showcase\ShowcaseApproved;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $showcase = Showcase::factory()->pending()->create();

        $response = post(route('staff.showcase-moderation.approve', $showcase));

        $response->assertRedirect(route('login'));
    });

    test('requires admin privileges', function () {
        /** @var User */
        $regularUser = User::factory()->create();
        $showcase = Showcase::factory()->pending()->create();

        actingAs($regularUser);

        $response = post(route('staff.showcase-moderation.approve', $showcase));

        $response->assertForbidden();
    });

    test('allows admin to approve showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->pending()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.approve', $showcase));

        $response->assertRedirect();
    });

    test('forbids manager roles without showcase.approve-reject', function () {
        $showcase = Showcase::factory()->pending()->create();

        foreach (['marketingManager', 'academyManager', 'challengeManager'] as $state) {
            $user = User::factory()->{$state}()->create();
            actingAs($user);

            post(route('staff.showcase-moderation.approve', $showcase))
                ->assertForbidden();
        }
    });
});

describe('approval', function () {
    test('approves pending showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->pending()->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.approve', $showcase));

        $showcase->refresh();

        expect($showcase->status)->toBe(ShowcaseStatus::Approved);
        expect($showcase->approved_at)->not->toBeNull();
        expect($showcase->approved_by)->toBe($admin->id);
    });

    test('approves draft showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->draft()->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.approve', $showcase));

        $showcase->refresh();

        expect($showcase->status)->toBe(ShowcaseStatus::Approved);
        expect($showcase->approved_at)->not->toBeNull();
        expect($showcase->approved_by)->toBe($admin->id);
    });

    test('approves rejected showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->rejected()->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.approve', $showcase));

        $showcase->refresh();

        expect($showcase->status)->toBe(ShowcaseStatus::Approved);
        expect($showcase->approved_at)->not->toBeNull();
        expect($showcase->approved_by)->toBe($admin->id);
    });

    test('clears rejection reason when approving rejected showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->rejected()->create([
            'rejection_reason' => 'Previous rejection reason',
        ]);

        actingAs($admin);

        post(route('staff.showcase-moderation.approve', $showcase));

        $showcase->refresh();

        expect($showcase->rejection_reason)->toBeNull();
    });
});

describe('notification', function () {
    test('sends notification to showcase owner', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->pending()->for($owner, 'user')->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.approve', $showcase));

        Notification::assertSentTo($owner, ShowcaseApproved::class);
    });

    test('notification contains correct showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->pending()->for($owner, 'user')->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.approve', $showcase));

        Notification::assertSentTo(
            $owner,
            function (ShowcaseApproved $notification) use ($showcase) {
                return $notification->showcase->id === $showcase->id;
            }
        );
    });

    test('notification email contains linkedin share url', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->pending()->for($owner, 'user')->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.approve', $showcase));

        Notification::assertSentTo(
            $owner,
            function (ShowcaseApproved $notification) use ($owner, $showcase) {
                $mail = $notification->toMail($owner);
                $mailContent = $mail->render();
                $showcaseUrl = route('showcase.show', $showcase);
                $expectedLinkedInUrl = 'https://www.linkedin.com/sharing/share-offsite/?url='.urlencode($showcaseUrl);

                return str_contains($mailContent, $expectedLinkedInUrl);
            }
        );
    });
});

describe('response', function () {
    test('redirects back to previous page', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->pending()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.approve', $showcase));

        $response->assertRedirect();
    });

    test('includes success message in session', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->pending()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.approve', $showcase));

        $response->assertSessionHas('flash.message', ['message' => 'Showcase approved successfully.', 'type' => 'success']);
    });
});
