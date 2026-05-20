<?php

namespace App\Mcp\Tools\Staff\Showcase;

use App\Mcp\Requests\Showcase\GetShowcaseRequest;
use App\Mcp\Shapes\Showcase\ShowcaseDetailResource;
use App\Models\Showcase\Showcase;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('get_showcase')]
#[Description('Fetch the full details of a single showcase by its stable id. No fuzzy matching — the id must be exact.')]
class GetShowcaseTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate((new GetShowcaseRequest)->rules());

        $showcase = Showcase::query()
            ->with(['practiceAreas', 'images'])
            ->withCount(['upvoters'])
            ->find($validated['id']);

        if ($showcase === null) {
            return Response::error("Showcase with id [{$validated['id']}] was not found.");
        }

        return Response::structured(ShowcaseDetailResource::from($showcase)->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->min(1)
                ->required()
                ->description('The stable showcase id.'),
        ];
    }
}
