<?php

use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;
use App\Notifications\Challenge\ChallengeInvitation;
use Illuminate\Notifications\Messages\MailMessage;

function invitationLines(MailMessage $mail): string
{
    return collect($mail->introLines)->merge($mail->outroLines)->implode(' ');
}

test('via uses the mail channel', function () {
    $inviteCode = ChallengeInviteCode::factory()->create();
    /** @var User $user */
    $user = User::factory()->create();

    expect((new ChallengeInvitation(inviteCode: $inviteCode, isNewUser: false))->via($user))
        ->toBe(['mail']);
});

test('new user mail includes a set password action and the custom message', function () {
    $inviteCode = ChallengeInviteCode::factory()->create();
    /** @var User $user */
    $user = User::factory()->create(['email' => 'new@example.com', 'first_name' => 'Newbie']);

    $mail = (new ChallengeInvitation(
        inviteCode: $inviteCode,
        isNewUser: true,
        passwordToken: 'tok-123',
        customMessage: 'A personal note',
    ))->toMail($user);

    $challengeUrl = route('inspiration.challenges.show', ['challenge' => $inviteCode->challenge]);

    expect($mail->subject)->toBe("You've been invited to {$inviteCode->challenge->title}")
        ->and($mail->greeting)->toBe('Hello Newbie!')
        ->and($mail->actionText)->toBe('Set Your Password')
        ->and($mail->actionUrl)->toContain('tok-123')
        ->and(invitationLines($mail))->toContain("[view the challenge here]({$challengeUrl})")
        ->and($mail->introLines)->toContain('A personal note')
        ->and(invitationLines($mail))
        ->toContain('A '.config('app.name').' account has been created for you, which you\'ll need to submit your entry. To get started, please set your password by clicking the button below.')
        ->and(invitationLines($mail))->toContain('expire in '.config('auth.passwords.users.expire').' minutes')
        ->and(invitationLines($mail))->toContain('new@example.com');
});

test('existing user mail uses the view challenge action and same custom message', function () {
    $inviteCode = ChallengeInviteCode::factory()->create();
    /** @var User $user */
    $user = User::factory()->create(['email' => 'old@example.com', 'first_name' => 'Olive']);

    $mail = (new ChallengeInvitation(
        inviteCode: $inviteCode,
        isNewUser: false,
        customMessage: 'A personal note',
    ))->toMail($user);

    expect($mail->subject)->toBe("You've been invited to {$inviteCode->challenge->title}")
        ->and($mail->greeting)->toBe('Hello Olive!')
        ->and($mail->actionText)->toBe('View Challenge')
        ->and($mail->actionUrl)->toContain($inviteCode->challenge->slug)
        ->and($mail->actionUrl)->not->toContain($inviteCode->code)
        ->and($mail->introLines)->toContain('A personal note')
        ->and(invitationLines($mail))->toContain('old@example.com')
        ->and(invitationLines($mail))->not->toContain('account has been created for you');
});

test('a new user without a password token falls back to the view challenge action', function () {
    $inviteCode = ChallengeInviteCode::factory()->create();
    /** @var User $user */
    $user = User::factory()->create();

    $mail = (new ChallengeInvitation(
        inviteCode: $inviteCode,
        isNewUser: true,
        passwordToken: null,
    ))->toMail($user);

    expect($mail->actionText)->toBe('View Challenge')
        ->and($mail->actionUrl)->toContain($inviteCode->challenge->slug)
        ->and($mail->actionUrl)->not->toContain($inviteCode->code)
        ->and(invitationLines($mail))->not->toContain('account has been created for you');
});

test('a view-only invite caters the new user copy towards viewing', function () {
    $inviteCode = ChallengeInviteCode::factory()->viewOnly()->create();
    /** @var User $user */
    $user = User::factory()->create();

    $mail = (new ChallengeInvitation(
        inviteCode: $inviteCode,
        isNewUser: true,
        passwordToken: 'tok-123',
    ))->toMail($user);

    expect(invitationLines($mail))
        ->toContain("You've been invited to view the \"{$inviteCode->challenge->title}\" challenge.")
        ->and(invitationLines($mail))
        ->toContain('A '.config('app.name').' account has been created for you so you can view the challenge. To get started, please set your password by clicking the button below.')
        ->and(invitationLines($mail))->not->toContain('submit your entry')
        ->and(invitationLines($mail))->not->toContain('added as a participant');
});

test('a view-only invite caters the existing user copy towards viewing', function () {
    $inviteCode = ChallengeInviteCode::factory()->viewOnly()->create();
    /** @var User $user */
    $user = User::factory()->create();

    $mail = (new ChallengeInvitation(
        inviteCode: $inviteCode,
        isNewUser: false,
    ))->toMail($user);

    expect(invitationLines($mail))
        ->toContain("You've been invited to view the \"{$inviteCode->challenge->title}\" challenge.")
        ->and(invitationLines($mail))->toContain('You can view the challenge using your existing account.')
        ->and(invitationLines($mail))->not->toContain('submit your entry');
});

test('a multi-paragraph custom message is split into separate lines', function () {
    $inviteCode = ChallengeInviteCode::factory()->create();
    /** @var User $user */
    $user = User::factory()->create();

    $mail = (new ChallengeInvitation(
        inviteCode: $inviteCode,
        isNewUser: false,
        customMessage: "First paragraph.\n\nSecond paragraph.\n\n\nThird paragraph.",
    ))->toMail($user);

    expect($mail->introLines)->toContain('First paragraph.')
        ->and($mail->introLines)->toContain('Second paragraph.')
        ->and($mail->introLines)->toContain('Third paragraph.');
});

test('a blank or whitespace-only custom message is omitted', function (?string $customMessage) {
    $inviteCode = ChallengeInviteCode::factory()->create();
    /** @var User $user */
    $user = User::factory()->create();

    $mail = (new ChallengeInvitation(
        inviteCode: $inviteCode,
        isNewUser: false,
        customMessage: $customMessage,
    ))->toMail($user);

    expect($mail->introLines)->toContain('You can access the challenge and submit your entry using your existing account.');

    if ($customMessage !== null) {
        expect($mail->introLines)->not->toContain($customMessage);
    }
})->with([
    'null' => [null],
    'empty string' => [''],
    'whitespace only' => ['   '],
]);
