<?php

namespace App\Console\Commands;

use App\Models\Camera;
use App\Models\ControlRoom;
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

            // 🍽️ Lunch reminder at 12:00 PM local time
            if ($force || ($now->hour === 12 && $now->minute === 0 && $now->second < 30)) {
                $this->createLunchReminder($controlRoom, $now);
            }

            // ⏰ Every 30 minutes (:00 and :30)
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
            try {
                $cameras = $site->cameras->sortBy('id')->values()->pluck('id')->toArray();
                $total = count($cameras);
                
                if ($total === 0) {
                    $this->info("Site {$site->name}: No cameras, skipping");
                    continue;
                }

                // File-based tracking - much simpler and reliable
                $trackingFile = storage_path("app/tracking/site_{$site->id}_control_room_{$controlRoom->id}.txt");
                
                // Read current index or start at 0
                $currentIndex = 0;
                if (file_exists($trackingFile)) {
                    $currentIndex = (int) trim(file_get_contents($trackingFile));
                }

                // Ensure index is within bounds
                if ($currentIndex < 0 || $currentIndex >= $total) {
                    $currentIndex = 0;
                }

                // Select next 2 cameras
                $selected = [];
                for ($i = 0; $i < min(2, $total); $i++) {
                    $index = ($currentIndex + $i) % $total;
                    $selected[] = $cameras[$index];
                }

                // Update index for next run
                $newIndex = ($currentIndex + 2) % $total;

                // Ensure directory exists and save new index
                if (!is_dir(dirname($trackingFile))) {
                    mkdir(dirname($trackingFile), 0755, true);
                }
                file_put_contents($trackingFile, $newIndex);

                // 📝 Save task
                $task = Task::create([
                    'control_room_id' => $controlRoom->id,
                    'site_id' => $site->id,
                    'camera_ids' => $selected,
                    'type' => 'country_specific',
                    'status' => 'sent',
                    'notified_at' => $now,
                ]);

                SendTaskNotificationJob::dispatch($task->id);

                $this->info("Site {$site->name}: selected cameras " . implode(',', $selected) . " | Next index: {$newIndex}");

            } catch (\Exception $e) {
                $this->error("Error processing site {$site->name}: " . $e->getMessage());
                Log::error("Error processing site {$site->name}", ['error' => $e->getMessage()]);
            }
        }

        Log::info("✅ Country tasks created for {$controlRoom->country->code} at {$now->toDateTimeString()}");
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

            Log::info("🍽️ Lunch break reminder created for {$controlRoom->country->code} Control Room and notification dispatched.");
        }
    }
}