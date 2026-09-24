<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE bookings MODIFY user_id BIGINT UNSIGNED NULL');

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('guest_name')->nullable()->after('user_id');
            $table->string('guest_phone')->nullable()->after('guest_name');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['guest_name', 'guest_phone']);
        });

        DB::statement('ALTER TABLE bookings MODIFY user_id BIGINT UNSIGNED NOT NULL');

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
