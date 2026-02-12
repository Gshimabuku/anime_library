<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anime_titles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('title_kana', 255)->nullable();

            // 1: cour_only, 2: cour_plus_movie, 3: movie_only
            $table->unsignedTinyInteger('work_type')->default(1);

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('title');
            $table->index('title_kana');
            $table->index('work_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anime_titles');
    }
};
