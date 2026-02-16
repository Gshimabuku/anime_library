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

            $table->string('episode_no', 20)->nullable(); // 話数（自由入力：01. / 第1話 / Episode01 / Ⅰ 等、省略可）
            $table->string('episode_title', 255)->nullable();
            $table->unsignedSmallInteger('onair_date')->nullable();
            $table->unsignedSmallInteger('duration_min');  // 分（1以上はバリデーションで担保）
            $table->unsignedInteger('sort_order')->default(0); // 表示順

            $table->timestamps();

            // 同一シリーズ内で話数重複禁止（NULLは許容）
            $table->unique(['series_id', 'episode_no']);
            $table->index(['series_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
