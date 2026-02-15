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
            $table->string('tagline');
            $table->text('description');
            $table->text('learning_objectives')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedTinyInteger('experience_level')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('started_count')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->boolean('visible')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->date('publish_date')->nullable();
            $table->string('thumbnail_extension')->nullable();
            $table->json('thumbnail_crops')->nullable();
            $table->foreignId(column: 'user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('course_course_tag', function (Blueprint $table) {
            $table->foreignId(column: 'course_id')->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'course_tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(columns: ['course_id', 'course_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_course_tag');
        Schema::dropIfExists('courses');
    }
};
