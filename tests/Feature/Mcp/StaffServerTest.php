<?php

use App\Mcp\Servers\StaffServer;
use App\Models\User;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;
use Laravel\Passport\Passport;

use function Pest\Laravel\postJson;

it('registers the staff MCP web route', function (): void {
    expect(collect(app('router')->getRoutes()->getRoutes())
        ->pluck('uri')
        ->all()
    )->toContain('mcp/staff');
});

it('publishes OAuth discovery routes', function (): void {
    $uris = collect(app('router')->getRoutes()->getRoutes())->pluck('uri');

    expect($uris)->toContain('.well-known/oauth-authorization-server');
    expect($uris)->toContain('.well-known/oauth-protected-resource');
});

describe('auth', function (): void {
    it('rejects unauthenticated requests', function (): void {
        postJson('/mcp/staff', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
        ])->assertUnauthorized();
    });

    it('rejects authenticated users without the staff.mcp.access permission', function (): void {
        /** @var User $user */
        $user = User::factory()->create();
        Passport::actingAs($user, ['mcp:use']);

        postJson('/mcp/staff', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
        ])->assertForbidden();
    });

    it('rejects tokens that lack the mcp:use scope', function (): void {
        $user = userWithPermissions(['staff.mcp.access']);

        Passport::actingAs($user, []);

        postJson('/mcp/staff', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
        ])->assertForbidden();
    });

    it('allows authenticated users with the staff.mcp.access permission', function (): void {
        $user = userWithPermissions(['staff.mcp.access']);

        Passport::actingAs($user, ['mcp:use']);

        postJson('/mcp/staff', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
        ])->assertOk();
    });

    it('rejects tools/call for every registered tool when the caller lacks the permission', function (string $toolName): void {
        /** @var User $user */
        $user = User::factory()->create();
        Passport::actingAs($user, ['mcp:use']);

        postJson('/mcp/staff', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => $toolName,
                'arguments' => [],
            ],
        ])->assertForbidden();
    })->with(function (): array {
        $reflection = new ReflectionClass(StaffServer::class);
        /** @var array<int, class-string<Tool>> $toolClasses */
        $toolClasses = $reflection->getProperty('tools')->getDefaultValue();

        return array_map(
            fn (string $toolClass): array => [app($toolClass)->name()],
            $toolClasses,
        );
    });
});

it('configures the server name, version, and instructions', function (): void {
    $reflection = new ReflectionClass(StaffServer::class);
    $attributes = collect($reflection->getAttributes())
        ->mapWithKeys(fn (ReflectionAttribute $attribute): array => [
            $attribute->getName() => $attribute->newInstance(),
        ]);

    expect($attributes[Name::class]->value)
        ->toBe('vibecode.law Staff MCP');
    expect($attributes[Version::class]->value)
        ->toBe('0.0.2');
    expect($attributes[Instructions::class]->value)
        ->toContain('staff-only');
});
