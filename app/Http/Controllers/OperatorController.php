<?php

namespace App\Http\Controllers;

use App\Models\ControlRoom;
use App\Models\Notification;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function index()
    {
        $controlRooms = ControlRoom::all();

        // Define styling and extra data externally (not in DB/model)
        $controlRoomStyles = [
            // Map control_room_id => styles and data
            // Use actual control_room IDs from your DB
            1 => [
                'bg_gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'border_color' => '#667eea',
                'icon_bg_color' => '#667eea',
                'icon_class' => 'fa-globe-africa',
                'button_color' => '#667eea',
                'active_farms_count' => 47,
                'alerts_today_count' => 12,
            ],
            2 => [
                'bg_gradient' => 'linear-gradient(135deg, #34d399 0%, #059669 100%)',
                'border_color' => '#34d399',
                'icon_bg_color' => '#34d399',
                'icon_class' => 'fa-mountain',
                'button_color' => '#34d399',
                'active_farms_count' => 63,
                'alerts_today_count' => 8,
            ],
            // add more mappings as needed for new control rooms
        ];

        // Inject styles & data into each control room object (dynamic props)
        foreach ($controlRooms as $room) {
            if (isset($controlRoomStyles[$room->id])) {
                $styles = $controlRoomStyles[$room->id];
                $room->bg_gradient = $styles['bg_gradient'];
                $room->border_color = $styles['border_color'];
                $room->icon_bg_color = $styles['icon_bg_color'];
                $room->icon_class = $styles['icon_class'];
                $room->button_color = $styles['button_color'];
                $room->active_farms_count = $styles['active_farms_count'];
                $room->alerts_today_count = $styles['alerts_today_count'];
            } else {
                // Default fallback styles if missing
                $room->bg_gradient = 'linear-gradient(135deg, #6b7280 0%, #374151 100%)'; // gray
                $room->border_color = '#6b7280';
                $room->icon_bg_color = '#6b7280';
                $room->icon_class = 'fa-building';
                $room->button_color = '#6b7280';
                $room->active_farms_count = 0;
                $room->alerts_today_count = 0;
            }
        }

        return view('operator.index', compact('controlRooms'));
    }

    public function manage()
    {
        $notifications = Notification::where('status', 'pending')->get();
        return view('operator.manage', compact('notifications'));
    }
}
