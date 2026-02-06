<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('press_coverage', function (Blueprint $table) {
            $table->id();

            // Article information
            $table->string('title');
            $table->string('publication_name');
            $table->date('publication_date');
            $table->string('url');
            $table->text('excerpt')->nullable();

            // Thumbnail - follows showcase thumbnail pattern
            $table->string('thumbnail_extension', 10)->nullable();
            $table->json('thumbnail_crop')->nullable();

            // Display settings
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('display_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['is_published', 'display_order']);
            $table->index('publication_date');
        });

        $this->seedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('press_coverage');
    }

    private function seedPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'press-coverage.create',
            'press-coverage.update',
            'press-coverage.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $moderatorRole = Role::firstOrCreate(['name' => 'Moderator']);
        $moderatorRole->givePermissionTo($permissions);
    }
};
