<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->text('learning_objectives')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedTinyInteger('experience_level')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('started_count')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->boolean('allow_preview')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->date('publish_date')->nullable();
            $table->string('thumbnail_filename')->nullable();
            $table->json('thumbnail_crops')->nullable();
            $table->timestamps();
        });

        Schema::create('course_tag', function (Blueprint $table) {
            $table->foreignId(column: 'tag_id')->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'course_id')->constrained()->cascadeOnDelete();

            $table->primary(columns: ['tag_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_tag');
        Schema::dropIfExists('courses');
    }
};
