<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Camera extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'site_id',
        'control_room_id',
        'name',
        'camera_type',
        'is_priority',
        'sort_order',
        'is_active',
        'is_online',
    ];

    protected $casts = [
        'camera_type' => 'string',
    ];

    // Relationships
     public function controlRoom()
    {
        return $this->belongsTo(ControlRoom::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

   
  


    
}