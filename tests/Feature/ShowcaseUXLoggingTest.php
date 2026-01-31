<?php

use App\Enums\SourceStatus;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

describe('showcase create page logging', function () {
    test('logs user access to showcaseUX channel', function () {
        Log::shouldReceive('channel')
            ->with('showcaseUX')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('User accessed showcase page', \Mockery::on(function ($context) {
                return isset($context['name']);
            }))
            ->once();

        /** @var User */
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        actingAs($user);

        get(route('showcase.manage.create'));
    });

    test('logs correct user name in context', function () {
        Log::shouldReceive('channel')
            ->with('showcaseUX')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('User accessed showcase page', ['name' => 'Jane Smith'])
            ->once();

        /** @var User */
        $user = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        actingAs($user);

        get(route('showcase.manage.create'));
    });
});

describe('showcase store validation logging', function () {
    beforeEach(function () {
        Storage::fake('public');
    });

    test('logs validation success on showcase store', function () {
        Log::shouldReceive('channel')
            ->with('showcaseUX')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('Showcase validation succeeded', \Mockery::on(function ($context) {
                return isset($context['route'])
                    && isset($context['name'])
                    && $context['route'] === 'showcase.manage.store'
                    && $context['name'] === 'John Doe';
            }))
            ->once();

        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        actingAs($user);

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'Test Showcase',
            'tagline' => 'Test tagline',
            'description' => 'Test description',
            'key_features' => 'Test key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);
    });

    test('logs validation failure on showcase store', function () {
        Log::shouldReceive('channel')
            ->with('showcaseUX')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('Showcase validation failed', \Mockery::on(function ($context) {
                return isset($context['route'])
                    && isset($context['name'])
                    && isset($context['errors'])
                    && isset($context['data'])
                    && $context['route'] === 'showcase.manage.store'
                    && $context['name'] === 'Jane Smith'
                    && isset($context['errors']['title']);
            }))
            ->once();

        /** @var User */
        $user = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        actingAs($user);

        post(route('showcase.manage.store'), [
            'title' => '', // Invalid - required
        ]);
    });
});

describe('showcase update validation logging', function () {
    test('logs validation success on showcase update', function () {
        Log::shouldReceive('channel')
            ->with('showcaseUX')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('Showcase validation succeeded', \Mockery::on(function ($context) {
                return isset($context['route'])
                    && isset($context['name'])
                    && $context['route'] === 'showcase.manage.update'
                    && $context['name'] === 'Alice Johnson';
            }))
            ->once();

        /** @var User */
        $user = User::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
        ]);

        $showcase = Showcase::factory()
            ->has(ShowcaseImage::factory(), 'images')
            ->for($user, 'user')
            ->create();

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);
    });

    test('logs validation failure on showcase update', function () {
        Log::shouldReceive('channel')
            ->with('showcaseUX')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('Showcase validation failed', \Mockery::on(function ($context) {
                return isset($context['route'])
                    && isset($context['name'])
                    && isset($context['errors'])
                    && isset($context['data'])
                    && $context['route'] === 'showcase.manage.update'
                    && $context['name'] === 'Bob Williams'
                    && isset($context['errors']['title']);
            }))
            ->once();

        /** @var User */
        $user = User::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Williams',
        ]);

        $showcase = Showcase::factory()
            ->has(ShowcaseImage::factory(), 'images')
            ->for($user, 'user')
            ->create();

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'title' => '', // Invalid - required
        ]);
    });
});
