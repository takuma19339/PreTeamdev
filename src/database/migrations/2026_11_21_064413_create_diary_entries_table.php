<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diary_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('weather', 10)->nullable(); // sunny / cloudy / rainy
            $table->text('body')->nullable();
            $table->timestamps();

            // 1人が、1冊の本の、1日に書ける日記は1件
            $table->unique(['book_id', 'user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diary_entries');
    }
};