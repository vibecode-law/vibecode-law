<?php

use App\Services\CropSanitizationService;

describe('sanitizeSingleCrop', function () {
    test('returns null for null input', function () {
        expect(CropSanitizationService::sanitizeSingleCrop(crop: null))->toBeNull();
    });

    test('keeps only x, y, width, height', function () {
        $crop = ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100, 'zoom' => 1.5, 'rotation' => 90];

        expect(CropSanitizationService::sanitizeSingleCrop(crop: $crop))->toBe([
            'x' => 10, 'y' => 20, 'width' => 100, 'height' => 100,
        ]);
    });

    test('returns crop unchanged when no extra fields', function () {
        $crop = ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500];

        expect(CropSanitizationService::sanitizeSingleCrop(crop: $crop))->toBe($crop);
    });
});

describe('sanitizeNamedCrops', function () {
    test('returns null for null input', function () {
        expect(CropSanitizationService::sanitizeNamedCrops(crops: null, allowedShapes: ['landscape']))->toBeNull();
    });

    test('strips extra fields within each shape', function () {
        $crops = [
            'square' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100, 'zoom' => 2.0],
            'landscape' => ['x' => 10, 'y' => 20, 'width' => 800, 'height' => 450, 'rotation' => 45],
        ];

        $result = CropSanitizationService::sanitizeNamedCrops(
            crops: $crops,
            allowedShapes: ['square', 'landscape'],
        );

        expect($result)->toBe([
            'square' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100],
            'landscape' => ['x' => 10, 'y' => 20, 'width' => 800, 'height' => 450],
        ]);
    });

    test('strips unknown shape keys', function () {
        $crops = [
            'landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450],
            'portrait' => ['x' => 0, 'y' => 0, 'width' => 450, 'height' => 800],
        ];

        $result = CropSanitizationService::sanitizeNamedCrops(crops: $crops, allowedShapes: ['landscape']);

        expect($result)->toBe([
            'landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450],
        ]);
    });

    test('returns crops unchanged when no extras', function () {
        $crops = [
            'square' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100],
            'landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450],
        ];

        $result = CropSanitizationService::sanitizeNamedCrops(
            crops: $crops,
            allowedShapes: ['square', 'landscape'],
        );

        expect($result)->toBe($crops);
    });
});

describe('sanitizeNamedCropsArray', function () {
    test('returns null for null input', function () {
        expect(CropSanitizationService::sanitizeNamedCropsArray(cropsArray: null, allowedShapes: ['landscape']))->toBeNull();
    });

    test('strips unknown shapes and extra fields within allowed shapes', function () {
        $cropsArray = [
            ['landscape' => ['x' => 10, 'y' => 20, 'width' => 800, 'height' => 450, 'zoom' => 1.5], 'square' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100]],
            ['landscape' => ['x' => 50, 'y' => 100, 'width' => 600, 'height' => 338]],
        ];

        $result = CropSanitizationService::sanitizeNamedCropsArray(
            cropsArray: $cropsArray,
            allowedShapes: ['landscape'],
        );

        expect($result)->toBe([
            ['landscape' => ['x' => 10, 'y' => 20, 'width' => 800, 'height' => 450]],
            ['landscape' => ['x' => 50, 'y' => 100, 'width' => 600, 'height' => 338]],
        ]);
    });

    test('passes through non-array items unchanged', function () {
        $cropsArray = ['not-an-array', ['landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450]]];

        $result = CropSanitizationService::sanitizeNamedCropsArray(
            cropsArray: $cropsArray,
            allowedShapes: ['landscape'],
        );

        expect($result[0])->toBe('not-an-array');
        expect($result[1])->toBe(['landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450]]);
    });
});

describe('sanitizeKeyedNamedCropsArray', function () {
    test('returns null for null input', function () {
        expect(CropSanitizationService::sanitizeKeyedNamedCropsArray(cropsArray: null, allowedShapes: ['landscape']))->toBeNull();
    });

    test('strips unknown shapes and extra fields within allowed shapes', function () {
        $cropsArray = [
            42 => ['landscape' => ['x' => 10, 'y' => 20, 'width' => 800, 'height' => 450, 'zoom' => 1.5], 'square' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100]],
            57 => ['landscape' => ['x' => 50, 'y' => 100, 'width' => 600, 'height' => 338]],
        ];

        $result = CropSanitizationService::sanitizeKeyedNamedCropsArray(
            cropsArray: $cropsArray,
            allowedShapes: ['landscape'],
        );

        expect($result)->toBe([
            42 => ['landscape' => ['x' => 10, 'y' => 20, 'width' => 800, 'height' => 450]],
            57 => ['landscape' => ['x' => 50, 'y' => 100, 'width' => 600, 'height' => 338]],
        ]);
    });

    test('preserves arbitrary keys', function () {
        $cropsArray = [
            'abc' => ['landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450]],
            99 => ['landscape' => ['x' => 10, 'y' => 20, 'width' => 600, 'height' => 338]],
        ];

        $result = CropSanitizationService::sanitizeKeyedNamedCropsArray(
            cropsArray: $cropsArray,
            allowedShapes: ['landscape'],
        );

        expect($result)->toHaveKeys(['abc', 99]);
    });

    test('passes through non-array items unchanged', function () {
        $cropsArray = [
            42 => 'not-an-array',
            57 => ['landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450]],
        ];

        $result = CropSanitizationService::sanitizeKeyedNamedCropsArray(
            cropsArray: $cropsArray,
            allowedShapes: ['landscape'],
        );

        expect($result[42])->toBe('not-an-array');
        expect($result[57])->toBe(['landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450]]);
    });
});
