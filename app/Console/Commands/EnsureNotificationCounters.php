<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NotificationCounter;
use App\Models\RequestItem;
use App\Models\Users;

class EnsureNotificationCounters extends Command
{
    protected $signature = 'notifications:ensure-counters';
    protected $description = 'Ensure notification counters exist for all offices';

    public function handle()
    {
        $this->info('Ensuring notification counters exist...');
        
        // Get all unique offices
        $offices = Users::distinct()->pluck('Office')->filter();
        $pages = ['pending', 'approved', 'released', 'disapproved'];
        
        foreach ($offices as $office) {
            $this->info("Processing office: {$office}");
            
            foreach ($pages as $page) {
                // Get or create counter
                $counter = NotificationCounter::firstOrCreate([
                    'office' => $office,
                    'page' => $page
                ], [
                    'count' => 0
                ]);
                
                // Calculate current count based on request items
                $currentCount = $this->calculateCount($office, $page);
                
                if ($counter->count != $currentCount) {
                    $this->info("  Updating {$page}: {$counter->count} -> {$currentCount}");
                    $counter->count = $currentCount;
                    $counter->save();
                } else {
                    $this->info("  {$page}: {$currentCount} (no change)");
                }
            }
        }
        
        $this->info('Notification counters updated successfully!');
    }
    
    private function calculateCount($office, $page)
    {
        $statusMap = [
            'pending' => 'pending',
            'approved' => 'approved', 
            'released' => 'released',
            'disapproved' => 'disapproved'
        ];
        
        if (!isset($statusMap[$page])) {
            return 0;
        }
        
        return RequestItem::where('office', $office)
                         ->where('status', $statusMap[$page])
                         ->count();
    }
}
