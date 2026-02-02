<?php

use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('returns 200 status', function () {
    get('/about')
        ->assertOk();
});

test('renders correct Inertia component', function () {
    get('/about')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('about/index')
        );
});

test('returns correct props', function () {
    get('/about')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('about/index')
            ->has('title')
            ->has('content')
            ->has('children', 4)
        );
});

test('returns correct title', function () {
    get('/about')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('title', 'About vibecode.law')
        );
});

test('content contains expected HTML', function () {
    get('/about')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('content', fn (string $content) => str_contains($content, '<h1>'))
        );
});

test('children array has correct structure', function () {
    get('/about')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('children', 4)
            ->has('children.0', fn (AssertableInertia $child) => $child
                ->has('name')
                ->where('slug', 'the-community')
                ->has('summary')
                ->has('icon')
                ->where('route', route(name: 'about.community'))
            )
            ->has('children.1', fn (AssertableInertia $child) => $child
                ->has('name')
                ->where('slug', 'submission-process')
                ->has('summary')
                ->has('icon')
                ->where('route', route(name: 'about.show', parameters: ['slug' => 'submission-process']))
            )
            ->has('children.2', fn (AssertableInertia $child) => $child
                ->has('name')
                ->where('slug', 'moderation-process')
                ->has('summary')
                ->has('icon')
                ->where('route', route(name: 'about.show', parameters: ['slug' => 'moderation-process']))
            )
            ->has('children.3', fn (AssertableInertia $child) => $child
                ->has('name')
                ->where('slug', 'contact')
                ->has('summary')
                ->has('icon')
                ->where('route', route(name: 'about.show', parameters: ['slug' => 'contact']))
            )
        );
});
