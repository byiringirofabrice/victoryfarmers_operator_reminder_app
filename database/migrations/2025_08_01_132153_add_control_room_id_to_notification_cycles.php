<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_cycles', function (Blueprint $table) {
            $table->unsignedBigInteger('control_room_id')->after('site_id'); // not nullable
            $table->unique(['site_id', 'control_room_id']);
            $table->foreign('control_room_id')->references('id')->on('control_rooms')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('notification_cycles', function (Blueprint $table) {
            $table->dropForeign(['control_room_id']);
            $table->dropUnique(['site_id', 'control_room_id']);
            $table->dropColumn('control_room_id');
        });
    }
};
