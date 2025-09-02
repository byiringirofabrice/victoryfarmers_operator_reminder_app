<?php

namespace App\Console\Commands;

use App\Models\ControlRoom;
use App\Models\NotificationCycle;
use App\Models\Task;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendTaskNotificationJob;

class GeneratePriorityTasks extends Command
{
    protected $signature = 'tasks:generate-priority {--force : Run regardless of current time}';
    protected $description = 'Generates tasks for ONE priority camera per site (rotating) every 16 minutes per control room';

    public function handle()
    {
        $now = Carbon::now();

        // 🔹 Check if current minute matches 16-min cycle
        if (!$this->option('force')) {
            if ($now->minute % 16 !== 0) {
                $this->info("⏸ Skipped: Not a 16-minute interval. Current minute = {$now->minute}");
                return;
            }
        }

        $controlRooms = ControlRoom::where('is_active', true)->get();

        foreach ($controlRooms as $controlRoom) {
            $this->createPriorityTaskForControlRoom($controlRoom, $now);
        }
    }

    protected function createPriorityTaskForControlRoom(ControlRoom $controlRoom, Carbon $now)
    {
        $sites = Site::with(['cameras' => fn($q) =>
            $q->where('is_active', true)
              ->where('is_online', true)
              ->where('is_priority', 1)
        ])
        ->whereHas('branch', function ($q) use ($controlRoom) {
            $q->where('control_room_id', $controlRoom->id);
        })
        ->where('is_active', true)
        ->where('is_online', true)
        ->get();

        $selectedCameraIds = [];

        foreach ($sites as $site) {
            $cameras = $site->cameras;
            if ($cameras->isEmpty()) continue;

            $cameraIds = $cameras->pluck('id')->values()->toArray();

            // 🔹 Get cycle for this site+control room
            $cycle = NotificationCycle::firstOrNew([
                'site_id' => $site->id,
                'control_room_id' => $controlRoom->id,
                'type' => 'priority',
            ]);

            $lastIndex = $cycle->last_country_index ?? -1;
            $nextIndex = ($lastIndex + 1) % count($cameraIds);

            $selectedCameraId = $cameraIds[$nextIndex];
            $selectedCameraIds[] = $selectedCameraId;

            // 🔹 Update cycle tracking
            $cycle->last_country_index = $nextIndex;
            $cycle->last_camera_ids = json_encode([$selectedCameraId]);
            $cycle->last_notified_at = $now;
            $cycle->save();
        }

        if (!empty($selectedCameraIds)) {
            $task = Task::create([
                'control_room_id' => $controlRoom->id,
                'site_id' => null, // aggregated across all sites in this control room
                'camera_ids' => array_values($selectedCameraIds),
                'type' => 'priority',
                'status' => 'sent',
                'notified_at' => $now,
            ]);

            SendTaskNotificationJob::dispatch($task->id);
            Log::info("✅ Priority task created for Control Room: {$controlRoom->name} with " . count($selectedCameraIds) . " cameras (1 per site)");
        } else {
            Log::info("ℹ️ No priority cameras found for Control Room: {$controlRoom->name}");
        }
    }
}
