<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'country_id',
        'control_room_id',
        'name',
        'is_active',
        'is_online',
    ];

    // Relationships
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function controlRoom()
    {
        return $this->belongsTo(ControlRoom::class);
    }

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function branchStatusLogs()
    {
        return $this->hasMany(BranchStatusLog::class);
    }
    public function branchCameras()
{
    return $this->hasManyThrough(
        \App\Models\Camera::class,
        \App\Models\Branch::class,
        'country_id',         // Foreign key on branches
        'site_id',            // Foreign key on cameras
        'id',                 // Local key on countries
        'id'                  // Local key on branches
    )->join('sites', 'sites.id', '=', 'cameras.site_id');
}
}