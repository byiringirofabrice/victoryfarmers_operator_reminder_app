<?php

namespace App\Imports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TasksImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Task([
            'control_room_id' => $row['control_room_id'],
            'site_id' => $row['site_id'] ?? null,
            'camera_ids' => $row['camera_ids'] ? json_decode($row['camera_ids'], true) : null,
            'title' => $row['title'],
            'message' => $row['message'],
            'duration_minutes' => $row['duration_minutes'] ?? 10,
            'is_priority' => $row['is_priority'] ?? false,
            'is_break' => $row['is_break'] ?? false,
            'break_type' => $row['break_type'] ?? null,
            'scheduled_time' => $row['scheduled_time'] ?? null,
            'frequency_hours' => $row['frequency_hours'] ?? null,
            'is_active' => $row['is_active'] ?? true,
        ]);
    }
}
