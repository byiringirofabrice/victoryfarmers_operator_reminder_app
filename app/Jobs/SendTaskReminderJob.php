<?php

namespace App\Jobs;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendTaskReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Task $task;

    /**
     * Create a new job instance.
     *
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $timezone = $this->task->getTimezone() ?? 'UTC';
            $now = Carbon::now($timezone);

            // Notify each camera (replace with your actual logic)
            foreach ($this->task->cameras as $camera) {
                Log::info("[TASK REMINDER] Task ID: {$this->task->id}, Camera ID: {$camera->id}, Time: {$now}");
                // You can fire a notification event or broadcast here
            }

            // Update task status if needed
            $this->task->update([
                'status' => 'sent', // or another relevant status if applicable
                'notified_at' => $now,
            ]);

            // Reschedule this task if it's recurring
            $frequency = $this->task->frequency_hours ?? 1;
            if ($frequency > 0) {
                $nextTime = $now->copy()->addHours($frequency);
                self::dispatch($this->task)->delay($nextTime);
                Log::info("[TASK RESCHEDULED] Task ID: {$this->task->id} will run at: {$nextTime}");
            }
        } catch (\Throwable $e) {
            Log::error("[TASK REMINDER ERROR] Task ID: {$this->task->id}, Error: {$e->getMessage()}");
        }
    }
}
