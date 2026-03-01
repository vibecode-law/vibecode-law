<?php

use App\Actions\Challenge\GenerateInviteCodeAction;

test('generates a 16-character string', function () {
    $action = new GenerateInviteCodeAction;
    $code = $action->generate();

    expect($code)->toBeString()
        ->and(strlen($code))->toBe(16);
});

test('generated codes are unique', function () {
    $action = new GenerateInviteCodeAction;

    $codes = collect(range(1, 10))->map(fn () => $action->generate());

    expect($codes->unique())->toHaveCount(10);
});
