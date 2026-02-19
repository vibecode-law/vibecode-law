<?php

use App\Models\Course\Lesson;
use App\Models\Tag;
use App\Services\Course\LessonCopywriterService;
use App\ValueObjects\LessonCopywriterResult;
use Illuminate\Support\Facades\Storage;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\StructuredResponseFake;

beforeEach(function () {
    Storage::fake();
});

test('generate returns a LessonCopywriterResult from transcript', function () {
    $lesson = Lesson::factory()->create(['title' => 'Intro to LegalTech']);
    $frontendTag = Tag::factory()->create(['name' => 'Frontend', 'slug' => 'frontend']);
    Tag::factory()->create(['name' => 'Backend', 'slug' => 'backend']);

    Storage::put("lessons/{$lesson->id}/transcript.txt", 'This is a sample transcript about legal technology and AI tools.');

    $fakeStructured = [
        'tagline' => 'Master the fundamentals of legal technology',
        'description' => 'Learn how AI tools are transforming the legal industry. This lesson covers the basics of legaltech adoption.',
        'learning_objectives' => "- Identify key AI tools used in legal practice\n\n- Evaluate legaltech solutions for your firm\n\n- Understand the ethical implications of AI in law",
        'copy' => "Legal technology is reshaping how lawyers work.\n\nThis lesson introduces you to the tools and techniques that are driving this transformation.",
        'suggested_tag_ids' => [$frontendTag->id],
    ];

    Prism::fake([
        StructuredResponseFake::make()
            ->withStructured($fakeStructured),
    ]);

    $result = (new LessonCopywriterService(lesson: $lesson))->generate();

    expect($result)
        ->toBeInstanceOf(LessonCopywriterResult::class)
        ->and($result->tagline)->toBe($fakeStructured['tagline'])
        ->and($result->description)->toBe($fakeStructured['description'])
        ->and($result->learningObjectives)->toBe($fakeStructured['learning_objectives'])
        ->and($result->copy)->toBe($fakeStructured['copy'])
        ->and($result->suggestedTagIds)->toBe([$frontendTag->id]);
});

test('generate includes lesson title and available tag IDs in the prompt', function () {
    $lesson = Lesson::factory()->create(['title' => 'Contract Automation']);
    $backendTag = Tag::factory()->create(['name' => 'Backend', 'slug' => 'backend']);
    $laravelTag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

    Storage::put("lessons/{$lesson->id}/transcript.txt", 'A transcript about contract automation workflows.');

    $fake = Prism::fake([
        StructuredResponseFake::make()
            ->withStructured([
                'tagline' => 'Automate your contracts',
                'description' => 'Learn contract automation.',
                'learning_objectives' => '- Understand automation basics',
                'copy' => 'Contract automation saves time.',
                'suggested_tag_ids' => [$backendTag->id, $laravelTag->id],
            ]),
    ]);

    (new LessonCopywriterService(lesson: $lesson))->generate();

    $fake->assertRequest(function (array $requests) use ($backendTag, $laravelTag) {
        $prompt = $requests[0]->prompt();

        expect($prompt)
            ->toContain('Contract Automation')
            ->toContain("{$backendTag->id}: Backend")
            ->toContain("{$laravelTag->id}: Laravel");
    });
});

test('generate throws exception when lesson has no transcript', function () {
    $lesson = Lesson::factory()->create();

    (new LessonCopywriterService(lesson: $lesson))->generate();
})->throws(RuntimeException::class, 'has no transcript available');

test('generate throws exception when transcript is empty', function () {
    $lesson = Lesson::factory()->create();

    Storage::put("lessons/{$lesson->id}/transcript.txt", '   ');

    (new LessonCopywriterService(lesson: $lesson))->generate();
})->throws(RuntimeException::class, 'has no transcript available');

test('result toArray returns expected structure', function () {
    $result = new LessonCopywriterResult(
        tagline: 'A great tagline',
        description: 'A detailed description.',
        learningObjectives: '- Objective one',
        copy: 'Some marketing copy.',
        suggestedTagIds: [1, 5],
    );

    expect($result->toArray())->toBe([
        'tagline' => 'A great tagline',
        'description' => 'A detailed description.',
        'learning_objectives' => '- Objective one',
        'copy' => 'Some marketing copy.',
        'suggested_tag_ids' => [1, 5],
    ]);
});

test('result casts suggested tag IDs to integers', function () {
    $result = LessonCopywriterResult::fromArray([
        'tagline' => 'Tagline',
        'description' => 'Description.',
        'learning_objectives' => '- Objective',
        'copy' => 'Copy.',
        'suggested_tag_ids' => [1.0, 3.0],
    ]);

    expect($result->suggestedTagIds)->toBe([1, 3]);
});
