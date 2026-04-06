<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CropAspectRatio implements ValidationRule
{
    private const float TOLERANCE = 0.02;

    /**
     * @param  array<string, float>  $expectedRatios
     */
    public function __construct(private array $expectedRatios) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return;
        }

        foreach ($value as $key => $crop) {
            if (! is_array($crop) || ! isset($crop['width'], $crop['height']) || (int) $crop['height'] === 0) {
                continue;
            }

            $expectedRatio = $this->expectedRatios[$key] ?? null;

            if ($expectedRatio === null) {
                continue;
            }

            $ratio = (int) $crop['width'] / (int) $crop['height'];

            if (abs($ratio - $expectedRatio) > self::TOLERANCE) {
                $fail("The {$key} crop does not have the correct aspect ratio.");
            }
        }
    }
}
