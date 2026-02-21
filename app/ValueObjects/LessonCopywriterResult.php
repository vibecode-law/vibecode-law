<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, string|array<int, int>>
 */
class LessonCopywriterResult implements Arrayable
{
    /**
     * @param  array<int, int>  $suggestedTagIds
     */
    public function __construct(
        public readonly string $tagline,
        public readonly string $description,
        public readonly string $learningObjectives,
        public readonly string $copy,
        public readonly array $suggestedTagIds,
    ) {}

    /**
     * @param  array{tagline: string, description: string, learning_objectives: string, copy: string, suggested_tag_ids: array<int, int>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tagline: $data['tagline'],
            description: $data['description'],
            learningObjectives: $data['learning_objectives'],
            copy: $data['copy'],
            suggestedTagIds: array_map(intval(...), $data['suggested_tag_ids']),
        );
    }

    /**
     * @return array{tagline: string, description: string, learning_objectives: string, copy: string, suggested_tag_ids: array<int, int>}
     */
    public function toArray(): array
    {
        return [
            'tagline' => $this->tagline,
            'description' => $this->description,
            'learning_objectives' => $this->learningObjectives,
            'copy' => $this->copy,
            'suggested_tag_ids' => $this->suggestedTagIds,
        ];
    }
}
