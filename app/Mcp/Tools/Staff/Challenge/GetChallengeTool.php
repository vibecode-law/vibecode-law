<?php

namespace App\Mcp\Tools\Staff\Challenge;

use App\Mcp\Requests\Challenge\GetChallengeRequest;
use App\Mcp\Shapes\Challenge\ChallengeDetailResource;
use App\Models\Challenge\Challenge;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('get_challenge')]
#[Description('Fetch the full details of a single challenge by its stable id. No fuzzy matching — the id must be exact.')]
class GetChallengeTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate((new GetChallengeRequest)->rules());

        $challenge = Challenge::query()
            ->withTotalUpvotesCount()
            ->withCount('showcases')
            ->find($validated['id']);

        if ($challenge === null) {
            return Response::error("Challenge with id [{$validated['id']}] was not found.");
        }

        return Response::structured(ChallengeDetailResource::from($challenge)->toArray());
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
                ->description('The stable challenge id.'),
        ];
    }
}
