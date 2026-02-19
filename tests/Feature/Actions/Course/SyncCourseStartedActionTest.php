<?php

use App\Actions\Course\SyncCourseStartedAction;
use App\Jobs\MarketingEmail\AddTagToSubscriberJob;
use App\Models\Course\Course;
use App\Models\Course\CourseUser;
use App\Models\Course\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

it('creates course_user with started_at when startedAt is provided', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    (new SyncCourseStartedAction)->handle(lesson: $lesson, user: $user, startedAt: now());

    $courseUser = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->first();

    expect($courseUser)
        ->not->toBeNull()
        ->started_at->not->toBeNull();
});

it('does not overwrite course started_at when already set', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    $originalStartedAt = now()->subMinutes(10);
    CourseUser::factory()->create([
        'course_id' => $course->id,
        'user_id' => $user->id,
        'started_at' => $originalStartedAt,
    ]);

    (new SyncCourseStartedAction)->handle(lesson: $lesson, user: $user, startedAt: now());

    $courseUser = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->first();

    expect($courseUser->started_at->timestamp)->toBe($originalStartedAt->timestamp);
});

it('does not create course_user when startedAt is null', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    (new SyncCourseStartedAction)->handle(lesson: $lesson, user: $user, startedAt: null);

    $courseUser = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->first();

    expect($courseUser)->toBeNull();
});

describe('marketing email tagging', function () {
    it('dispatches tag job when course is first started', function () {
        Bus::fake([AddTagToSubscriberJob::class]);

        $user = User::factory()->create([
            'external_subscriber_uuid' => 'sub-uuid-123',
        ]);
        $course = Course::factory()->published()->create(['slug' => 'intro-to-ai']);
        $lesson = Lesson::factory()->published()->for($course)->create();

        (new SyncCourseStartedAction)->handle(lesson: $lesson, user: $user, startedAt: now());

        Bus::assertDispatched(AddTagToSubscriberJob::class, function (AddTagToSubscriberJob $job) {
            return $job->externalSubscriberUuid === 'sub-uuid-123'
                && $job->tag === 'startedCourse:intro-to-ai';
        });
    });

    it('dispatches tag job when started_at is first set on existing course_user', function () {
        Bus::fake([AddTagToSubscriberJob::class]);

        $user = User::factory()->create([
            'external_subscriber_uuid' => 'sub-uuid-456',
        ]);
        $course = Course::factory()->published()->create(['slug' => 'advanced-contracts']);
        $lesson = Lesson::factory()->published()->for($course)->create();

        CourseUser::factory()->create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'started_at' => null,
        ]);

        (new SyncCourseStartedAction)->handle(lesson: $lesson, user: $user, startedAt: now());

        Bus::assertDispatched(AddTagToSubscriberJob::class, function (AddTagToSubscriberJob $job) {
            return $job->externalSubscriberUuid === 'sub-uuid-456'
                && $job->tag === 'startedCourse:advanced-contracts';
        });
    });

    it('does not dispatch tag job when course was already started', function () {
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
        ]);

        (new SyncCourseStartedAction)->handle(lesson: $lesson, user: $user, startedAt: now());

        Bus::assertNotDispatched(AddTagToSubscriberJob::class);
    });

    it('does not dispatch tag job when user has no external subscriber uuid', function () {
        Bus::fake([AddTagToSubscriberJob::class]);

        $user = User::factory()->create([
            'external_subscriber_uuid' => null,
        ]);
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        (new SyncCourseStartedAction)->handle(lesson: $lesson, user: $user, startedAt: now());

        Bus::assertNotDispatched(AddTagToSubscriberJob::class);
    });

    it('does not dispatch tag job when startedAt is null', function () {
        Bus::fake([AddTagToSubscriberJob::class]);

        $user = User::factory()->create([
            'external_subscriber_uuid' => 'sub-uuid-123',
        ]);
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        (new SyncCourseStartedAction)->handle(lesson: $lesson, user: $user, startedAt: null);

        Bus::assertNotDispatched(AddTagToSubscriberJob::class);
    });
});
