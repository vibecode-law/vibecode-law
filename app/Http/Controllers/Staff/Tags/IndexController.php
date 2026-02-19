<?php

namespace App\Http\Controllers\Staff\Tags;

use App\Enums\TagType;
use App\Http\Controllers\BaseController;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class IndexController extends BaseController
{
    public function __invoke(): Response
    {
        $this->authorize('viewAny', Tag::class);

        return Inertia::render('staff-area/tags/index', [
            'tags' => $this->getTags(),
            'tagTypes' => collect(TagType::cases())->map(
                fn (TagType $type) => $type->forFrontend(),
            ),
        ]);
    }

    /**
     * @return DataCollection<int, TagResource>
     */
    private function getTags(): DataCollection
    {
        $tags = Tag::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return TagResource::collect($tags, DataCollection::class);
    }
}
