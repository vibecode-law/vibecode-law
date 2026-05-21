<?php

use App\Mcp\Shapes\Showcase\ShowcaseColumn;
use App\Mcp\Shapes\Showcase\ShowcaseDetailResource;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\LazyLoadingViolationException;

it('renders a key for every list column, guarding against enum and resource drift', function (): void {
    $showcase = Showcase::factory()->approved()->create();
    $showcase->practiceAreas()->sync([PracticeArea::factory()->create()->id]);
    $showcase->upvoters()->sync([User::factory()->create()->id]);

    $loaded = Showcase::query()
        ->with(['user', 'images', 'practiceAreas', 'challenges'])
        ->withCount('upvoters')
        ->findOrFail($showcase->id);

    $columns = ShowcaseColumn::values();

    $rendered = ShowcaseDetailResource::from($loaded)->include(...$columns)->toArray();

    expect(array_diff($columns, array_keys($rendered)))->toBe([]);
});

it('renders only the summary fields when nothing is included', function (): void {
    $showcase = Showcase::factory()->approved()->create();

    $array = ShowcaseDetailResource::from($showcase)->toArray();

    expect(array_keys($array))->toEqualCanonicalizing([
        'id', 'slug', 'title', 'tagline', 'status', 'submitted_date', 'user_id',
    ]);
});

it('does not touch a relation when its field is not included', function (): void {
    Showcase::factory()->count(2)->approved()->create();

    // A multi-row result enables lazy-loading prevention; rendering without
    // including the user field must not read the relation.
    $rendered = Showcase::query()->get()->map(
        fn (Showcase $showcase): array => ShowcaseDetailResource::from($showcase)->toArray(),
    );

    expect($rendered->first())->not->toHaveKey('user');
});

it('fails loudly when a relation-backed field is included but not eager loaded', function (): void {
    Showcase::factory()->count(2)->approved()->create();

    $showcases = Showcase::query()->get();

    expect(fn (): Collection => $showcases->map(
        fn (Showcase $showcase): array => ShowcaseDetailResource::from($showcase)->include('user')->toArray(),
    ))->toThrow(LazyLoadingViolationException::class);
});

it('renders an included relation when it has been eager loaded', function (): void {
    $first = Showcase::factory()->approved()->create();
    Showcase::factory()->approved()->create();

    $showcases = Showcase::query()->with('user')->get();

    $rendered = $showcases->map(
        fn (Showcase $showcase): array => ShowcaseDetailResource::from($showcase)->include('user')->toArray(),
    );

    expect($rendered->firstWhere('id', $first->id))->toHaveKey('user.id', $first->user_id);
});
