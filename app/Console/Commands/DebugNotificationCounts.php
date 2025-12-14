<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\NotificationCounter;
use App\Services\SidebarNotificationService;
use App\Services\NotificationDisplayService;

class DebugNotificationCounts extends Command
{
    protected $signature = 'debug:notification-counts';
    protected $description = 'Debug notification count discrepancies';

    public function handle()
    {
        $this->info('=== NOTIFICATION COUNT DEBUG ===');
        $this->newLine();

        // 1. Check actual Notification table counts
        $this->info('=== ACTUAL NOTIFICATION TABLE DATA ===');
        
        $totalNotifications = Notification::count();
        $unreadNotifications = Notification::where('is_read', false)->count();
        $readNotifications = Notification::where('is_read', true)->count();
        
        $this->line("Total notifications in database: " . $totalNotifications);
        $this->line("Unread notifications: " . $unreadNotifications);
        $this->line("Read notifications: " . $readNotifications);
        $this->newLine();
        
        // Check by action types
        $actionCounts = Notification::selectRaw('action, is_read, COUNT(*) as count')
            ->groupBy('action', 'is_read')
            ->get();
        
        $this->line("Breakdown by action and read status:");
        foreach ($actionCounts as $actionCount) {
            $status = $actionCount->is_read ? 'READ' : 'UNREAD';
            $this->line("  {$actionCount->action} ({$status}): {$actionCount->count}");
        }
        
        $this->newLine();
        $this->info('=== NOTIFICATION COUNTER TABLE DATA ===');

        // 2. Check NotificationCounter table
        $allCounters = NotificationCounter::all();
        $this->line("Total NotificationCounter entries: " . $allCounters->count());

        if ($allCounters->count() > 0) {
            $this->line("NotificationCounter breakdown:");
            foreach ($allCounters as $counter) {
                $this->line("  Office: {$counter->office}, Page: {$counter->page}, Count: {$counter->count}");
            }
        } else {
            $this->line("No entries found in NotificationCounter table");
        }

        $this->newLine();
        $this->info('=== SERVICE COMPARISON ===');

        // 3. Compare service outputs
        $this->line("SidebarNotificationService::getSuperAdminNotificationCounts():");
        $sidebarCounts = SidebarNotificationService::getSuperAdminNotificationCounts();
        foreach ($sidebarCounts as $key => $value) {
            $this->line("  {$key}: {$value}");
        }
        
        $this->newLine();
        $this->line("NotificationDisplayService::getAllOfficesPageCounts():");
        $displayCounts = NotificationDisplayService::getAllOfficesPageCounts();
        foreach ($displayCounts as $key => $value) {
            $this->line("  {$key}: {$value}");
        }

        $this->newLine();
        $this->info('=== RECOMMENDATIONS ===');

        if ($unreadNotifications == 0 && $displayCounts['total_unread'] > 0) {
            $this->error("ISSUE FOUND: NotificationCounter table has stale data!");
            $this->line("Solution: Clear the NotificationCounter table or reset counts");
            
            // Offer to fix it
            if ($this->confirm('Would you like to clear the stale notification counters?')) {
                NotificationCounter::truncate();
                $this->info("NotificationCounter table cleared successfully!");
            }
        } else if ($unreadNotifications > 0 && $displayCounts['total_unread'] == 0) {
            $this->error("ISSUE FOUND: NotificationCounter table is missing entries!");
            $this->line("Solution: Update NotificationCounter table with current notification counts");
        } else {
            $this->info("Counts appear to be in sync");
        }

        return 0;
    }
}
