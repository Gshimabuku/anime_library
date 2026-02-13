<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arcs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('series_id')->constrained('series')->cascadeOnDelete();

            $table->string('name', 255); // 例: 幼少編
            $table->unsignedInteger('start_episode_no');
            $table->unsignedInteger('end_episode_no');

            $table->timestamps();

            $table->index(['series_id', 'start_episode_no', 'end_episode_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arcs');
    }
};
