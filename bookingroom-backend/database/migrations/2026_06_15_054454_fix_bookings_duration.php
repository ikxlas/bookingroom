<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('duration')->nullable();
            if (Schema::hasColumn('bookings', 'start_time')) {
                $table->dropColumn(['start_time', 'end_time']);
            }
        });
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'open_time')) {
                $table->dropColumn(['open_time', 'close_time']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
};
