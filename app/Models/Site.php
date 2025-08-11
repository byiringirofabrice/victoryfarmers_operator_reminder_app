<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'is_priority',
        'is_active',
        'is_online',
        'location',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function cameras()
    {
        return $this->hasMany(Camera::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function branchStatusLogs()
    {
        return $this->hasMany(BranchStatusLog::class);
    }
}