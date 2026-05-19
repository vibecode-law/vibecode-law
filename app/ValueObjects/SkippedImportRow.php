<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @implements Arrayable<string, int|string|null>
 */
#[TypeScript]
class SkippedImportRow implements Arrayable
{
    public function __construct(
        public readonly int $row,
        public readonly ?string $email,
        public readonly string $reason,
    ) {}

    /**
     * @param  array{row: int, email: string|null, reason: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            row: $data['row'],
            email: $data['email'] ?? null,
            reason: $data['reason'],
        );
    }

    /**
     * @return array{row: int, email: string|null, reason: string}
     */
    public function toArray(): array
    {
        return [
            'row' => $this->row,
            'email' => $this->email,
            'reason' => $this->reason,
        ];
    }
}
