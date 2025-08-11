<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id', 'sites_branch_id_fk')
                  ->references('id')->on('branches')->onDelete('cascade');
            $table->string('name'); // e.g., Nairobi Hatchery
            $table->boolean('is_priority')->default(false)->index();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(true)->index();
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['branch_id', 'name']);
            $table->index('branch_id');
        });

        Schema::create('cameras', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->foreign('site_id', 'cameras_site_id_fk')
                  ->references('id')->on('sites')->onDelete('cascade');
            $table->unsignedBigInteger('control_room_id');
            $table->foreign('control_room_id', 'cameras_control_room_id_fk')
                  ->references('id')->on('control_rooms')->onDelete('cascade');
            $table->string('name'); // e.g., Kagano Lake Shore Cam
            $table->enum('camera_type', ['ptz', 'fixed'])->default('fixed');
            $table->boolean('is_priority')->default(false)->index();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['site_id', 'name']);
            $table->index('site_id');
            $table->index('control_room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cameras');
        Schema::dropIfExists('sites');
    }
};