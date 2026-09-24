<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
