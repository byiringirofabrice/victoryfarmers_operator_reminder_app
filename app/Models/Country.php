<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'code', 'timezone'];

    // Relationships
    public function controlRooms()
    {
        return $this->hasMany(ControlRoom::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function cameraStatusLogs()
    {
        return $this->hasMany(CameraStatusLog::class);
    }

    public function branchStatusLogs()
    {
        return $this->hasMany(BranchStatusLog::class);
    }
    public function cameras()
{
    return $this->hasManyThrough(
        \App\Models\Camera::class,
        \App\Models\ControlRoom::class,
        'country_id',       // Foreign key on control_rooms
        'control_room_id',  // Foreign key on cameras
        'id',               // Local key on countries
        'id'                // Local key on control_rooms
    );
}

}