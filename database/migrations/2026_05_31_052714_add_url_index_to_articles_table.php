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
        // ⚡ Bolt Optimization: Added index on `url` column as it is used heavily
        // in FetchNewsCommand via `Article::updateOrCreate(['url' => $item['url']])`
        // to prevent full table scans and speed up background news syncing.
        Schema::table('articles', function (Blueprint $table) {
            $table->index('url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['url']);
        });
    }
};
