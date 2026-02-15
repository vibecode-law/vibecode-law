<?php

use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('returns 200 for valid guide page slugs', function (string $slug) {
    get("/learn/guides/{$slug}")
        ->assertOk();
})->with([
    'what-is-vibecoding',
    'start-vibecoding',
    'risks-of-vibecoding',
    'responsible-vibecoding',
]);

test('renders correct Inertia component', function () {
    get('/learn/guides/what-is-vibecoding')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('learn/guides/show')
        );
});

test('returns correct props', function () {
    get('/learn/guides/what-is-vibecoding')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('learn/guides/show')
            ->has('title')
            ->has('slug')
            ->has('content')
        );
});

test('content is rendered HTML', function () {
    get('/learn/guides/what-is-vibecoding')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('learn/guides/show')
            ->where('content', fn (string $content) => str_contains($content, '<h1>'))
        );
});

test('returns 404 for invalid slugs', function (string $slug) {
    get("/learn/guides/{$slug}")
        ->assertNotFound();
})->with([
    'invalid-page',
    'nonexistent',
    'terms-of-use',
    'index',
]);

test('each configured guide page returns correct data', function (string $slug, string $title) {
    get("/learn/guides/{$slug}")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('learn/guides/show')
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

describe('redirects', function () {
    test('old /resources URL permanently redirects to /learn', function () {
        get('/resources')
            ->assertRedirect('/learn')
            ->assertStatus(301);
    });

    test('old /resources/{slug} URL permanently redirects to /learn/guides/{slug}', function () {
        get('/resources/what-is-vibecoding')
            ->assertRedirect('/learn/guides/what-is-vibecoding')
            ->assertStatus(301);
    });
});
