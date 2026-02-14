<?php

use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;
use Illuminate\Support\Carbon;

test('timestamps are cast to datetime', function () {
    $progress = LessonUser::factory()->viewed()->started()->completed()->create();

    expect($progress->viewed_at)->toBeInstanceOf(Carbon::class)
        ->and($progress->started_at)->toBeInstanceOf(Carbon::class)
        ->and($progress->completed_at)->toBeInstanceOf(Carbon::class);
});

test('timestamps default to null', function () {
    $progress = LessonUser::factory()->create();

    expect($progress->viewed_at)->toBeNull()
        ->and($progress->started_at)->toBeNull()
        ->and($progress->completed_at)->toBeNull();
});

describe('cascade deletes', function () {
    test('deleting user cascades to lesson_user', function () {
        $user = User::factory()->create();
        $progress = LessonUser::factory()->create(['user_id' => $user->id]);

        $user->delete();

        expect(LessonUser::query()->find($progress->id))->toBeNull();
    });

    test('deleting lesson cascades to lesson_user', function () {
        $lesson = Lesson::factory()->create();
        $progress = LessonUser::factory()->create(['lesson_id' => $lesson->id]);

        $lesson->delete();

        expect(LessonUser::query()->find($progress->id))->toBeNull();
    });
});

test('user and lesson combination is unique', function () {
    $progress = LessonUser::factory()->create();

    LessonUser::factory()->create([
        'user_id' => $progress->user_id,
        'lesson_id' => $progress->lesson_id,
    ]);
})->throws(Illuminate\Database\UniqueConstraintViolationException::class);
