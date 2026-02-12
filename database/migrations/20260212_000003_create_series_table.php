<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series', function (Blueprint $table) {
            $table->id();

            $table->foreignId('anime_title_id')->constrained('anime_titles')->cascadeOnDelete();

            $table->string('name', 255);
            $table->unsignedInteger('series_order')->default(1);

            // 1: series, 2: special, 3: movie
            $table->unsignedTinyInteger('format_type')->default(1);

            $table->timestamps();

            $table->unique(['anime_title_id', 'name']);
            $table->index(['anime_title_id', 'series_order']);
            $table->index('format_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
