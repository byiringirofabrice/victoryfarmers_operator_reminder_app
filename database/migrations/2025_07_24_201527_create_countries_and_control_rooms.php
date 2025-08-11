<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name'); // e.g., Rwanda, Kenya
            $table->string('code', 2)->unique(); // ISO 3166-1 alpha-2 (e.g., RW, KE)
            $table->string('timezone', 30)->index(); // e.g., Africa/Kigali, Africa/Nairobi
            $table->timestamps();
        });

        Schema::create('control_rooms', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('country_id');
            $table->foreign('country_id', 'control_rooms_country_id_fk')
                  ->references('id')->on('countries')->onDelete('cascade');
            $table->string('name'); // e.g., Rwanda Control Room
            $table->integer('notification_interval_minutes')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['country_id', 'name']);
            $table->index('country_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_rooms');
        Schema::dropIfExists('countries');
    }
};