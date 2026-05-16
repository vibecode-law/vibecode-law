<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

uses(
    TestCase::class,
)->in('Feature');

uses(
    TestCase::class,
)->in('Browser')->group('browser');

/**
 * Create a user with the given permissions granted directly (no role assignment).
 * Includes staff.access so the staff gate passes.
 *
 * @param  array<int, string>  $permissions
 */
function userWithPermissions(array $permissions): User
{
    /** @var User $user */
    $user = User::factory()->create();

    foreach (['staff.access', ...$permissions] as $name) {
        Permission::findOrCreate($name);
    }

    $user->givePermissionTo(['staff.access', ...$permissions]);

    return $user;
}
