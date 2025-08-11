<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchStatusLog extends Model
{
    protected $fillable = [
        'branch_id',
        'site_id',
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
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}