<?php

// database/migrations/xxxx_xx_xx_create_push_subscriptions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePushSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('control_room_id')->nullable()->constrained('control_rooms')->nullOnDelete();
            $table->text('endpoint')->unique();
            $table->string('p256dh')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('content_encoding')->nullable();
            $table->json('subscription')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('push_subscriptions');
    }
}
