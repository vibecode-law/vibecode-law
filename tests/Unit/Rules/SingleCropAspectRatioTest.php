<?php

use App\Rules\SingleCropAspectRatio;

function validateSingleCrop(SingleCropAspectRatio $rule, mixed $value): array
{
    $errors = [];

    $rule->validate('thumbnail_crop', $value, function (string $message) use (&$errors) {
        $errors[] = $message;
    });

    return $errors;
}

describe('passing validation', function () {
    test('passes for crop matching expected square ratio', function () {
        $rule = new SingleCropAspectRatio(expectedRatio: 1.0);

        $errors = validateSingleCrop($rule, [
            'x' => 0, 'y' => 0, 'width' => 100, 'height' => 100,
        ]);

        expect($errors)->toBeEmpty();
    });

    test('passes for crop matching expected landscape ratio', function () {
        $rule = new SingleCropAspectRatio(expectedRatio: 16 / 9);

        $errors = validateSingleCrop($rule, [
            'x' => 0, 'y' => 0, 'width' => 1600, 'height' => 900,
        ]);

        expect($errors)->toBeEmpty();
    });

    test('passes within tolerance threshold', function () {
        $rule = new SingleCropAspectRatio(expectedRatio: 1.0);

        $errors = validateSingleCrop($rule, [
            'x' => 0, 'y' => 0, 'width' => 100, 'height' => 99,
        ]);

        expect($errors)->toBeEmpty();
    });

    test('passes when value is not an array', function () {
        $rule = new SingleCropAspectRatio(expectedRatio: 1.0);

        $errors = validateSingleCrop($rule, 'not-an-array');

        expect($errors)->toBeEmpty();
    });

    test('passes when crop is missing width', function () {
        $rule = new SingleCropAspectRatio(expectedRatio: 1.0);

        $errors = validateSingleCrop($rule, [
            'x' => 0, 'y' => 0, 'height' => 100,
        ]);

        expect($errors)->toBeEmpty();
    });

    test('passes when crop is missing height', function () {
        $rule = new SingleCropAspectRatio(expectedRatio: 1.0);

        $errors = validateSingleCrop($rule, [
            'x' => 0, 'y' => 0, 'width' => 100,
        ]);

        expect($errors)->toBeEmpty();
    });

    test('passes when crop height is zero', function () {
        $rule = new SingleCropAspectRatio(expectedRatio: 1.0);

        $errors = validateSingleCrop($rule, [
            'x' => 0, 'y' => 0, 'width' => 100, 'height' => 0,
        ]);

        expect($errors)->toBeEmpty();
    });
});

describe('failing validation', function () {
    test('fails for crop with wrong ratio', function () {
        $rule = new SingleCropAspectRatio(expectedRatio: 1.0);

        $errors = validateSingleCrop($rule, [
            'x' => 0, 'y' => 0, 'width' => 200, 'height' => 100,
        ]);

        expect($errors)->toBe(['The crop does not have the correct aspect ratio.']);
    });

    test('fails outside tolerance threshold', function () {
        $rule = new SingleCropAspectRatio(expectedRatio: 1.0);

        $errors = validateSingleCrop($rule, [
            'x' => 0, 'y' => 0, 'width' => 100, 'height' => 90,
        ]);

        expect($errors)->toBe(['The crop does not have the correct aspect ratio.']);
    });

    test('fails for landscape crop with wrong ratio', function () {
        $rule = new SingleCropAspectRatio(expectedRatio: 16 / 9);

        $errors = validateSingleCrop($rule, [
            'x' => 0, 'y' => 0, 'width' => 100, 'height' => 100,
        ]);

        expect($errors)->toBe(['The crop does not have the correct aspect ratio.']);
    });
});
