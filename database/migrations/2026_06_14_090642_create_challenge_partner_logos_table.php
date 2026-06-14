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
        Schema::create('challenge_partner_logos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('filename');
            $table->string('href')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('invert_in_dark')->default(false);
            $table->timestamps();

            $table->index(['challenge_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_partner_logos');
    }
};
