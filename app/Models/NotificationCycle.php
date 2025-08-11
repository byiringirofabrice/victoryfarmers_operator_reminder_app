<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationCycle extends Model
{
    protected $fillable = [
        'control_room_id',
        'task_id',
        'last_notification_sent',
        'cycle_started_at',
        'is_active',
    ];

    protected $casts = [
        'last_notification_sent' => 'datetime',
        'cycle_started_at' => 'datetime',
    ];

    // Relationships
    public function controlRoom()
    {
        return $this->belongsTo(ControlRoom::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}