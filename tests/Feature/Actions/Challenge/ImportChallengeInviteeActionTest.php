<?php

use App\Actions\Challenge\AcceptChallengeInviteCodeAction;
use App\Actions\Challenge\ImportChallengeInviteeAction;
use App\Exceptions\SkippedImportRowException;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;
use App\Notifications\Challenge\ChallengeInvitation;
use Illuminate\Support\Facades\Notification;

function importInvitee(ChallengeInviteCode $inviteCode, array $row, ?string $message = null): void
{
    app(ImportChallengeInviteeAction::class)->import(
        inviteCode: $inviteCode,
        row: $row,
        customMessage: $message,
    );
}

test('creates a new user, attaches them and sends an invitation with a password token', function () {
    Notification::fake();
    $inviteCode = ChallengeInviteCode::factory()->create();

    importInvitee($inviteCode, [
        'email' => 'jane@example.com',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'organisation' => 'Acme',
        'job_title' => 'Counsel',
        'linkedin_url' => 'https://linkedin.com/in/jane',
        'bio' => 'Hello',
    ]);

    $user = User::query()->where('email', 'jane@example.com')->sole();

    expect($user->first_name)->toBe('Jane')
        ->and($user->last_name)->toBe('Doe')
        ->and($user->organisation)->toBe('Acme')
        ->and($user->job_title)->toBe('Counsel')
        ->and($user->linkedin_url)->toBe('https://linkedin.com/in/jane')
        ->and($user->bio)->toBe('Hello')
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($inviteCode->users()->whereKey($user->id)->exists())->toBeTrue();

    Notification::assertSentTo(
        $user,
        ChallengeInvitation::class,
        fn (ChallengeInvitation $n) => $n->isNewUser === true && $n->passwordToken !== null
    );
});

test('reuses an existing user without a password token and applies the custom message', function () {
    Notification::fake();
    $inviteCode = ChallengeInviteCode::factory()->create();
    /** @var User $existing */
    $existing = User::factory()->create(['email' => 'existing@example.com']);

    importInvitee($inviteCode, [
        'email' => 'existing@example.com',
        'first_name' => 'Existing',
        'last_name' => 'User',
    ], 'See you there');

    expect(User::query()->where('email', 'existing@example.com')->count())->toBe(1)
        ->and($inviteCode->users()->whereKey($existing->id)->exists())->toBeTrue();

    Notification::assertSentTo(
        $existing,
        ChallengeInvitation::class,
        fn (ChallengeInvitation $n) => $n->isNewUser === false
            && $n->passwordToken === null
            && $n->customMessage === 'See you there'
    );
});

test('matches an existing user when the email differs only by case', function () {
    Notification::fake();
    $inviteCode = ChallengeInviteCode::factory()->create();
    /** @var User $existing */
    $existing = User::factory()->create(['email' => 'person@example.com']);

    importInvitee($inviteCode, [
        'email' => 'Person@Example.COM',
        'first_name' => 'Person',
        'last_name' => 'User',
    ]);

    expect(User::query()->where('email', 'person@example.com')->count())->toBe(1)
        ->and($inviteCode->users()->whereKey($existing->id)->exists())->toBeTrue();

    Notification::assertSentTo(
        $existing,
        ChallengeInvitation::class,
        fn (ChallengeInvitation $n) => $n->isNewUser === false
    );
});

test('skips and does not email an existing user who already has sufficient access via another code', function () {
    Notification::fake();
    $challenge = Challenge::factory()->create();
    $existingCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();
    $importCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();
    /** @var User $existing */
    $existing = User::factory()->create(['email' => 'existing@example.com']);

    app(AcceptChallengeInviteCodeAction::class)->accept(inviteCode: $existingCode, user: $existing);

    expect(fn () => importInvitee($importCode, [
        'email' => 'existing@example.com',
        'first_name' => 'Existing',
        'last_name' => 'User',
    ]))->toThrow(SkippedImportRowException::class, 'Already has access to this challenge.');

    expect($importCode->users()->whereKey($existing->id)->exists())->toBeFalse();
    Notification::assertNothingSent();
});

test('still imports and emails an existing user being upgraded from view to view and submit', function () {
    Notification::fake();
    $challenge = Challenge::factory()->create();
    $viewCode = ChallengeInviteCode::factory()->forChallenge($challenge)->viewOnly()->create();
    $viewAndSubmitCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();
    /** @var User $existing */
    $existing = User::factory()->create(['email' => 'existing@example.com']);

    app(AcceptChallengeInviteCodeAction::class)->accept(inviteCode: $viewCode, user: $existing);

    importInvitee($viewAndSubmitCode, [
        'email' => 'existing@example.com',
        'first_name' => 'Existing',
        'last_name' => 'User',
    ]);

    expect($viewAndSubmitCode->users()->whereKey($existing->id)->exists())->toBeTrue()
        ->and($viewCode->users()->whereKey($existing->id)->exists())->toBeFalse();

    Notification::assertSentTo($existing, ChallengeInvitation::class);
});

test('throws a skipped row exception for invalid rows', function (array $row) {
    Notification::fake();
    $inviteCode = ChallengeInviteCode::factory()->create();

    expect(fn () => importInvitee($inviteCode, $row))
        ->toThrow(SkippedImportRowException::class);

    expect(User::query()->count())->toBe(0);
    Notification::assertNothingSent();
})->with([
    'missing email' => [['first_name' => 'A', 'last_name' => 'B']],
    'invalid email' => [['email' => 'not-an-email', 'first_name' => 'A', 'last_name' => 'B']],
    'missing first name' => [['email' => 'a@example.com', 'last_name' => 'B']],
    'missing last name' => [['email' => 'a@example.com', 'first_name' => 'A']],
    'invalid linkedin url' => [['email' => 'a@example.com', 'first_name' => 'A', 'last_name' => 'B', 'linkedin_url' => 'not-a-url']],
]);
