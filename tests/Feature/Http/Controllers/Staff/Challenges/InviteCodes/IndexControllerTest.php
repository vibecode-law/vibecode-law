<?php

use App\Enums\ChallengeInviteCodeImportStatus;
use App\Enums\InviteCodeScope;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\Challenge\ChallengeInviteCodeImport;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();

        get(route('staff.challenges.invite-codes.index', $challenge))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view invite codes', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin)
            ->get(route('staff.challenges.invite-codes.index', $challenge))
            ->assertOk();
    });

    test('does not allow regular users to view invite codes', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        actingAs($user)
            ->get(route('staff.challenges.invite-codes.index', $challenge))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns invite codes with correct data structure', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->inviteToSubmit()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create([
            'scope' => InviteCodeScope::ViewAndSubmit,
        ]);

        /** @var User */
        $acceptedUser = User::factory()->create();
        $inviteCode->users()->attach($acceptedUser);

        $import = ChallengeInviteCodeImport::factory()->completed()->create([
            'challenge_invite_code_id' => $inviteCode->id,
            'total_rows' => 5,
            'imported_count' => 4,
            'skipped_count' => 1,
            'skipped_rows' => [
                ['row' => 3, 'email' => 'bad@', 'reason' => 'Invalid email.'],
            ],
        ]);

        actingAs($admin)
            ->get(route('staff.challenges.invite-codes.index', $challenge))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/challenges/invite-codes/index', shouldExist: false)
                ->has('challenge', fn (AssertableInertia $c) => $c
                    ->where('id', $challenge->id)
                    ->where('slug', $challenge->slug)
                    ->where('title', $challenge->title)
                    ->where('visibility', $challenge->visibility->value)
                )
                ->has('inviteCodes', 1)
                ->has('inviteCodes.0', fn (AssertableInertia $ic) => $ic
                    ->where('id', $inviteCode->id)
                    ->where('code', $inviteCode->code)
                    ->where('label', $inviteCode->label)
                    ->where('scope', InviteCodeScope::ViewAndSubmit->value)
                    ->where('is_active', true)
                    ->where('users_count', 1)
                    ->has('created_at')
                )
                ->has('scopeOptions', count(InviteCodeScope::cases()))
                ->has('scopeOptions.0', fn (AssertableInertia $so) => $so
                    ->where('value', (string) InviteCodeScope::View->value)
                    ->where('label', InviteCodeScope::View->label())
                    ->has('name')
                )
                ->has('recentImports', 1)
                ->has('recentImports.0', fn (AssertableInertia $ri) => $ri
                    ->where('id', $import->id)
                    ->where('challenge_invite_code_id', $inviteCode->id)
                    ->where('status', ChallengeInviteCodeImportStatus::Completed->value)
                    ->where('total_rows', 5)
                    ->where('imported_count', 4)
                    ->where('skipped_count', 1)
                    ->has('skipped_rows', 1)
                    ->has('skipped_rows.0', fn (AssertableInertia $sr) => $sr
                        ->where('row', 3)
                        ->where('email', 'bad@')
                        ->where('reason', 'Invalid email.')
                    )
                    ->has('created_at')
                )
            );
    });

    test('returns empty invite codes when none exist', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin)
            ->get(route('staff.challenges.invite-codes.index', $challenge))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('inviteCodes', 0)
                ->has('recentImports', 0)
            );
    });
});
