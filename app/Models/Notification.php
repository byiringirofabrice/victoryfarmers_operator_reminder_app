<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'control_room_id',
        'task_id',
        'camera_id',
        'site_id',
        'notification_type',
        'title',
        'message',
        'scheduled_for',
        'sent_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'notification_type' => 'string',
        'status' => 'string',
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
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

    public function camera()
    {
        return $this->belongsTo(Camera::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}