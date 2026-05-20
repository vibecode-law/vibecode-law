<?php

namespace App\Mcp\Tools\Staff\PracticeArea;

use App\Mcp\Shapes\PracticeAreaResource;
use App\Models\PracticeArea;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Spatie\LaravelData\DataCollection;

#[Name('list_practice_areas')]
#[Description('List all practice areas (id, name, slug). Use the slug as the practice_area filter on list_showcases.')]
class ListPracticeAreasTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $items = PracticeAreaResource::collect(
            PracticeArea::query()->orderBy('name')->get(),
            DataCollection::class,
        )->toArray();

        return Response::structured([
            'items' => $items,
            'total_count' => count($items),
        ]);
    }
}
