<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'staff.mcp.access']);

        $role = Role::firstOrCreate(['name' => 'Staff MCP User']);
        $role->syncPermissions([
            'staff.mcp.access',
        ]);
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::where('name', 'Staff MCP User')->delete();

        Permission::where('name', 'staff.mcp.access')->delete();
    }
};
