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

        $permissions = [
            'testimonial.view',
            'press-coverage.view',
            'challenge.view',
            'challenge.create',
            'challenge.update',
            'challenge.delete',
            'course.view',
            'course.create',
            'course.update',
            'course.delete',
            'lesson.view',
            'lesson.create',
            'lesson.update',
            'lesson.delete',
            'tag.view',
            'tag.create',
            'tag.update',
            'tag.delete',
            'organisation.create',
            'organisation.update',
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'site-setting.view',
            'site-setting.update',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $marketingManager = Role::firstOrCreate(['name' => 'Marketing Manager']);
        $marketingManager->syncPermissions([
            'staff.access',
            'testimonial.view',
            'testimonial.create',
            'testimonial.update',
            'testimonial.delete',
            'press-coverage.view',
            'press-coverage.create',
            'press-coverage.update',
            'press-coverage.delete',
        ]);

        $academyManager = Role::firstOrCreate(['name' => 'Academy Manager']);
        $academyManager->syncPermissions([
            'staff.access',
            'course.view',
            'course.create',
            'course.update',
            'course.delete',
            'lesson.view',
            'lesson.create',
            'lesson.update',
            'lesson.delete',
        ]);

        $challengeManager = Role::firstOrCreate(['name' => 'Challenge Manager']);
        $challengeManager->syncPermissions([
            'staff.access',
            'challenge.view',
            'challenge.create',
            'challenge.update',
            'challenge.delete',
            'organisation.create',
            'organisation.update',
        ]);

        Role::where('name', 'Moderator')->first()?->revokePermissionTo([
            'testimonial.create',
            'testimonial.update',
            'testimonial.delete',
            'press-coverage.create',
            'press-coverage.update',
            'press-coverage.delete',
        ]);
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::whereIn('name', ['Marketing Manager', 'Academy Manager', 'Challenge Manager'])->delete();

        Role::where('name', 'Moderator')->first()?->givePermissionTo([
            'testimonial.create',
            'testimonial.update',
            'testimonial.delete',
            'press-coverage.create',
            'press-coverage.update',
            'press-coverage.delete',
        ]);

        Permission::whereIn('name', [
            'testimonial.view',
            'press-coverage.view',
            'challenge.view',
            'challenge.create',
            'challenge.update',
            'challenge.delete',
            'course.view',
            'course.create',
            'course.update',
            'course.delete',
            'lesson.view',
            'lesson.create',
            'lesson.update',
            'lesson.delete',
            'tag.view',
            'tag.create',
            'tag.update',
            'tag.delete',
            'organisation.create',
            'organisation.update',
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'site-setting.view',
            'site-setting.update',
        ])->delete();
    }
};
