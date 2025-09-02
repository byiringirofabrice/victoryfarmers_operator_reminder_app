<?php 
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;

class DeleteOldTasks extends Command
{
    protected $signature = 'tasks:cleanup';
    protected $description = 'Delete tasks older than 24 hours';

    public function handle()
    {
        $deleted = Task::where('created_at', '<', Carbon::now()->subDay())->delete();
        $this->info("Deleted $deleted old tasks.");
    }
}
