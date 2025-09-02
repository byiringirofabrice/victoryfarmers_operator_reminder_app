<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\PushSubscription;
use App\Models\Camera;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class SendTaskNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $taskId;

    public function __construct(int $taskId)
    {
        $this->taskId = $taskId;
    }

    public function handle()
    {
        $task = Task::with(['controlRoom', 'site'])->find($this->taskId);
        if (! $task) {
            Log::warning("SendTaskNotificationJob: Task {$this->taskId} not found.");
            return;
        }

        // get subscriptions (deduplicate by endpoint)
        $subs = PushSubscription::where('control_room_id', $task->control_room_id)
            ->get()
            ->unique(fn($s) => $s->endpoint);

        if ($subs->isEmpty()) {
            Log::info("No subscriptions for control room {$task->control_room_id}");
            return;
        }

        $payload = [
            'title' => $this->titleForTask($task),
            'body'  => $this->buildBody($task),
            'icon'  => '/images/notification-icon.png',
            'url'   => url('/'),
            'task_id' => $task->id,
            'type' => $task->type,
        ];

        $vapid = config('services.webpush.vapid');
        $auth = [
            'VAPID' => [
                'subject' => $vapid['subject'] ?? 'mailto:admin@yourdomain.com',
                'publicKey' => $vapid['public_key'] ?? env('VAPID_PUBLIC_KEY'),
                'privateKey' => $vapid['private_key'] ?? env('VAPID_PRIVATE_KEY'),
            ],
        ];

        $webPush = new WebPush($auth);

        foreach ($subs as $s) {
            try {
                $subscription = Subscription::create([
                    'endpoint' => $s->endpoint,
                    'keys' => [
                        'p256dh' => $s->p256dh,
                        'auth' => $s->auth_token,
                    ],
                ]);
                $webPush->queueNotification($subscription, json_encode($payload));
            } catch (\Exception $e) {
                Log::error('Error queueing notification: ' . $e->getMessage());
            }
        }

        foreach ($webPush->flush() as $report) {
            $endpoint = (string) $report->getRequest()->getUri();
            if (! $report->isSuccess()) {
                Log::warning("Push failed for {$endpoint}: {$report->getReason()}");
                PushSubscription::where('endpoint', $endpoint)->delete();
            } else {
                Log::info("Push sent for {$endpoint}");
            }
        }
    }

    protected function titleForTask(Task $task): string
    {
        return match($task->type) {
            'priority'       => '🚨 Hi',
            'kenya_hatchery' => '🐣 Hatchery — Quick Check',
            'lunch_break'    => '🍽️ Time to take lunch!!!!',
            default          => '👋 Time to report on these camares',
        };
    }

    protected function buildBody(Task $task): string
    {
        // ✅ Properly decode camera_ids
        $cameraIds = is_array($task->camera_ids)
            ? $task->camera_ids
            : (json_decode($task->camera_ids, true) ?? []);

        if (empty($cameraIds)) {
            return match($task->type) {
                'lunch_break'    => "🍽️ It's lunch time — take a well-deserved break!",
                'kenya_hatchery' => "🐣 Please check the hatchery camera.",
                'priority'       => "🚨 Urgent camera check required.",
                default          => "Please check your dashboard for details."
            };
        }

        $cameras = Camera::whereIn('id', $cameraIds)->with('site')->get();

        if ($task->type === 'priority') {
            $cam = $cameras->first();
            $siteName = $cam->site?->name ?? 'Site';
            $camName  = $cam->name ?? "Camera {$cam->id}";
            return "🚨 Priority check required!\n📍 {$siteName} — 🎥 {$camName}";
        }

        $grouped = [];
        foreach ($cameras as $cam) {
            $siteName = $cam->site?->name ?? 'Site';
            $grouped[$siteName][] = $cam->name ?? "Camera {$cam->id}";
        }

        $lines = ["👋 Hey, it's time to check on the following cameras:"];
        foreach ($grouped as $siteName => $names) {
            $lines[] = "📍 {$siteName} — 🎥 " . implode(', ', $names);
        }

        return implode("\n", $lines);
    }
}
