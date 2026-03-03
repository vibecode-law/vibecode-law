<?php

use App\Rules\CropAspectRatio;

function validateCrop(CropAspectRatio $rule, mixed $value): array
{
    $errors = [];

    $rule->validate('thumbnail_crops', $value, function (string $message) use (&$errors) {
        $errors[] = $message;
    });

    return $errors;
}

describe('passing validation', function () {
    test('passes for crops matching expected square ratio', function () {
        $rule = new CropAspectRatio(expectedRatios: ['square' => 1.0]);

        $errors = validateCrop($rule, [
            'square' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100],
        ]);

        expect($errors)->toBeEmpty();
    });

    test('passes for crops matching expected landscape ratio', function () {
        $rule = new CropAspectRatio(expectedRatios: ['landscape' => 16 / 9]);

        $errors = validateCrop($rule, [
            'landscape' => ['x' => 0, 'y' => 0, 'width' => 1600, 'height' => 900],
        ]);

        expect($errors)->toBeEmpty();
    });

    test('passes for multiple crops matching their expected ratios', function () {
        $rule = new CropAspectRatio(expectedRatios: ['square' => 1.0, 'landscape' => 16 / 9]);

        $errors = validateCrop($rule, [
            'square' => ['x' => 0, 'y' => 0, 'width' => 200, 'height' => 200],
            'landscape' => ['x' => 0, 'y' => 0, 'width' => 1920, 'height' => 1080],
        ]);

        expect($errors)->toBeEmpty();
    });

    test('passes within tolerance threshold', function () {
        $rule = new CropAspectRatio(expectedRatios: ['square' => 1.0]);

        $errors = validateCrop($rule, [
            'square' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 99],
        ]);

        expect($errors)->toBeEmpty();
    });

    test('passes when value is not an array', function () {
        $rule = new CropAspectRatio(expectedRatios: ['square' => 1.0]);

        $errors = validateCrop($rule, 'not-an-array');

        expect($errors)->toBeEmpty();
    });

    test('passes when crop is missing width or height', function () {
        $rule = new CropAspectRatio(expectedRatios: ['square' => 1.0]);

        $errors = validateCrop($rule, [
            'square' => ['x' => 0, 'y' => 0, 'width' => 100],
        ]);

        expect($errors)->toBeEmpty();
    });

    test('passes when crop height is zero', function () {
        $rule = new CropAspectRatio(expectedRatios: ['square' => 1.0]);

        $errors = validateCrop($rule, [
            'square' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 0],
        ]);

        expect($errors)->toBeEmpty();
    });

    test('passes for crop keys not in expected ratios', function () {
        $rule = new CropAspectRatio(expectedRatios: ['square' => 1.0]);

        $errors = validateCrop($rule, [
            'portrait' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 200],
        ]);

        expect($errors)->toBeEmpty();
    });

    test('passes when crop is not an array', function () {
        $rule = new CropAspectRatio(expectedRatios: ['square' => 1.0]);

        $errors = validateCrop($rule, [
            'square' => 'not-an-array',
        ]);

        expect($errors)->toBeEmpty();
    });
});

describe('failing validation', function () {
    test('fails for crop with wrong ratio', function () {
        $rule = new CropAspectRatio(expectedRatios: ['square' => 1.0]);

        $errors = validateCrop($rule, [
            'square' => ['x' => 0, 'y' => 0, 'width' => 200, 'height' => 100],
        ]);

        expect($errors)->toBe(['The square crop does not have the correct aspect ratio.']);
    });

    test('fails for landscape crop with wrong ratio', function () {
        $rule = new CropAspectRatio(expectedRatios: ['landscape' => 16 / 9]);

        $errors = validateCrop($rule, [
            'landscape' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100],
        ]);

        expect($errors)->toBe(['The landscape crop does not have the correct aspect ratio.']);
    });

    test('fails for each crop with wrong ratio independently', function () {
        $rule = new CropAspectRatio(expectedRatios: ['square' => 1.0, 'landscape' => 16 / 9]);

        $errors = validateCrop($rule, [
            'square' => ['x' => 0, 'y' => 0, 'width' => 200, 'height' => 100],
            'landscape' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100],
        ]);

        expect($errors)->toBe([
            'The square crop does not have the correct aspect ratio.',
            'The landscape crop does not have the correct aspect ratio.',
        ]);
    });

    test('fails outside tolerance threshold', function () {
        $rule = new CropAspectRatio(expectedRatios: ['square' => 1.0]);

        $errors = validateCrop($rule, [
            'square' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 90],
        ]);

        expect($errors)->toBe(['The square crop does not have the correct aspect ratio.']);
    });
});
