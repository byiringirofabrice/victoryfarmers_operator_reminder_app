<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'control_room_id',
        'site_id',
        'camera_ids',
        'title',
        'message',
        'duration_minutes',
        'is_priority',
        'is_break',
        'break_type',
        'scheduled_time',
        'frequency_hours',
        'is_active',
    ];

    protected $casts = [
        'camera_ids' => 'array',
        'break_type' => 'string',
    ];

    // Relationships
    public function controlRoom()
    {
        return $this->belongsTo(ControlRoom::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
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