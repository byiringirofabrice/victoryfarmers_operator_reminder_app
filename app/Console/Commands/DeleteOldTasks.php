<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteOldTasks extends Command
{
    protected $signature = 'tasks:cleanup 
        {--days=1 : Delete tasks older than X days}
        {--chunk=1000 : Number of records to delete per chunk}
        {--dry-run : Show how many tasks would be deleted without actually deleting them}';
    
    protected $description = 'Delete tasks older than specified days to free up database space';

    public function handle()
    {
        $days = (int) $this->option('days');
        $chunkSize = (int) $this->option('chunk');
        $isDryRun = $this->option('dry-run');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Finding tasks older than {$days} day(s) (before {$cutoffDate->toDateTimeString()})...");

        if ($isDryRun) {
            $count = Task::where('created_at', '<', $cutoffDate)->count();
            $this->info("[DRY RUN] Would delete {$count} tasks");
            return;
        }

        $totalDeleted = 0;
        $startTime = microtime(true);

        // Chunked deletion for better performance with large datasets
        Task::where('created_at', '<', $cutoffDate)
            ->chunkById($chunkSize, function ($tasks) use (&$totalDeleted) {
                $deletedInChunk = 0;
                
                foreach ($tasks as $task) {
                    $task->delete(); // This will trigger model events if any
                    $deletedInChunk++;
                }
                
                $totalDeleted += $deletedInChunk;
                $this->info("Deleted {$deletedInChunk} tasks in this chunk...");
            });

        $executionTime = round(microtime(true) - $startTime, 2);

        $this->info("✅ Successfully deleted {$totalDeleted} tasks older than {$days} day(s)");
        $this->info("⏰ Execution time: {$executionTime} seconds");

        // Log the cleanup operation
        Log::info("Tasks cleanup completed: Deleted {$totalDeleted} tasks older than {$days} days", [
            'execution_time' => $executionTime,
            'cutoff_date' => $cutoffDate,
        ]);
    }
}