<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_cycles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('control_room_id');
            $table->foreign('control_room_id', 'notification_cycles_control_room_id_fk')
                  ->references('id')->on('control_rooms')->onDelete('cascade');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->foreign('task_id', 'notification_cycles_task_id_fk')
                  ->references('id')->on('tasks')->onDelete('set null');
            $table->timestamp('last_notification_sent')->nullable();
            $table->timestamp('cycle_started_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index('control_room_id');
            $table->index('task_id');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('control_room_id');
            $table->foreign('control_room_id', 'notifications_control_room_id_fk')
                  ->references('id')->on('control_rooms')->onDelete('cascade');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->foreign('task_id', 'notifications_task_id_fk')
                  ->references('id')->on('tasks')->onDelete('set null');
            $table->unsignedBigInteger('camera_id')->nullable();
            $table->foreign('camera_id', 'notifications_camera_id_fk')
                  ->references('id')->on('cameras')->onDelete('set null');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->foreign('site_id', 'notifications_site_id_fk')
                  ->references('id')->on('sites')->onDelete('set null');
            $table->enum('notification_type', ['camera_check', 'priority_hourly', 'eye_break', 'lunch_break'])->index();
            $table->string('title');
            $table->text('message');
            $table->timestamp('scheduled_for');
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index('control_room_id');
            $table->index('task_id');
            $table->index('camera_id');
            $table->index('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('notification_cycles');
    }
};