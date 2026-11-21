<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete(); // null = 全員(共有)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 100);
            $table->date('due_date')->nullable();
            $table->boolean('is_done')->default(false);
            $table->timestamps();

            $table->index(['book_id', 'is_done']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};