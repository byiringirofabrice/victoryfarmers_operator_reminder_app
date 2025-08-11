<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('country_id');
            $table->foreign('country_id', 'branches_country_id_fk')
                  ->references('id')->on('countries')->onDelete('cascade');
            $table->unsignedBigInteger('control_room_id');
            $table->foreign('control_room_id', 'branches_control_room_id_fk')
                  ->references('id')->on('control_rooms')->onDelete('cascade');
            $table->string('name'); // e.g., Nairobi
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(true)->index();
            $table->timestamps();
            $table->unique(['country_id', 'name']);
            $table->index('country_id');
            $table->index('control_room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};