<?php

namespace Database\Seeders;

 use Illuminate\Database\Seeder;
use App\Models\Camera;
use App\Models\Site;

class CameraSeeder extends Seeder
{
    public function run(): void
    {
        $sites = Site::with('branch.controlRoom')->get();

        foreach ($sites as $site) {
            $controlRoomId = $site->branch->controlRoom->id;

            for ($i = 1; $i <= 50; $i++) {
                Camera::create([
                    'site_id' => $site->id,
                    'control_room_id' => $controlRoomId,
                    'name' => "Camera {$i} - Site {$site->id}",
                    'camera_type' => 'fixed',
                    'is_priority' => ($i % 10 === 0), // make every 10th camera a priority
                    'sort_order' => $i,
                    'is_active' => true,
                    'is_online' => true,
                ]);
            }
        }
    }
}

