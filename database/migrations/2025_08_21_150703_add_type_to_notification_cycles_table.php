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
        Schema::table('notification_cycles', function (Blueprint $table) {
            // Add control room ID to link cycles to a specific control room
           
            // Add last country index for Kenya generator
            $table->integer('last_country_index')->default(0)->after('last_camera_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_cycles', function (Blueprint $table) {
            $table->dropForeign(['control_room_id']);
            $table->dropColumn(['control_room_id', 'last_country_index']);
        });
    }
};
