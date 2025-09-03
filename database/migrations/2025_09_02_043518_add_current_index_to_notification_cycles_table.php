<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('notification_cycles', function (Blueprint $table) {
        $table->integer('current_index')->default(0)->after('last_camera_ids');
    });
}

public function down()
{
    Schema::table('notification_cycles', function (Blueprint $table) {
        $table->dropColumn('current_index');
    });
}
};
