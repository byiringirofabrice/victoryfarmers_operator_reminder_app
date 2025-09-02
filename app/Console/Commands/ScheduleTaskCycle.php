<?php
namespace App\Console\Commands;

use App\Jobs\SendTaskReminderJob;
use App\Models\Task;

use Illuminate\Console\Command;

class ScheduleTaskCycle extends Command
{
    protected $signature = 'tasks:schedule';
    protected $description = 'Dispatch recurring task jobs';

    public function handle()
    {
        Task::where('is_active', true)->chunk(50, function ($tasks) {
            foreach ($tasks as $task) {
                SendTaskReminderJob::dispatch($task)->delay(now()->addSeconds(5));
            }
        });

        $this->info('Recurring tasks scheduled.');
    }
}
