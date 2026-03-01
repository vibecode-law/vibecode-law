<?php

use App\Enums\InviteCodeScope;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();

        post(route('staff.challenges.invite-codes.store', $challenge), [
            'label' => 'Test Code',
            'scope' => InviteCodeScope::ViewAndSubmit->value,
        ])->assertRedirect(route('login'));
    });

    test('allows admin to create invite codes', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin)
            ->post(route('staff.challenges.invite-codes.store', $challenge), [
                'label' => 'Test Code',
                'scope' => InviteCodeScope::ViewAndSubmit->value,
            ])
            ->assertRedirect();
    });

    test('does not allow regular users to create invite codes', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        actingAs($user)
            ->post(route('staff.challenges.invite-codes.store', $challenge), [
                'label' => 'Test Code',
                'scope' => InviteCodeScope::ViewAndSubmit->value,
            ])
            ->assertForbidden();
    });
});

describe('store', function () {
    test('creates invite code with correct data', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin)
            ->post(route('staff.challenges.invite-codes.store', $challenge), [
                'label' => 'Stanford Law',
                'scope' => InviteCodeScope::View->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'Invite code created successfully.',
                'type' => 'success',
            ]);

        $inviteCode = ChallengeInviteCode::query()
            ->where('challenge_id', $challenge->id)
            ->firstOrFail();

        expect($inviteCode->label)->toBe('Stanford Law')
            ->and($inviteCode->scope)->toBe(InviteCodeScope::View)
            ->and($inviteCode->is_active)->toBeTrue()
            ->and($inviteCode->code)->toHaveLength(16);
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin)
            ->post(route('staff.challenges.invite-codes.store', $challenge), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing label' => [
            ['scope' => InviteCodeScope::ViewAndSubmit->value],
            ['label'],
        ],
        'missing scope' => [
            ['label' => 'Test Code'],
            ['scope'],
        ],
        'invalid scope value' => [
            ['label' => 'Test Code', 'scope' => 999],
            ['scope'],
        ],
    ]);
});
