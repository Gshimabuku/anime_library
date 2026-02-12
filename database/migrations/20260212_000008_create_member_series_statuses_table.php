<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_series_statuses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();

            $table->foreignId('series_id')->constrained('series')->cascadeOnDelete();

            // 0: unwatched, 1: watching, 2: watched
            $table->unsignedTinyInteger('status')->default(0);

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['member_id', 'series_id']);
            $table->index('status');
            $table->index(['member_id', 'status']);
            $table->index(['series_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_series_statuses');
    }
};
