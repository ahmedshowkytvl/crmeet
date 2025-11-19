<?php

namespace App\Console\Commands;

use App\Jobs\CheckTaskOverdueNotifications;
use Illuminate\Console\Command;

class CheckOverdueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue tasks and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting overdue tasks check...');
        
        try {
            // تشغيل Job للتحقق من المهام المتأخرة
            CheckTaskOverdueNotifications::dispatch();
            
            $this->info('Overdue tasks check job dispatched successfully.');
            
        } catch (\Exception $e) {
            $this->error('Failed to dispatch overdue tasks check job: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}