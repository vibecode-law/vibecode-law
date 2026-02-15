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
            $table->string('slug')->unique();
            $table->string('tagline');
            $table->text('description');
            $table->text('learning_objectives')->nullable();
            $table->text('copy')->nullable();
            $table->text('transcript')->nullable();
            $table->string('asset_id')->nullable();
            $table->string('playback_id')->nullable();
            $table->string('caption_track_id')->nullable();
            $table->unsignedTinyInteger('host')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->boolean('gated')->default(true);
            $table->string('thumbnail_extension')->nullable();
            $table->json('thumbnail_crops')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->foreignId(column: 'course_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
