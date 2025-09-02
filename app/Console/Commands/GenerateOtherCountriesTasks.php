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

class GenerateOtherCountriesTasks extends Command
{
    protected $signature = 'tasks:generate-other-countries {--force : Run regardless of current time}';
    protected $description = 'Generates tasks for other countries every 30 mins and lunch at 12pm local time.';

    public function handle()
    {
        $controlRooms = ControlRoom::with('country')
            ->whereHas('country', function ($q) {
                $q->where('code', '!=', 'KE');
            })
            ->where('is_active', true)
            ->get();

        $force = $this->option('force');

        foreach ($controlRooms as $controlRoom) {
            $timezone = $controlRoom->country->timezone ?? 'Africa/Nairobi';
            $now = Carbon::now($timezone);

            // Lunch reminder at 12:00 PM local time
            if ($force || ($now->hour === 12 && $now->minute === 0 && $now->second < 30)) {
                $this->createLunchReminder($controlRoom, $now);
            }

            // Every 30 minutes
            if ($force || ($now->minute % 30 === 0 && $now->second < 30)) {
                $this->createCountryTasks($controlRoom, $now);
            }
        }
    }

    protected function createCountryTasks(ControlRoom $controlRoom, Carbon $now)
    {
        $sites = Site::with(['cameras' => function ($q) {
                $q->where('is_active', true)->where('is_online', true);
            }])
            ->where('is_active', true)
            ->where('is_online', true)
            ->whereHas('branch', function ($q) use ($controlRoom) {
                $q->where('country_id', $controlRoom->country->id);
            })
            ->get();

        foreach ($sites as $site) {
            $cameras = $site->cameras;
            if ($cameras->isEmpty()) continue;

            $cameraIds = $cameras->pluck('id')->toArray();

            $cycle = NotificationCycle::firstOrCreate(
                ['site_id' => $site->id, 'control_room_id' => $controlRoom->id],
                ['last_camera_ids' => json_encode([])]
            );

            $used = json_decode($cycle->last_camera_ids, true) ?? [];
            $available = array_values(array_diff($cameraIds, $used));

            if (empty($available)) {
                // Restart cycle
                $available = $cameraIds;
                $used = [];
            }

            // Pick 2 cameras max per site
            $pickCount = min(2, count($available));
            $nextCameras = array_splice($available, 0, $pickCount);

            // Create a task for this site
            $task = Task::create([
                'control_room_id' => $controlRoom->id,
                'site_id' => $site->id,
                'camera_ids' => $nextCameras,
                'type' => 'country_specific',
                'status' => 'sent',
                'notified_at' => $now,
            ]);

            SendTaskNotificationJob::dispatch($task->id);

            // Update cycle
            $cycle->last_camera_ids = json_encode(array_merge($used, $nextCameras));
            $cycle->last_notified_at = $now;
            $cycle->save();
        }

        Log::info("✅ Tasks created for {$controlRoom->country->code} at {$now->toDateTimeString()} and notifications dispatched.");
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
                'camera_ids' => [],
                'type' => 'lunch_break',
                'status' => 'sent',
                'notified_at' => $now,
            ]);
            SendTaskNotificationJob::dispatch($task->id);
            Log::info("✅ Lunch break reminder created for {$controlRoom->country->code} Control Room and notification dispatched.");
        }
    }
}
