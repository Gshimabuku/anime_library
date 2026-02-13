<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('series_id')->constrained('series')->cascadeOnDelete();

            $table->unsignedInteger('episode_no');         // 話数（映画でも番号を持つ）
            $table->string('episode_title', 255)->nullable();
            $table->date('onair_date')->nullable();
            $table->unsignedSmallInteger('duration_min');  // 分（1以上はバリデーションで担保）
            $table->unsignedInteger('is_movie')->default(0);

            $table->timestamps();

            // 同一シリーズ内で話数重複禁止
            $table->unique(['series_id', 'episode_no']);
            $table->index(['series_id', 'episode_no']);
            $table->index('is_movie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
