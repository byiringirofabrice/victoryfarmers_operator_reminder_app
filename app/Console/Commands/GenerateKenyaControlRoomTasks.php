<?php

namespace App\Console\Commands;

use App\Models\Camera;
use App\Models\ControlRoom;
use App\Models\NotificationCycle;
use App\Models\Site;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendTaskNotificationJob;

class GenerateKenyaControlRoomTasks extends Command
{
    protected $signature = 'tasks:generate-kenyas {--force : Run regardless of current time}';
    protected $description = 'Generates tasks for the Kenya Control Room every 30 mins, 1 hour, and lunch at 12pm.';

    public function handle()
    {
        $controlRoom = ControlRoom::with('country')
            ->whereHas('country', fn ($q) => $q->where('code', 'KE'))
            ->first();

        if (!$controlRoom || !$controlRoom->is_active) {
            Log::warning('Kenya Control Room not active or not found.');
            return;
        }

        $timezone = $controlRoom->country->timezone ?? 'Africa/Nairobi';
        $now = Carbon::now($timezone);
        $force = $this->option('force');

        // Lunch reminder at 12:00 PM local time
        if ($force || ($now->hour === 12 && $now->minute === 0 && $now->second < 30)) {
            $this->createLunchReminder($controlRoom, $now);
        }

        // Hatchery task every hour (only one per hour) — unchanged
        if ($force || ($now->minute === 0 && $now->second < 30)) {
            $this->createKenyaHatcheryTask($controlRoom, $now);
        }

        // Cross-country reminders every 30 minutes — now aggregated into ONE task
        if ($force || ($now->minute % 30 === 0 && $now->second < 30)) {
            $this->createCrossCountryReminder($controlRoom, $now);
        }
    }

    protected function createKenyaHatcheryTask(ControlRoom $controlRoom, Carbon $now)
    {
        $already = Task::where('control_room_id', $controlRoom->id)
            ->where('type', 'kenya_hatchery')
            ->whereDate('created_at', $now->toDateString())
            ->whereTime('created_at', '>=', $now->copy()->subHour())
            ->exists();

        if (!$already) {
            $camera = Camera::where('name', 'LIKE', '%Hatchery%')->first();

            if ($camera) {
                $task = Task::create([
                    'control_room_id' => $controlRoom->id,
                    'site_id' => $camera->site_id,
                    'camera_ids' => [$camera->id],
                    'type' => 'kenya_hatchery',
                    'status' => 'sent',
                    'notified_at' => $now,
                ]);
                SendTaskNotificationJob::dispatch($task->id);
                Log::info('✅ Kenya hatchery task created and notification dispatched.');
            } else {
                Log::warning('⚠️ Hatchery camera not found.');
            }
        } else {
            Log::info('ℹ️ Kenya hatchery task already created within the last hour, skipping.');
        }
    }

    /**
     * For each active site pick one camera (using your NotificationCycle logic),
     * but create ONE aggregated Task that contains all selected cameras.
     */
    protected function createCrossCountryReminder(ControlRoom $controlRoom, Carbon $now)
    {
        $sites = Site::with(['cameras' => fn ($q) =>
            $q->where('is_active', true)->where('is_online', true)
        ])
        ->where('is_active', true)
        ->where('is_online', true)
        ->get();

        $selectedCameraIds = []; // aggregated camera ids
        $siteCameraMap = [];      // optional map site_id => [camera_ids] (useful for UI / debugging)
        $anyPicked = false;

        foreach ($sites as $site) {
            $cameras = $site->cameras;
            if ($cameras->isEmpty()) {
                continue;
            }

            $cameraIds = $cameras->pluck('id')->toArray();

            // Get or create NotificationCycle for this site & control room
            $cycle = NotificationCycle::firstOrNew([
                'site_id' => $site->id,
                'control_room_id' => $controlRoom->id,
            ]);
            $used = json_decode($cycle->last_camera_ids ?? '[]', true);
            $used = is_array($used) ? $used : [];

            $available = array_values(array_diff($cameraIds, $used));

            // If none available, restart cycle
            if (empty($available)) {
                $available = $cameraIds;
                $used = [];
            }

            // Pick one camera from available (random)
            $next = $available[array_rand($available)];

            // Update the cycle
            $used[] = $next;
            $cycle->last_camera_ids = json_encode($used);
            $cycle->last_notified_at = $now;
            $cycle->save();

            // Add to aggregated arrays
            $selectedCameraIds[] = $next;
            $siteCameraMap[$site->id] = [$next];
            $anyPicked = true;
        }

        if ($anyPicked && !empty($selectedCameraIds)) {
            // remove duplicates just in case
            $selectedCameraIds = array_values(array_unique($selectedCameraIds));

            // Create one aggregated task covering all picked cameras
            $task = Task::create([
                'control_room_id' => $controlRoom->id,
                'site_id' => null, // aggregated across many sites
                'camera_ids' => $selectedCameraIds,
                'type' => 'cross_country', // keep same type if you prefer
                'status' => 'sent',
                'notified_at' => $now,
                // if your tasks table has a JSON 'meta' or 'extra' column you can store $siteCameraMap for UI
                // 'extra' => ['site_camera_map' => $siteCameraMap],
            ]);

            SendTaskNotificationJob::dispatch($task->id);

            Log::info('✅ Kenya cross-country aggregated task created and notification dispatched.');
        } else {
            Log::info('ℹ️ No active cameras found for Kenya cross-country task.');
        }
    }

    protected function createLunchReminder(ControlRoom $controlRoom, Carbon $now)
    {
        $exists = Task::where('control_room_id', $controlRoom->id)
            ->where('type', 'lunch_break')
            ->whereDate('created_at', $now->toDateString())
            ->exists();

        if (!$exists) {
            $task = Task::create([
                'control_room_id' => $controlRoom->id,
                'site_id' => null,
                'camera_ids' => [],
                'type' => 'lunch_break',
                'status' => 'sent',
                'notified_at' => $now,
            ]);
            SendTaskNotificationJob::dispatch($task->id);
            Log::info("✅ Lunch break reminder created for Kenya Control Room and notification dispatched.");
        } else {
            Log::info("ℹ️ Lunch break reminder already exists today for Kenya, skipping.");
        }
    }
}
