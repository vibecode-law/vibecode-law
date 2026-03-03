<?php

namespace App\Services;

use Illuminate\Support\Arr;

class CropSanitizationService
{
    private const CROP_FIELDS = ['x', 'y', 'width', 'height'];

    /**
     * @param  array<string, mixed>|null  $crop
     * @return array{x: int, y: int, width: int, height: int}|null
     */
    public static function sanitizeSingleCrop(?array $crop): ?array
    {
        if ($crop === null) {
            return null;
        }

        return Arr::only($crop, self::CROP_FIELDS);
    }

    /**
     * @param  array<string, array<string, mixed>>|null  $crops
     * @param  array<int, string>  $allowedShapes
     * @return array<string, array{x: int, y: int, width: int, height: int}>|null
     */
    public static function sanitizeNamedCrops(?array $crops, array $allowedShapes): ?array
    {
        if ($crops === null) {
            return null;
        }

        $sanitized = [];

        foreach (Arr::only($crops, $allowedShapes) as $shape => $crop) {
            if (is_array($crop)) {
                $sanitized[$shape] = Arr::only($crop, self::CROP_FIELDS);
            }
        }

        return $sanitized;
    }

    /**
     * @param  array<int, mixed>|null  $cropsArray
     * @param  array<int, string>  $allowedShapes
     * @return array<int, array<string, array{x: int, y: int, width: int, height: int}>>|null
     */
    public static function sanitizeNamedCropsArray(?array $cropsArray, array $allowedShapes): ?array
    {
        if ($cropsArray === null) {
            return null;
        }

        return array_map(
            fn (mixed $item) => is_array($item)
                ? static::sanitizeNamedCrops(crops: $item, allowedShapes: $allowedShapes)
                : $item,
            $cropsArray,
        );
    }

    /**
     * Sanitize crop updates keyed by arbitrary IDs (e.g. image IDs).
     *
     * Used for: image_crop_updates = {42: {landscape: {x,y,w,h}}, 57: {landscape: {x,y,w,h}}}
     *
     * @param  array<int|string, mixed>|null  $cropsArray
     * @param  array<int, string>  $allowedShapes
     * @return array<int|string, array<string, array{x: int, y: int, width: int, height: int}>>|null
     */
    public static function sanitizeKeyedNamedCropsArray(?array $cropsArray, array $allowedShapes): ?array
    {
        if ($cropsArray === null) {
            return null;
        }

        $sanitized = [];

        foreach ($cropsArray as $key => $item) {
            $sanitized[$key] = is_array($item)
                ? static::sanitizeNamedCrops(crops: $item, allowedShapes: $allowedShapes)
                : $item;
        }

        return $sanitized;
    }
}
