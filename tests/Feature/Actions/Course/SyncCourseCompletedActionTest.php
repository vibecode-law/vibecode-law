<?php

use App\Actions\Course\SyncCourseCompletedAction;
use App\Jobs\MarketingEmail\AddTagToSubscriberJob;
use App\Models\Course\Course;
use App\Models\Course\CourseUser;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

it('sets course completed_at when all visible lessons are completed', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();
    $lessonA = Lesson::factory()->published()->for($course)->create(['order' => 1]);
    $lessonB = Lesson::factory()->published()->for($course)->create(['order' => 2]);

    LessonUser::factory()->create([
        'user_id' => $user->id,
        'lesson_id' => $lessonA->id,
        'completed_at' => now()->subMinutes(5),
    ]);

    LessonUser::factory()->create([
        'user_id' => $user->id,
        'lesson_id' => $lessonB->id,
        'completed_at' => now(),
    ]);

    CourseUser::factory()->create([
        'course_id' => $course->id,
        'user_id' => $user->id,
        'started_at' => now()->subMinutes(10),
    ]);

    (new SyncCourseCompletedAction)->handle(lesson: $lessonB, user: $user);

    $courseUser = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->first();

    expect($courseUser->completed_at)->not->toBeNull();
});

it('does not set course completed_at when not all visible lessons are completed', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();
    $lessonA = Lesson::factory()->published()->for($course)->create(['order' => 1]);
    Lesson::factory()->published()->for($course)->create(['order' => 2]);
    Lesson::factory()->published()->for($course)->create(['order' => 3]);

    LessonUser::factory()->create([
        'user_id' => $user->id,
        'lesson_id' => $lessonA->id,
        'completed_at' => now(),
    ]);

    CourseUser::factory()->create([
        'course_id' => $course->id,
        'user_id' => $user->id,
        'started_at' => now()->subMinutes(10),
    ]);

    (new SyncCourseCompletedAction)->handle(lesson: $lessonA, user: $user);

    $courseUser = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->first();

    expect($courseUser->completed_at)->toBeNull();
});

it('does not overwrite course completed_at when already set', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    $originalCompletedAt = now()->subMinutes(5);
    CourseUser::factory()->create([
        'course_id' => $course->id,
        'user_id' => $user->id,
        'started_at' => now()->subMinutes(10),
        'completed_at' => $originalCompletedAt,
    ]);

    LessonUser::factory()->create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'completed_at' => now(),
    ]);

    (new SyncCourseCompletedAction)->handle(lesson: $lesson, user: $user);

    $courseUser = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->first();

    expect($courseUser->completed_at->timestamp)->toBe($originalCompletedAt->timestamp);
});

it('ignores draft lessons when checking course completion', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();
    $publishedLesson = Lesson::factory()->published()->for($course)->create(['order' => 1]);
    Lesson::factory()->draft()->for($course)->create(['order' => 2]);

    LessonUser::factory()->create([
        'user_id' => $user->id,
        'lesson_id' => $publishedLesson->id,
        'completed_at' => now(),
    ]);

    CourseUser::factory()->create([
        'course_id' => $course->id,
        'user_id' => $user->id,
        'started_at' => now()->subMinutes(10),
    ]);

    (new SyncCourseCompletedAction)->handle(lesson: $publishedLesson, user: $user);

    $courseUser = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->first();

    expect($courseUser->completed_at)->not->toBeNull();
});

it('includes previewable lessons when checking course completion', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();
    $publishedLesson = Lesson::factory()->published()->for($course)->create(['order' => 1]);
    Lesson::factory()->previewable()->for($course)->create(['order' => 2]);

    LessonUser::factory()->create([
        'user_id' => $user->id,
        'lesson_id' => $publishedLesson->id,
        'completed_at' => now(),
    ]);

    CourseUser::factory()->create([
        'course_id' => $course->id,
        'user_id' => $user->id,
        'started_at' => now()->subMinutes(10),
    ]);

    (new SyncCourseCompletedAction)->handle(lesson: $publishedLesson, user: $user);

    $courseUser = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->first();

    expect($courseUser->completed_at)->toBeNull();
});

it('creates course_user with completed_at when no course_user record exists', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    LessonUser::factory()->create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'completed_at' => now(),
    ]);

    (new SyncCourseCompletedAction)->handle(lesson: $lesson, user: $user);

    $courseUser = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->first();

    expect($courseUser)
        ->not->toBeNull()
        ->completed_at->not->toBeNull();
});

describe('marketing email tagging', function () {
    it('dispatches tag job when course is first completed via existing course_user', function () {
        Bus::fake([AddTagToSubscriberJob::class]);

        $user = User::factory()->create([
            'external_subscriber_uuid' => 'sub-uuid-123',
        ]);
        $course = Course::factory()->published()->create(['slug' => 'intro-to-ai']);
        $lesson = Lesson::factory()->published()->for($course)->create();

        LessonUser::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
        ]);

        CourseUser::factory()->create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'started_at' => now()->subMinutes(10),
        ]);

        (new SyncCourseCompletedAction)->handle(lesson: $lesson, user: $user);

        Bus::assertDispatched(AddTagToSubscriberJob::class, function (AddTagToSubscriberJob $job) {
            return $job->externalSubscriberUuid === 'sub-uuid-123'
                && $job->tag === 'completedCourse:intro-to-ai';
        });
    });

    it('dispatches tag job when course is first completed without existing course_user', function () {
        Bus::fake([AddTagToSubscriberJob::class]);

        $user = User::factory()->create([
            'external_subscriber_uuid' => 'sub-uuid-456',
        ]);
        $course = Course::factory()->published()->create(['slug' => 'advanced-contracts']);
        $lesson = Lesson::factory()->published()->for($course)->create();

        LessonUser::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
        ]);

        (new SyncCourseCompletedAction)->handle(lesson: $lesson, user: $user);

        Bus::assertDispatched(AddTagToSubscriberJob::class, function (AddTagToSubscriberJob $job) {
            return $job->externalSubscriberUuid === 'sub-uuid-456'
                && $job->tag === 'completedCourse:advanced-contracts';
        });
    });

    it('does not dispatch tag job when course was already completed', function () {
        Bus::fake([AddTagToSubscriberJob::class]);

        $user = User::factory()->create([
            'external_subscriber_uuid' => 'sub-uuid-789',
        ]);
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        CourseUser::factory()->create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'started_at' => now()->subMinutes(10),
            'completed_at' => now()->subMinutes(5),
        ]);

        LessonUser::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
        ]);

        (new SyncCourseCompletedAction)->handle(lesson: $lesson, user: $user);

        Bus::assertNotDispatched(AddTagToSubscriberJob::class);
    });

    it('does not dispatch tag job when not all lessons are completed', function () {
        Bus::fake([AddTagToSubscriberJob::class]);

        $user = User::factory()->create([
            'external_subscriber_uuid' => 'sub-uuid-123',
        ]);
        $course = Course::factory()->published()->create();
        $lessonA = Lesson::factory()->published()->for($course)->create(['order' => 1]);
        Lesson::factory()->published()->for($course)->create(['order' => 2]);

        LessonUser::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lessonA->id,
            'completed_at' => now(),
        ]);

        CourseUser::factory()->create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'started_at' => now()->subMinutes(10),
        ]);

        (new SyncCourseCompletedAction)->handle(lesson: $lessonA, user: $user);

        Bus::assertNotDispatched(AddTagToSubscriberJob::class);
    });

    it('does not dispatch tag job when user has no external subscriber uuid', function () {
        Bus::fake([AddTagToSubscriberJob::class]);

        $user = User::factory()->create([
            'external_subscriber_uuid' => null,
        ]);
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        LessonUser::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
        ]);

        CourseUser::factory()->create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'started_at' => now()->subMinutes(10),
        ]);

        (new SyncCourseCompletedAction)->handle(lesson: $lesson, user: $user);

        Bus::assertNotDispatched(AddTagToSubscriberJob::class);
    });
});
