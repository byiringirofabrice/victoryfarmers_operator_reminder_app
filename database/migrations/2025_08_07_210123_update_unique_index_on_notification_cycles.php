<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Check if the single-column index exists before dropping
        $indexExists = DB::select("SHOW INDEX FROM notification_cycles WHERE Key_name = 'notification_cycles_site_id_unique'");
        if (!empty($indexExists)) {
            Schema::table('notification_cycles', function (Blueprint $table) {
                $table->dropUnique('notification_cycles_site_id_unique');
            });
        }

        // Check if compound index already exists before adding
        $compoundIndex = DB::select("SHOW INDEX FROM notification_cycles WHERE Key_name = 'notification_cycles_site_id_control_room_id_unique'");
        if (empty($compoundIndex)) {
            Schema::table('notification_cycles', function (Blueprint $table) {
                $table->unique(['site_id', 'control_room_id']);
            });
        }
    }

    public function down()
    {
        Schema::table('notification_cycles', function (Blueprint $table) {
            $table->dropUnique(['site_id', 'control_room_id']);
            $table->unique(['site_id']);
        });
    }
};
