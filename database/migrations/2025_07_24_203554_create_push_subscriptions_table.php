<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webpush_subscriptions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'webpush_subscriptions_user_id_fk')
                  ->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('control_room_id')->nullable();
            $table->foreign('control_room_id', 'webpush_subscriptions_control_room_id_fk')
                  ->references('id')->on('control_rooms')->onDelete('set null');
            $table->string('endpoint')->unique();
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('content_encoding')->nullable();
            $table->timestamps();
            $table->index('control_room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webpush_subscriptions');
    }
};