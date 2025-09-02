<?php

namespace App\Filament\Imports;

use App\Models\Camera;
use App\Models\ControlRoom;
use App\Models\Site;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CameraImporter extends Importer
{
    protected static ?string $model = Camera::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('control_room_name')
                ->label('Control Room Name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
                
            ImportColumn::make('site_name')
                ->label('Site Name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
                
            ImportColumn::make('name')
                ->label('Camera Name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255', 'unique:cameras,name']),
                
            ImportColumn::make('camera_type')
                ->label('Camera Type')
                ->requiredMapping()
                ->rules(['required', 'in:fixed,ptz']),
                
            ImportColumn::make('is_priority')
                ->label('Is Priority')
                ->requiredMapping()
                ->rules(['required', 'boolean']),
                
            ImportColumn::make('sort_order')
                ->label('Sort Order')
                ->requiredMapping()
                ->rules(['required', 'integer', 'min:0']),
                
            ImportColumn::make('is_active')
                ->label('Is Active')
                ->requiredMapping()
                ->rules(['required', 'boolean']),
                
            ImportColumn::make('is_online')
                ->label('Is Online')
                ->requiredMapping()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): ?Camera
    {
        DB::beginTransaction();
        
        try {
            $controlRoom = ControlRoom::where('name', $this->data['control_room_name'])->first();
            $site = Site::where('name', $this->data['site_name'])->first();

            if (!$controlRoom || !$site) {
                DB::commit();
                Log::warning("Skipping camera - control room or site not found", $this->data);
                return null;
            }

            $camera = Camera::create([
                'control_room_id' => $controlRoom->id,
                'site_id' => $site->id,
                'name' => $this->data['name'],
                'camera_type' => $this->data['camera_type'],
                'is_priority' => (bool)$this->data['is_priority'],
                'sort_order' => (int)$this->data['sort_order'],
                'is_active' => (bool)$this->data['is_active'],
                'is_online' => (bool)$this->data['is_online'],
            ]);

            DB::commit();
            
            // Immediate verification
            if (!Camera::where('id', $camera->id)->exists()) {
                throw new \Exception("Camera record not persisted to database!");
            }

            return $camera;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Camera import failed: {$e->getMessage()}", [
                'data' => $this->data,
                'exception' => $e
            ]);
            return null;
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = "Imported {$import->successful_rows} cameras successfully.";
        
        if ($failed = $import->getFailedRowsCount()) {
            $body .= " {$failed} rows failed (check logs)";
        }
        
        return $body;
    }
}