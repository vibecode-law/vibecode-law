<?php

namespace App\Services\Course;

use App\Models\Course\Lesson;
use App\Models\Tag;
use App\ValueObjects\LessonCopywriterResult;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use RuntimeException;

class LessonCopywriterService
{
    public function __construct(protected Lesson $lesson) {}

    public function generate(): LessonCopywriterResult
    {
        $transcript = $this->lesson->transcript_txt;

        if ($transcript === null || trim($transcript) === '') {
            throw new RuntimeException("Lesson [{$this->lesson->id}] has no transcript available.");
        }

        /** @var array<int, int|string> $availableTags */
        $availableTags = Tag::query()->orderBy('name')->pluck('name', 'id')->all();

        $schema = new ObjectSchema(
            name: 'lesson_copywriting',
            description: 'Suggested copywriting content for an online lesson',
            properties: [
                new StringSchema(
                    name: 'tagline',
                    description: 'A short, plaintext, compelling tagline for the lesson (max 120 characters). Should capture the core value proposition. Do not pad for length unnecessarily.',
                ),
                new StringSchema(
                    name: 'description',
                    description: 'A concise plaintext description of the lesson content (2-3 sentences) to be used for marketing and meta descriptions. Explain what the lesson covers and why it matters.',
                ),
                new StringSchema(
                    name: 'learning_objectives',
                    description: 'A markdown-formatted bulleted list of 3-5 specific learning objectives. Each should start with an action verb and describe a measurable outcome. Use "- " for each bullet point. Do not use any markdown other than bullets and bold.',
                ),
                new StringSchema(
                    name: 'copy',
                    description: 'A written propose version of the lesson, written in markdown. It should include equivelent detail and should not expand or contract on detail. When using headings, start at h3 (i.e. do not use h1 or h2).',
                ),
                new ArraySchema(
                    name: 'suggested_tag_ids',
                    description: 'A list of tag IDs for this lesson, chosen from the available tags provided. Only include tags that are clearly relevant to the lesson content.',
                    items: new NumberSchema(
                        name: 'tag_id',
                        description: 'A tag ID from the available tags list.',
                    ),
                ),
            ],
            requiredFields: ['tagline', 'description', 'learning_objectives', 'copy', 'suggested_tag_ids'],
        );

        $response = Prism::structured()
            ->using(Provider::Anthropic, 'claude-sonnet-4-6')
            ->withSchema($schema)
            ->withSystemPrompt($this->buildSystemPrompt())
            ->withPrompt($this->buildUserPrompt(
                transcript: $transcript,
                availableTags: $availableTags,
            ))
            ->withMaxTokens(20000)
            ->withClientOptions(['timeout' => 120])
            ->asStructured();

        /** @var array{tagline: string, description: string, learning_objectives: string, copy: string, suggested_tag_ids: array<int, int>} $structured */
        $structured = $response->structured;

        return LessonCopywriterResult::fromArray($structured);
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
        You are an expert copywriter for an online legal technology education platform. Your audience is legal professionals, law students, and technologists interested in legaltech.

        Write in a professional but approachable tone. Be specific about what learners will gain. Avoid jargon unless it's industry-standard terminology that the audience would know.

        Format learning objectives as a markdown bulleted list using "- " prefix, and a space between each line. Format copy using markdown paragraphs.
        PROMPT;
    }

    /**
     * @param  array<int, string>  $availableTags
     */
    private function buildUserPrompt(string $transcript, array $availableTags): string
    {
        $title = $this->lesson->title;
        $tagsList = collect($availableTags)
            ->map(fn (string $name, int $id): string => "{$id}: {$name}")
            ->implode("\n");

        return <<<PROMPT
        Based on the following lesson transcript, generate copywriting content for the lesson titled "{$title}".

        <transcript>
        {$transcript}
        </transcript>

        <available_tags>
        {$tagsList}
        </available_tags>

        For suggested_tag_ids, return only the numeric IDs of tags that are clearly relevant to the lesson content.
        PROMPT;
    }
}
