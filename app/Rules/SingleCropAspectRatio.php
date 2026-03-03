<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SingleCropAspectRatio implements ValidationRule
{
    private const float TOLERANCE = 0.02;

    public function __construct(private float $expectedRatio) {}

    /**
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value) || ! isset($value['width'], $value['height']) || (int) $value['height'] === 0) {
            return;
        }

        $ratio = (int) $value['width'] / (int) $value['height'];

        if (abs($ratio - $this->expectedRatio) > self::TOLERANCE) {
            $fail('The crop does not have the correct aspect ratio.');
        }
    }
}
