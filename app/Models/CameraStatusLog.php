<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CameraStatusLog extends Model
{
    protected $fillable = [
        'camera_id',
        'is_online',
        'country_id',
        'changed_at',
        'changed_by_ip',
        'reason',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // Relationships
    public function camera()
    {
        return $this->belongsTo(Camera::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}