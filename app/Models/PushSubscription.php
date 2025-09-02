<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'control_room_id',
        'endpoint',
        'p256dh',
        'auth_token',
        'content_encoding',
        'subscription',
    ];

    protected $casts = [
        'subscription' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function controlRoom()
    {
        return $this->belongsTo(ControlRoom::class);
    }
}
