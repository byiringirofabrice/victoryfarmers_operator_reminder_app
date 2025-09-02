<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->unsignedBigInteger('site_id')->nullable()->after('control_room_id');
        $table->index('site_id'); // Faster filtering
    });
}

public function down()
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->dropIndex(['site_id']);
        $table->dropColumn('site_id');
    });
}

};
