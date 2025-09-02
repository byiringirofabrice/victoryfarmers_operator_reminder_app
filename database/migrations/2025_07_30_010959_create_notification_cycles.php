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
       Schema::create('notification_cycles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->unique();
            $table->json('last_camera_ids')->nullable(); 
            // last cameras notified (array)
            $table->timestamp('last_notified_at')->nullable();
               
    

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_cycles');
    }
};
