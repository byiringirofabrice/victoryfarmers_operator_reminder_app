<?php

// namespace App\Console\Commands;

// use App\Models\Camera;
// use App\Models\ControlRoom;
// use App\Models\NotificationCycle;
// use App\Models\Site;
// use App\Models\Task;
// use Carbon\Carbon;
// use Illuminate\Console\Command;
// use Illuminate\Support\Facades\Log;

// class GenerateKenyaControlRoomTasks extends Command
// {
//     protected $signature = 'tasks:generate-kenya';
//     protected $description = 'Generate monitoring tasks for the Kenya Control Room with site-based camera rotation across all countries';

//     public function handle()
//     {
//         $controlRoom = ControlRoom::with('country')->whereHas('country', function ($q) {
//             $q->where('code', 'KE'); // Country code for Kenya
//         })->first();

//         if (!$controlRoom || !$controlRoom->is_active) {
//             $this->warn('Kenya Control Room is not active.');
//             return;
//         }

//         $timezone = $controlRoom->country->timezone ?? 'Africa/Nairobi';
//         $now = Carbon::now($timezone);

//         if ($now->second() === 0) {
//             $this->createKenyaHatcheryReminder($controlRoom);
//         }

//         if ($now->second % 30 === 0) {
//             $this->createCrossCountrySiteReminder($controlRoom);
//         }
//     }

//     protected function createKenyaHatcheryReminder($controlRoom)
//     {
//         $exists = Task::where('control_room_id', $controlRoom->id)
//             ->whereDate('created_at', now())
//             ->where('type', 'kenya_hatchery')
//             ->whereTime('created_at', '>=', now()->subHour())
//             ->exists();

//         if (!$exists) {
//             $camera = Camera::where('name', 'LIKE', '%Kenya Hatchery%')->first();

//             if ($camera) {
//                 Task::create([
//                     'control_room_id' => $controlRoom->id,
//                     'camera_ids' => [$camera->id],
//                     'status' => 'pending',
//                     'type' => 'kenya_hatchery',
//                     'notified_at' => now(),
//                 ]);

//                 Log::info("Kenya Hatchery task created.");
//             }
//         }
//     }

//     protected function createCrossCountrySiteReminder($controlRoom)
//     {
//         $sites = Site::with(['cameras' => function ($q) {
//             $q->where('is_active', true)->where('is_online', true);
//         }])->where('is_active', true)->where('is_online', true)->get();

//         $selectedCameras = [];

//         foreach ($sites as $site) {
//             $cameras = $site->cameras;
//             if ($cameras->isEmpty()) {
//                 continue;
//             }

//             $cycle = NotificationCycle::firstOrCreate(
//                 ['site_id' => $site->id, 'control_room_id' => $controlRoom->id],
//                 ['last_camera_ids' => json_encode([]), 'last_notified_at' => null]
//             );

//             $lastIds = json_decode($cycle->last_camera_ids, true) ?? [];
//             $lastId = end($lastIds);
//             $cameraIds = $cameras->pluck('id')->toArray();
//             $nextCameraId = $this->getNextCameraId($cameraIds, $lastId);

//             if ($nextCameraId) {
//                 $selectedCameras[] = $nextCameraId;

//                 $cycle->last_camera_ids = json_encode([$nextCameraId]);
//                 $cycle->last_notified_at = now();
//                 $cycle->save();
//             }
//         }

//         if (!empty($selectedCameras)) {
//             Task::create([
//                 'control_room_id' => $controlRoom->id,
//                 'camera_ids' => $selectedCameras,
//                 'status' => 'pending',
//                 'type' => 'cross_country',
//                 'notified_at' => now(),
//             ]);

//             Log::info("Kenya Control Room task created with " . count($selectedCameras) . " rotating cameras.");
//         }
//     }

//     private function getNextCameraId(array $cameraIds, $lastId)
//     {
//         if (empty($cameraIds)) return null;

//         $index = array_search($lastId, $cameraIds);

//         if ($index === false || $index === count($cameraIds) - 1) {
//             return $cameraIds[0];
//         }

//         return $cameraIds[$index + 1];
//     }
// }