<?php

use App\Actions\Course\LogLessonProgressAction;
use App\Listeners\SyncGuestLessonProgressOnLogin;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as LinkedinUser;

use function Pest\Laravel\mock;

it('calls LogLessonProgressAction with correct parameters for each lesson', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();
    $lessonA = Lesson::factory()->published()->for($course)->create();
    $lessonB = Lesson::factory()->published()->for($course)->create();

    $viewedAt = now()->subMinutes(10)->toIso8601String();
    $startedAt = now()->subMinutes(5)->toIso8601String();
    $completedAt = now()->subMinute()->toIso8601String();

    session([
        'lesson_progress' => [
            $lessonA->id => [
                'viewed_at' => $viewedAt,
                'started_at' => $startedAt,
                'playback_time_seconds' => 120,
                'completed_at' => $completedAt,
            ],
            $lessonB->id => [
                'viewed_at' => $viewedAt,
                'started_at' => $startedAt,
            ],
        ],
    ]);

    $action = mock(LogLessonProgressAction::class);

    $action->shouldReceive('handle')
        ->once()
        ->withArgs(function (Lesson $lesson, User $u, ?Carbon $viewed, ?Carbon $started, ?int $playback, ?Carbon $completed) use ($lessonA, $user, $viewedAt, $startedAt, $completedAt) {
            return $lesson->is($lessonA)
                && $u->is($user)
                && $viewed->toIso8601String() === $viewedAt
                && $started->toIso8601String() === $startedAt
                && $playback === 120
                && $completed->toIso8601String() === $completedAt;
        });

    $action->shouldReceive('handle')
        ->once()
        ->withArgs(function (Lesson $lesson, User $u, ?Carbon $viewed, ?Carbon $started, ?int $playback, ?Carbon $completed) use ($lessonB, $user, $viewedAt, $startedAt) {
            return $lesson->is($lessonB)
                && $u->is($user)
                && $viewed->toIso8601String() === $viewedAt
                && $started->toIso8601String() === $startedAt
                && $playback === null
                && $completed === null;
        });

    $listener = app(SyncGuestLessonProgressOnLogin::class);
    $listener->handle(new Login(guard: 'web', user: $user, remember: false));
});

it('clears session after syncing', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    session([
        'lesson_progress' => [
            $lesson->id => [
                'started_at' => now()->toIso8601String(),
            ],
        ],
    ]);

    mock(LogLessonProgressAction::class)->shouldReceive('handle');

    $listener = app(SyncGuestLessonProgressOnLogin::class);
    $listener->handle(new Login(guard: 'web', user: $user, remember: false));

    expect(session('lesson_progress'))->toBeNull();
});

it('handles empty session gracefully', function () {
    $user = User::factory()->create();

    $action = mock(LogLessonProgressAction::class);
    $action->shouldNotReceive('handle');

    $listener = app(SyncGuestLessonProgressOnLogin::class);
    $listener->handle(new Login(guard: 'web', user: $user, remember: false));
});

it('skips non-existent lessons without error', function () {
    $user = User::factory()->create();

    session([
        'lesson_progress' => [
            99999 => [
                'started_at' => now()->toIso8601String(),
            ],
        ],
    ]);

    $action = mock(LogLessonProgressAction::class);
    $action->shouldNotReceive('handle');

    $listener = app(SyncGuestLessonProgressOnLogin::class);
    $listener->handle(new Login(guard: 'web', user: $user, remember: false));
});

describe('login event integration', function () {
    it('syncs guest progress when logging in via Fortify', function () {
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        $viewedAt = now()->subMinutes(10)->toIso8601String();

        test()->withSession([
            'lesson_progress' => [
                $lesson->id => [
                    'viewed_at' => $viewedAt,
                    'started_at' => now()->toIso8601String(),
                    'playback_time_seconds' => 60,
                ],
            ],
        ])->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot)
            ->not->toBeNull()
            ->viewed_at->toIso8601String()->toBe($viewedAt)
            ->playback_time_seconds->toBe(60);
    });

    it('syncs guest progress when logging in via LinkedIn', function () {
        Storage::fake('public');
        Http::fake();

        $existingUser = User::factory()->create([
            'linkedin_id' => 'linkedin-sync-test',
            'email' => 'sync@email.com',
        ]);

        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-sync-test',
            'email' => 'sync@email.com',
            'user' => [
                'given_name' => 'Sync',
                'family_name' => 'Test',
            ],
        ])->setToken('fake-token'));

        $viewedAt = now()->subMinutes(10)->toIso8601String();

        test()->withSession([
            'lesson_progress' => [
                $lesson->id => [
                    'viewed_at' => $viewedAt,
                    'started_at' => now()->toIso8601String(),
                    'completed_at' => now()->toIso8601String(),
                ],
            ],
        ])->get('/auth/login/linkedin/callback');

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $existingUser->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot)
            ->not->toBeNull()
            ->viewed_at->toIso8601String()->toBe($viewedAt)
            ->started_at->not->toBeNull()
            ->completed_at->not->toBeNull();
    });
});
