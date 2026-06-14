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
        Schema::table('challenges', function (Blueprint $table) {
            $table->boolean('live_view_enabled')->default(false)->after('is_featured');
            $table->string('live_view_access_token')->nullable()->after('live_view_enabled');
            $table->string('live_view_heading')->nullable()->after('live_view_access_token');
            $table->string('live_view_subheading')->nullable()->after('live_view_heading');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn([
                'live_view_enabled',
                'live_view_access_token',
                'live_view_heading',
                'live_view_subheading',
            ]);
        });
    }
};
