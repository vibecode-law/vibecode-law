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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->text('learning_objectives')->nullable();
            $table->text('copy')->nullable();
            $table->string('asset_id')->nullable();
            $table->string('playback_id')->nullable();
            $table->unsignedTinyInteger('host')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->boolean('gated')->default(true);
            $table->string('thumbnail_filename')->nullable();
            $table->json('thumbnail_crops')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('allow_preview')->default(false);
            $table->date('publish_date')->nullable();
            $table->foreignId(column: 'course_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['course_id', 'slug']);
        });

        Schema::create('lesson_tag', function (Blueprint $table) {
            $table->foreignId(column: 'tag_id')->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'lesson_id')->constrained()->cascadeOnDelete();

            $table->primary(columns: ['tag_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_tag');
        Schema::dropIfExists('lessons');
    }
};
