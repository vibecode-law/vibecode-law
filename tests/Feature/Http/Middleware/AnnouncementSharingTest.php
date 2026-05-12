<?php

use App\Models\SiteSetting;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('shares null announcement when none is set', function () {
    get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('announcement', null)
        );
});

test('shares rendered HTML announcement when one is set', function () {
    SiteSetting::factory()->announcement(value: 'Hello **world**')->create();

    get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('announcement', fn (string $html) => str_contains($html, '<strong>world</strong>'))
        );
});
