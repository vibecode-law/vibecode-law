<?php

namespace App\Mcp\Tools\Staff\User;

use App\Mcp\Requests\User\GetUserRequest;
use App\Mcp\Shapes\User\UserSummaryResource;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('get_user')]
#[Description('Fetch profile details for a user by id.')]
class GetUserTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate((new GetUserRequest)->rules());

        $user = User::query()->find($validated['id']);

        if ($user === null) {
            return Response::error("User with id [{$validated['id']}] was not found.");
        }

        return Response::structured(UserSummaryResource::from($user)->toArray());
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
                ->description('The stable user id.'),
        ];
    }
}
