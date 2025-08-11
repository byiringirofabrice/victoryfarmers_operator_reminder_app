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
}