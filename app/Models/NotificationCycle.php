<?php
// app/Models/NotificationCycle.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationCycle extends Model
{
    protected $fillable = [
        'site_id',
        'last_camera_ids',
        'last_notified_at',
    ];

    protected $casts = [
        'last_camera_ids' => 'array',
        'last_notified_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
