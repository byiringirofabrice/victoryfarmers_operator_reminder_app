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
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();

        $table->foreignId('control_room_id')->constrained()->cascadeOnDelete();
        $table->json('camera_ids'); // store multiple camera IDs
        $table->enum('status', ['pending', 'done', 'skipped'])->default('sent');

        $table->timestamp('notified_at')->nullable(); // when was it sent
        $table->timestamp('completed_at')->nullable(); // when operator marked as done

        
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
