<?php

namespace App\Console\Commands;

use App\Models\ControlRoom;
use App\Models\Task;
use App\Models\Site;
use App\Models\Camera;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendTaskNotificationJob;

class GeneratePriorityTasks extends Command
{
    protected $signature = 'tasks:generate-priority {--force : Run regardless of current time}';
    protected $description = 'Generates single priority camera tasks every 7 minutes per control room';

    public function handle()
    {
        $now = Carbon::now();
        $force = $this->option('force');

        // 🔹 Run every 7 minutes (7, 14, 21, 28, 35, 42, 49, 56 minutes)
        if (!$force && $now->minute % 7 !== 0) {
            $this->info("⏸ Skipped: Not a 7-minute interval. Current minute = {$now->minute}");
            return;
        }

        $controlRooms = ControlRoom::where('is_active', true)->get();

        foreach ($controlRooms as $controlRoom) {
            $this->createPriorityTaskForControlRoom($controlRoom, $now);
        }
    }

    protected function createPriorityTaskForControlRoom(ControlRoom $controlRoom, Carbon $now)
    {
        // Get sites for THIS control room's country only
        $sites = Site::with(['cameras' => function($q) {
                $q->where('is_active', true)
                  ->where('is_online', true)
                  ->where('is_priority', 1);
            }])
            ->whereHas('branch.controlRoom.country', function ($q) use ($controlRoom) {
                // Only sites from this control room's country
                $q->where('id', $controlRoom->country_id);
            })
            ->where('is_active', true)
            ->where('is_online', true)
            ->get()
            ->filter(fn($site) => $site->cameras->count() > 0); // Only sites with priority cameras

        if ($sites->isEmpty()) {
            Log::info("ℹ️ No priority cameras found for {$controlRoom->name} ({$controlRoom->country->name})");
            return;
        }

        // File-based tracking for THIS control room
        $trackingFile = storage_path("app/tracking/priority_rotation_cr_{$controlRoom->id}.txt");
        
        // Get current rotation state for THIS control room
        $rotationState = [];
        if (file_exists($trackingFile)) {
            $rotationState = json_decode(file_get_contents($trackingFile), true) ?? [];
        }

        $siteIds = $sites->pluck('id')->toArray();
        $lastSiteIndex = $rotationState['last_site_index'] ?? -1;
        
        // Move to next site in rotation
        $nextSiteIndex = ($lastSiteIndex + 1) % count($siteIds);
        $siteId = $siteIds[$nextSiteIndex];
        $site = $sites->firstWhere('id', $siteId);

        if (!$site) {
            Log::error("Site not found during rotation for {$controlRoom->name}");
            return;
        }

        // Get priority cameras for this site
        $cameras = $site->cameras;
        $cameraIds = $cameras->pluck('id')->toArray();

        if (empty($cameraIds)) {
            Log::warning("No priority cameras found for site {$site->name}");
            return;
        }

        // Get camera rotation for this site
        $lastCameraIndex = $rotationState['site_cameras'][$siteId]['last_camera_index'] ?? -1;
        $nextCameraIndex = ($lastCameraIndex + 1) % count($cameraIds);
        $cameraId = $cameraIds[$nextCameraIndex];

        // Create task for this single priority camera
        $task = Task::create([
            'control_room_id' => $controlRoom->id,
            'site_id' => $site->id,
            'camera_ids' => [$cameraId],
            'type' => 'priority',
            'status' => 'sent',
            'notified_at' => $now,
        ]);

        SendTaskNotificationJob::dispatch($task->id);

        // Update rotation state for THIS control room
        $rotationState['last_site_index'] = $nextSiteIndex;
        $rotationState['site_cameras'][$siteId]['last_camera_index'] = $nextCameraIndex;
        $rotationState['last_run'] = $now->toISOString();

        // Save tracking for THIS control room
        if (!is_dir(dirname($trackingFile))) {
            mkdir(dirname($trackingFile), 0755, true);
        }
        file_put_contents($trackingFile, json_encode($rotationState));

        $camera = Camera::find($cameraId);
        $sitePosition = $nextSiteIndex + 1;
        $totalSites = count($siteIds);
        
        $this->info("✅ {$controlRoom->name}: {$site->name} - {$camera->name} (Site {$sitePosition}/{$totalSites})");
        Log::info("✅ Priority: {$controlRoom->name} → {$site->name} - {$camera->name}");
    }
}