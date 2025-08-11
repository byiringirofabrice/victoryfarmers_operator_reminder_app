<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('camera_status_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('camera_id');
            $table->foreign('camera_id', 'camera_status_logs_camera_id_fk')
                  ->references('id')->on('cameras')->onDelete('cascade');
            $table->boolean('is_online');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id', 'camera_status_logs_country_id_fk')
                  ->references('id')->on('countries')->onDelete('set null');
            $table->timestamp('changed_at');
            $table->ipAddress('changed_by_ip')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index('camera_id');
            $table->index('country_id');
        });

        Schema::create('branch_status_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreign('branch_id', 'branch_status_logs_branch_id_fk')
                  ->references('id')->on('branches')->onDelete('set null');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->foreign('site_id', 'branch_status_logs_site_id_fk')
                  ->references('id')->on('sites')->onDelete('set null');
            $table->boolean('is_online');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id', 'branch_status_logs_country_id_fk')
                  ->references('id')->on('countries')->onDelete('set null');
            $table->timestamp('changed_at');
            $table->ipAddress('changed_by_ip')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index('branch_id');
            $table->index('site_id');
            $table->index('country_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_status_logs');
        Schema::dropIfExists('camera_status_logs');
    }
};