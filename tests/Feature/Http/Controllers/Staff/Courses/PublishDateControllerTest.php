<?php

use App\Enums\ExperienceLevel;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();

        patch(route('staff.academy.courses.publish-date', $course), [
            'publish_date' => '2026-06-01',
        ])->assertRedirect(route('login'));
    });

    test('allows admin to update publish date', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',
            'experience_level' => ExperienceLevel::Beginner,
        ]);
        Lesson::factory()->create([
            'course_id' => $course->id,
            'publish_date' => '2026-06-01',
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.publish-date', $course), [
            'publish_date' => '2026-06-01',
        ])->assertRedirect();
    });

    test('does not allow regular users to update publish date', function () {
        /** @var User */
        $user = User::factory()->create();

        $course = Course::factory()->create();

        actingAs($user);

        patch(route('staff.academy.courses.publish-date', $course), [
            'publish_date' => '2026-06-01',
        ])->assertForbidden();
    });

    test('does not allow moderators to update publish date', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();

        actingAs($moderator);

        patch(route('staff.academy.courses.publish-date', $course), [
            'publish_date' => '2026-06-01',
        ])->assertForbidden();
    });
});

describe('setting publish date', function () {
    test('sets publish date when all requirements are met', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',
            'experience_level' => ExperienceLevel::Beginner,
            'publish_date' => null,
        ]);
        Lesson::factory()->create([
            'course_id' => $course->id,
            'publish_date' => '2026-06-01',
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.publish-date', $course), [
            'publish_date' => '2026-06-01',
        ])->assertRedirect(route('staff.academy.courses.edit', $course))
            ->assertSessionHas('flash.message', [
                'message' => 'Publish date set successfully.',
                'type' => 'success',
            ]);

        $course->refresh();

        expect($course->publish_date->format('Y-m-d'))->toBe('2026-06-01');
    });

    test('clears publish date without readiness checks', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',
            'experience_level' => ExperienceLevel::Beginner,
            'publish_date' => '2026-06-01',
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.publish-date', $course), [
            'publish_date' => null,
        ])->assertRedirect(route('staff.academy.courses.edit', $course))
            ->assertSessionHas('flash.message', [
                'message' => 'Publish date cleared.',
                'type' => 'success',
            ]);

        $course->refresh();

        expect($course->publish_date)->toBeNull();
    });

    test('clears publish date even when required fields are missing', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'learning_objectives' => null,
            'publish_date' => '2026-06-01',
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.publish-date', $course), [
            'publish_date' => null,
        ])->assertRedirect(route('staff.academy.courses.edit', $course))
            ->assertSessionDoesntHaveErrors();

        expect($course->refresh()->publish_date)->toBeNull();
    });
});

describe('readiness validation', function () {
    test('fails when nullable required fields are missing', function (string $field) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',
            'experience_level' => ExperienceLevel::Beginner,
            $field => null,
        ]);
        Lesson::factory()->create([
            'course_id' => $course->id,
            'publish_date' => '2026-06-01',
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.publish-date', $course), [
            'publish_date' => '2026-06-01',
        ])->assertSessionHasErrors('publish_date');
    })->with([
        'missing learning_objectives' => 'learning_objectives',
        'missing experience_level' => 'experience_level',
    ]);

    test('fails when no lesson has a matching publish date', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',
            'experience_level' => ExperienceLevel::Beginner,
        ]);
        Lesson::factory()->create([
            'course_id' => $course->id,
            'publish_date' => '2026-07-01',
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.publish-date', $course), [
            'publish_date' => '2026-06-01',
        ])->assertSessionHasErrors('publish_date');
    });

    test('fails when course has no lessons', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',
            'experience_level' => ExperienceLevel::Beginner,
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.publish-date', $course), [
            'publish_date' => '2026-06-01',
        ])->assertSessionHasErrors('publish_date');
    });
});

describe('allow preview', function () {
    test('enables allow_preview when all required fields are complete', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',
            'experience_level' => ExperienceLevel::Beginner,
            'allow_preview' => false,
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.publish-date', $course), [
            'allow_preview' => true,
        ])->assertRedirect(route('staff.academy.courses.edit', $course))
            ->assertSessionHas('flash.message', [
                'message' => 'Preview enabled.',
                'type' => 'success',
            ]);

        expect($course->refresh()->allow_preview)->toBeTrue();
    });

    test('disables allow_preview without readiness checks', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'learning_objectives' => null,
            'allow_preview' => true,
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.publish-date', $course), [
            'allow_preview' => false,
        ])->assertRedirect(route('staff.academy.courses.edit', $course))
            ->assertSessionDoesntHaveErrors();

        expect($course->refresh()->allow_preview)->toBeFalse();
    });

    test('fails to enable allow_preview when required fields are missing', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => null,
            'experience_level' => null,
            'allow_preview' => false,
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.publish-date', $course), [
            'allow_preview' => true,
        ])->assertSessionHasErrors('allow_preview');
    });

    test('does not require lesson date match when enabling allow_preview', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',
            'experience_level' => ExperienceLevel::Beginner,
            'allow_preview' => false,
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.publish-date', $course), [
            'allow_preview' => true,
        ])->assertRedirect()
            ->assertSessionDoesntHaveErrors();

        expect($course->refresh()->allow_preview)->toBeTrue();
    });
});

describe('validation', function () {
    test('validates publish_date is a valid date', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        patch(route('staff.academy.courses.publish-date', $course), [
            'publish_date' => 'not-a-date',
        ])->assertSessionHasErrors('publish_date');
    });
});
