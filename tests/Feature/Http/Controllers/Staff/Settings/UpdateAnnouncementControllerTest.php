<?php

use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Markdown\MarkdownService;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        patch(route('staff.settings.update-announcement'), [
            'announcement' => 'Test',
        ])->assertRedirect(route('login'));
    });

    test('allows admin to update announcement', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        patch(route('staff.settings.update-announcement'), [
            'announcement' => 'Test',
        ])->assertRedirect();
    });

    test('does not allow moderators to update announcement', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        patch(route('staff.settings.update-announcement'), [
            'announcement' => 'Test',
        ])->assertForbidden();
    });

    test('does not allow regular users to update announcement', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        patch(route('staff.settings.update-announcement'), [
            'announcement' => 'Test',
        ])->assertForbidden();
    });
});

describe('update', function () {
    test('saves an announcement and redirects back', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        patch(route('staff.settings.update-announcement'), [
            'announcement' => 'We have a **new feature**!',
        ])->assertRedirect();

        $setting = SiteSetting::query()
            ->where('key', SiteSetting::ANNOUNCEMENT)
            ->first();

        expect($setting)->not->toBeNull()
            ->and($setting->value)->toBe('We have a **new feature**!');
    });

    test('updates an existing announcement', function () {
        $admin = User::factory()->admin()->create();
        SiteSetting::factory()->announcement(value: 'Old announcement')->create();

        actingAs($admin);

        patch(route('staff.settings.update-announcement'), [
            'announcement' => 'New announcement',
        ])->assertRedirect();

        $setting = SiteSetting::query()
            ->where('key', SiteSetting::ANNOUNCEMENT)
            ->first();

        expect($setting->value)->toBe('New announcement');
    });

    test('clears the announcement when null is sent', function () {
        $admin = User::factory()->admin()->create();
        SiteSetting::factory()->announcement(value: 'To be removed')->create();

        actingAs($admin);

        patch(route('staff.settings.update-announcement'), [
            'announcement' => null,
        ])->assertRedirect();

        expect(SiteSetting::query()->where('key', SiteSetting::ANNOUNCEMENT)->exists())->toBeFalse();
    });

    test('clears the announcement when empty string is sent', function () {
        $admin = User::factory()->admin()->create();
        SiteSetting::factory()->announcement(value: 'To be removed')->create();

        actingAs($admin);

        patch(route('staff.settings.update-announcement'), [
            'announcement' => '',
        ])->assertRedirect();

        expect(SiteSetting::query()->where('key', SiteSetting::ANNOUNCEMENT)->exists())->toBeFalse();
    });

    test('clears the cached rendered announcement when updated', function () {
        $admin = User::factory()->admin()->create();
        SiteSetting::factory()->announcement(value: 'Old announcement')->create();

        $cacheKey = app(MarkdownService::class)->getCacheKey(cacheKey: 'site-announcement');
        Cache::forever($cacheKey, '<p>stale rendered html</p>');

        actingAs($admin);

        patch(route('staff.settings.update-announcement'), [
            'announcement' => 'Visit [our site](https://example.com)!',
        ])->assertRedirect();

        expect(Cache::has($cacheKey))->toBeFalse();
    });
});

describe('validation', function () {
    test('validates announcement max length', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        patch(route('staff.settings.update-announcement'), [
            'announcement' => str_repeat('a', 1001),
        ])->assertSessionHasErrors(['announcement']);
    });
});
