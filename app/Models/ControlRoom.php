<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlRoom extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'notification_interval_minutes',
        'is_active',
    ];

    // Relationships
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function cameras()
    {
        return $this->hasMany(Camera::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function notificationCycles()
    {
        return $this->hasMany(NotificationCycle::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function site()
{
    return $this->belongsTo(Site::class);
}

    public function countryCameras()
    {
        return $this->hasManyThrough(
            Camera::class,
            Country::class,
            'id',               // Foreign key on countries
            'control_room_id', // Foreign key on cameras
            'country_id',      // Local key on control_rooms
            'id'               // Local key on countries
        );
    }
}