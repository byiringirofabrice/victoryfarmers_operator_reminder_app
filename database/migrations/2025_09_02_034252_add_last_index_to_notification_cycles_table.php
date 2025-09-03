<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notification_cycles', function (Blueprint $table) {
            $table->integer('last_index')->default(-1)->after('type');
            $table->timestamp('last_notified_at')->nullable()->after('last_index');
        });
    }

    public function down(): void
    {
        Schema::table('notification_cycles', function (Blueprint $table) {
            $table->dropColumn(['last_index', 'last_notified_at']);
        });
    }
};
