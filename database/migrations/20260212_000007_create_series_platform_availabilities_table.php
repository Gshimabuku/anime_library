<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series_platform_availabilities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('series_id')->constrained('series')->cascadeOnDelete();

            $table->foreignId('platform_id')->constrained('platforms')->restrictOnDelete();

            // 1:無料 2:ポイント必要 3:レンタル 4:見放題(任意)
            $table->unsignedTinyInteger('watch_condition')->default(1);

            $table->string('note', 255)->nullable();

            $table->timestamps();

            $table->unique(['series_id', 'platform_id']);
            $table->index(['platform_id', 'watch_condition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series_platform_availabilities');
    }
};
