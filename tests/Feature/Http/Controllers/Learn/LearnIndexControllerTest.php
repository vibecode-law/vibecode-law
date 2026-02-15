<?php

use App\Models\Course\Course;
use App\Models\Course\CourseUser;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('index returns 200 for guests', function () {
    get(route('learn.index'))
        ->assertOk();
});

test('index renders the correct inertia component', function () {
    get(route('learn.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('learn/courses/index')
        );
});

test('index returns courses ordered by order column', function () {
    $third = Course::factory()->create(['order' => 3]);
    $first = Course::factory()->create(['order' => 1]);
    $second = Course::factory()->create(['order' => 2]);

    get(route('learn.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('courses', 3)
            ->where('courses.0.id', $first->id)
            ->where('courses.1.id', $second->id)
            ->where('courses.2.id', $third->id)
        );
});

test('index includes lesson counts', function () {
    $course = Course::factory()->create();
    Lesson::factory()->count(3)->for($course)->create();

    get(route('learn.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('courses.0.lessons_count', 3)
        );
});

test('index returns the correct data structure', function () {
    $course = Course::factory()->create()->fresh();
    Lesson::factory()->count(2)->for($course)->create();

    $user = $course->user;

    get(route('learn.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('courses', 1)
            ->has('courses.0', fn (AssertableInertia $c) => $c
                ->where('id', $course->id)
                ->where('slug', $course->slug)
                ->where('title', $course->title)
                ->where('tagline', $course->tagline)
                ->where('order', $course->order)
                ->where('lessons_count', 2)
                ->where('started_count', $course->started_count)
                ->where('thumbnail_url', $course->thumbnail_url)
                ->where('thumbnail_rect_strings', $course->thumbnail_rect_strings)
                ->where('experience_level', $course->experience_level->forFrontend()->toArray())
                ->where('duration_seconds', $course->duration_seconds)
                ->has('user', fn (AssertableInertia $u) => $u
                    ->where('first_name', $user->first_name)
                    ->where('last_name', $user->last_name)
                    ->where('handle', $user->handle)
                    ->where('organisation', $user->organisation)
                    ->where('job_title', $user->job_title)
                    ->where('avatar', $user->avatar)
                    ->where('linkedin_url', $user->linkedin_url)
                    ->where('team_role', $user->team_role)
                )
                ->missing('tags')
                ->missing('description')
                ->missing('description_html')
                ->missing('completed_count')
                ->missing('lessons')
            )
        );
});

test('index returns total enrolled users as distinct count', function () {
    $course1 = Course::factory()->create();
    $course2 = Course::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    CourseUser::factory()->create(['course_id' => $course1->id, 'user_id' => $user1->id]);
    CourseUser::factory()->create(['course_id' => $course2->id, 'user_id' => $user1->id]);
    CourseUser::factory()->create(['course_id' => $course1->id, 'user_id' => $user2->id]);

    get(route('learn.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('totalEnrolledUsers', 2)
        );
});

test('index returns zero total enrolled users when none exist', function () {
    get(route('learn.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('totalEnrolledUsers', 0)
        );
});

test('index returns guides from config', function () {
    $guidesConfig = config('content.guides.children');

    get(route('learn.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('guides', count($guidesConfig))
            ->where('guides.0.name', $guidesConfig[0]['title'])
            ->where('guides.0.slug', $guidesConfig[0]['slug'])
            ->where('guides.0.summary', $guidesConfig[0]['summary'])
            ->where('guides.0.icon', $guidesConfig[0]['icon'])
            ->where('guides.0.route', route('learn.guides.show', ['slug' => $guidesConfig[0]['slug']]))
        );
});

test('index returns empty course progress for guests', function () {
    Course::factory()->create();

    get(route('learn.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('courseProgress', [])
        );
});

describe('course progress for authenticated users', function () {
    test('index returns zero progress when no lessons completed', function () {
        $course = Course::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        Lesson::factory()->count(3)->for($course)->create();

        actingAs($user)
            ->get(route('learn.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where("courseProgress.{$course->id}.progressPercentage", 0)
            );
    });

    test('index returns correct progress percentage based on completed lessons', function () {
        $course = Course::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        $lessons = Lesson::factory()->count(4)->for($course)->create();

        LessonUser::factory()->completed()->create(['lesson_id' => $lessons[0]->id, 'user_id' => $user->id]);
        LessonUser::factory()->completed()->create(['lesson_id' => $lessons[1]->id, 'user_id' => $user->id]);

        actingAs($user)
            ->get(route('learn.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where("courseProgress.{$course->id}.progressPercentage", 50)
            );
    });

    test('index returns 100 percent when all lessons completed', function () {
        $course = Course::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        $lesson = Lesson::factory()->for($course)->create();
        LessonUser::factory()->completed()->create(['lesson_id' => $lesson->id, 'user_id' => $user->id]);

        actingAs($user)
            ->get(route('learn.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where("courseProgress.{$course->id}.progressPercentage", 100)
            );
    });

    test('index returns progress for multiple courses independently', function () {
        $course1 = Course::factory()->create(['order' => 1]);
        $course2 = Course::factory()->create(['order' => 2]);

        /** @var User */
        $user = User::factory()->create();

        $lessons1 = Lesson::factory()->count(2)->for($course1)->create();
        Lesson::factory()->count(2)->for($course2)->create();

        LessonUser::factory()->completed()->create(['lesson_id' => $lessons1[0]->id, 'user_id' => $user->id]);

        actingAs($user)
            ->get(route('learn.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where("courseProgress.{$course1->id}.progressPercentage", 50)
                ->where("courseProgress.{$course2->id}.progressPercentage", 0)
            );
    });
});
