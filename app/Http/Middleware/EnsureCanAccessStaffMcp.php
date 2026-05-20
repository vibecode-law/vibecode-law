<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessStaffMcp
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User === false || $user->hasPermissionTo('staff.mcp.access', 'web') === false) {
            $description = 'The authenticated user does not have permission to access this MCP server.';

            return new JsonResponse(
                ['error' => 'insufficient_scope', 'error_description' => $description],
                Response::HTTP_FORBIDDEN,
                ['WWW-Authenticate' => sprintf('Bearer error="insufficient_scope", error_description="%s"', $description)],
            );
        }

        if ($user->tokenCan('mcp:use') === false) {
            $description = 'The access token is missing the required "mcp:use" scope.';

            return new JsonResponse(
                ['error' => 'insufficient_scope', 'error_description' => $description],
                Response::HTTP_FORBIDDEN,
                ['WWW-Authenticate' => sprintf('Bearer error="insufficient_scope", scope="mcp:use", error_description="%s"', $description)],
            );
        }

        return $next($request);
    }
}
