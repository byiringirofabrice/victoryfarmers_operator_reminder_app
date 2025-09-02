<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_room_id',
        'site_id',           // tasks have site_id column
        'camera_ids',
        'status',
        'notified_at',
        'completed_at',
        'type',
    ];

    protected $casts = [
        'camera_ids' => 'array',
        'notified_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function controlRoom(): BelongsTo
    {
        return $this->belongsTo(ControlRoom::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    // Return collection of Camera models for the camera_ids array
    public function cameras()
    {
        return Camera::whereIn('id', $this->camera_ids ?? [])->get();
    }

    // Accessor for Site Name (for Filament display)
    public function getSiteNameAttribute(): string
    {
        return $this->site?->name ?? 'N/A';
    }

    // Accessor for Cameras Names (comma separated)
    public function getCameraNamesAttribute(): string
    {
        $cameras = $this->cameras();
        if ($cameras->isEmpty()) {
            return '-';
        }

        return $cameras->pluck('name')->join(', ');
    }
}
