<?php

namespace App\Imports;

use App\Models\Camera;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CamerasImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Camera([
            'site_id' => $row['site_id'],
            'control_room_id' => $row['control_room_id'],
            'name' => $row['name'],
            'camera_type' => $row['camera_type'],
            'is_priority' => $row['is_priority'] ?? false,
            'sort_order' => $row['sort_order'] ?? 0,
            'is_active' => $row['is_active'] ?? true,
            'is_online' => $row['is_online'] ?? true,
        ]);
    }
}