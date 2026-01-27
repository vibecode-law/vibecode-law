<?php

use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('returns 200 status', function () {
    get('/showcase/help/how-it-works')
        ->assertOk();
});

test('renders correct Inertia component', function () {
    get('/showcase/help/how-it-works')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/help/how-it-works')
        );
});
