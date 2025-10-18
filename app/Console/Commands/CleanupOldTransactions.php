<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Carbon\Carbon;

class CleanupOldTransactions extends Command
{
    protected $signature = 'cleanup:transactions {--days=90}';
    protected $description = 'Delete transactions older than specified days';

    public function handle()
    {
        $days = $this->option('days');
        $date = Carbon::now()->subDays($days);
        
        $count = Transaction::where('created_at', '<', $date)->count();
        
        if ($count === 0) {
            $this->info('No old transactions to delete.');
            return;
        }

        if ($this->confirm("Delete {$count} transactions older than {$days} days?")) {
            Transaction::where('created_at', '<', $date)->delete();
            $this->info("{$count} transactions deleted successfully!");
        }
    }
}