<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable();
        });

        Schema::table('book_user', function (Blueprint $table) {
            $table->timestamp('last_read_at')->nullable();
        });

        // すでに参加している人は「既読」から始める(いきなり○だらけにならないように)
        DB::table('book_user')->update(['last_read_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('last_activity_at');
        });

        Schema::table('book_user', function (Blueprint $table) {
            $table->dropColumn('last_read_at');
        });
    }
};