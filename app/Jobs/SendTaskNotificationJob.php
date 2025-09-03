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
        $titles = [
            'priority' => [
                '🚨 Priority Check Needed!',
                '⚠️ Attention Required!',
                '🔍 Priority Camera Alert',
                '👀 Important Check',
                '📸 Priority Monitoring'
            ],
            'kenya_hatchery' => [
                '🐣 Hatchery Check',
                '🥚 Hatchery Monitoring',
                '🐥 Hatchery Update',
                '🏭 Hatchery Inspection',
                '🔬 Hatchery Watch'
            ],
            'lunch_break' => [
                '🍽️ Lunch Time!',
                '⏰ Break Time!',
                '🥗 Lunch Break',
                '☕ Time to Refuel',
                '🌮 Lunch Alert'
            ],
            'default' => [
                '👋 Time to Report',
                '📋 Monitoring Check',
                '🔔 Task Reminder',
                '📊 Status Update',
                '👁️ Camera Check'
            ]
        ];

        $type = $task->type;
        $availableTitles = $titles[$type] ?? $titles['default'];
        
        return $availableTitles[array_rand($availableTitles)];
    }

    protected function buildBody(Task $task): string
    {
        // ✅ Properly decode camera_ids
        $cameraIds = is_array($task->camera_ids)
            ? $task->camera_ids
            : (json_decode($task->camera_ids, true) ?? []);

        if (empty($cameraIds)) {
            $messages = [
                'lunch_break' => [
                    "🍽️ It's lunch time — take a well-deserved break!",
                    "⏰ Time for lunch! Enjoy your meal!",
                    "🥗 Lunch break — recharge and refresh!",
                    "☕ Break time! Enjoy your lunch!",
                    "🌮 Lunch alert! Time to eat!"
                ],
                'kenya_hatchery' => [
                    "🐣 Please check the hatchery camera.",
                    "🥚 Hatchery requires your attention.",
                    "🐥 Time to inspect the hatchery.",
                    "🏭 Hatchery monitoring needed.",
                    "🔬 Check hatchery conditions."
                ],
                'priority' => [
                    "🚨 What's happening here?!",
                    "⚠️ Situation requires attention!",
                    "🔍 Please investigate this!",
                    "👀 Immediate check needed!",
                    "📸 Priority situation detected!"
                ],
                'default' => [
                    "Please check your dashboard for details.",
                    "Review the task details on your dashboard.",
                    "Check the system for more information.",
                    "See dashboard for complete details.",
                    "Open dashboard for full context."
                ]
            ];

            $type = $task->type;
            $availableMessages = $messages[$type] ?? $messages['default'];
            
            return $availableMessages[array_rand($availableMessages)];
        }

        $cameras = Camera::whereIn('id', $cameraIds)->with('site')->get();

        if ($task->type === 'priority') {
            $cam = $cameras->first();
            $siteName = $cam->site?->name ?? 'Site';
            $camName  = $cam->name ?? "Camera {$cam->id}";
            
            $priorityMessages = [
                "🚨 What's happening here?!\n📍 {$siteName} — 🎥 {$camName}",
                "⚠️ Attention needed!\n📍 {$siteName} — 🎥 {$camName}",
                "🔍 Investigate this!\n📍 {$siteName} — 🎥 {$camName}",
                "👀 Immediate check required!\n📍 {$siteName} — 🎥 {$camName}",
                "📸 Priority alert!\n📍 {$siteName} — 🎥 {$camName}"
            ];
            
            return $priorityMessages[array_rand($priorityMessages)];
        }

        $grouped = [];
        foreach ($cameras as $cam) {
            $siteName = $cam->site?->name ?? 'Site';
            $grouped[$siteName][] = $cam->name ?? "Camera {$cam->id}";
        }

        // Shuffle the messages
        $introMessages = [
            "👋 Hey, it's time to check on:",
            "📋 Please monitor these cameras:",
            "🔔 Time to check these locations:",
            "👁️ Camera inspection needed for:",
            "📊 Status check required for:"
        ];
        
        $lines = [$introMessages[array_rand($introMessages)]];

        $counter = 1;
        foreach ($grouped as $siteName => $cameraNames) {
            foreach ($cameraNames as $cameraName) {
                $lines[] = "{$counter}) {$siteName} - {$cameraName}";
                $counter++;
            }
        }

        return implode("\n", $lines);
    }
}