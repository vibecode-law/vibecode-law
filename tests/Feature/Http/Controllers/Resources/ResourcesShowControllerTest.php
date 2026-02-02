<?php

use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('returns 200 for valid resources page slugs', function (string $slug) {
    get("/resources/{$slug}")
        ->assertOk();
})->with([
    'what-is-vibecoding',
    'start-vibecoding',
    'risks-of-vibecoding',
    'responsible-vibecoding',
]);

test('renders correct Inertia component', function () {
    get('/resources/what-is-vibecoding')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('resources/show')
        );
});

test('returns correct props', function () {
    get('/resources/what-is-vibecoding')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('resources/show')
            ->has('title')
            ->has('slug')
            ->has('content')
        );
});

test('content is rendered HTML', function () {
    get('/resources/what-is-vibecoding')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('resources/show')
            ->where('content', fn (string $content) => str_contains($content, '<h1>'))
        );
});

test('returns 404 for invalid slugs', function (string $slug) {
    get("/resources/{$slug}")
        ->assertNotFound();
})->with([
    'invalid-page',
    'nonexistent',
    'terms-of-use',
    'index',
]);

test('each configured resources page returns correct data', function (string $slug, string $title) {
    get("/resources/{$slug}")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('resources/show')
            ->where('title', $title)
            ->where('slug', $slug)
            ->has('content')
        );
})->with([
    ['what-is-vibecoding', 'What is Vibecoding?'],
    ['start-vibecoding', 'Start Vibecoding'],
    ['risks-of-vibecoding', 'Risks of Vibecoding'],
    ['responsible-vibecoding', 'Responsible Vibecoding'],
]);
