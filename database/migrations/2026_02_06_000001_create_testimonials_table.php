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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // If not linked to user, store these directly
            $table->string('name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('organisation')->nullable();

            // Testimonial content
            $table->text('content');

            // Avatar/thumbnail - follows avatar storage pattern
            $table->string('avatar_path')->nullable();
            $table->json('avatar_crop')->nullable();

            // Display settings
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('display_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['is_published', 'display_order']);
        });

        $this->seedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }

    private function seedPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'testimonial.create',
            'testimonial.update',
            'testimonial.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $moderatorRole = Role::firstOrCreate(['name' => 'Moderator']);
        $moderatorRole->givePermissionTo($permissions);
    }
};
