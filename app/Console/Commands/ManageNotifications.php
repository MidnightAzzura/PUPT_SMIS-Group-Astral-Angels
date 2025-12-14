<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NotificationCounter;
use App\Services\NotificationDisplayService;
use App\Models\Users;

class ManageNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'smis:notifications 
                            {action : The action to perform (show|clear|test|recalculate)}
                            {--office= : Specific office to target}
                            {--page= : Specific page to target}
                            {--count=5 : Number of test notifications to create}';

    /**
     * The console command description.
     */
    protected $description = 'Manage SMIS notification counters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'show':
                $this->showNotifications();
                break;
            case 'clear':
                $this->clearNotifications();
                break;
            case 'test':
                $this->createTestNotifications();
                break;
            case 'recalculate':
                $this->recalculateCounters();
                break;
            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: show, clear, test, recalculate");
        }
    }

    /**
     * Show current notification counts
     */
    private function showNotifications()
    {
        $office = $this->option('office');
        
        if ($office) {
            $this->showOfficeNotifications($office);
        } else {
            $this->showAllNotifications();
        }
    }

    /**
     * Show notifications for all offices
     */
    private function showAllNotifications()
    {
        $this->info("📊 Current Notification Counts (All Offices)");
        $this->line("═══════════════════════════════════════════");
        
        $offices = Users::distinct()->pluck('Office')->filter();
        
        foreach ($offices as $office) {
            $counts = NotificationDisplayService::getAllPageCounts($office);
            
            $this->line("");
            $this->info("🏢 Office: {$office}");
            $this->line("   📋 Pending: {$counts['pending']}");
            $this->line("   ✅ Approved: {$counts['approved']}");
            $this->line("   🚚 Released: {$counts['released']}");
            $this->line("   ❌ Disapproved: {$counts['disapproved']}");
            $this->line("   📧 Total Unread: {$counts['total_unread']}");
        }
    }

    /**
     * Show notifications for specific office
     */
    private function showOfficeNotifications($office)
    {
        $counts = NotificationDisplayService::getAllPageCounts($office);
        
        $this->info("📊 Notification Counts for Office: {$office}");
        $this->line("═══════════════════════════════════════");
        $this->line("📋 Pending: {$counts['pending']}");
        $this->line("✅ Approved: {$counts['approved']}");
        $this->line("🚚 Released: {$counts['released']}");
        $this->line("❌ Disapproved: {$counts['disapproved']}");
        $this->line("📧 Total Unread: {$counts['total_unread']}");
    }

    /**
     * Clear notifications
     */
    private function clearNotifications()
    {
        $office = $this->option('office');
        $page = $this->option('page');
        
        if ($office && $page) {
            // Clear specific office and page
            NotificationDisplayService::clearPageNotifications($office, $page);
            $this->info("✅ Cleared {$page} notifications for office: {$office}");
        } elseif ($office) {
            // Clear all pages for specific office
            $pages = ['pending', 'approved', 'released', 'disapproved'];
            foreach ($pages as $p) {
                NotificationDisplayService::clearPageNotifications($office, $p);
            }
            $this->info("✅ Cleared all notifications for office: {$office}");
        } else {
            // Clear all notifications
            if ($this->confirm('⚠️  Are you sure you want to clear ALL notification counters?')) {
                NotificationCounter::truncate();
                $this->info("✅ Cleared all notification counters");
            }
        }
    }

    /**
     * Create test notifications
     */
    private function createTestNotifications()
    {
        $office = $this->option('office') ?? 'Test Office';
        $count = (int) $this->option('count');
        
        $this->info("🧪 Creating {$count} test notifications for office: {$office}");
        
        $pages = ['pending', 'approved', 'released', 'disapproved'];
        
        foreach ($pages as $page) {
            $counter = NotificationCounter::getCounter($office, $page);
            $counter->setCount($count);
            $this->line("   📝 Set {$page}: {$count}");
        }
        
        $this->info("✅ Test notifications created successfully!");
        $this->line("💡 You can now test the notification display on your web interface");
    }

    /**
     * Recalculate all counters
     */
    private function recalculateCounters()
    {
        $this->info("🔄 Recalculating notification counters...");
        
        try {
            NotificationDisplayService::recalculateAllCounters();
            $this->info("✅ Notification counters recalculated successfully!");
        } catch (\Exception $e) {
            $this->error("❌ Failed to recalculate counters: " . $e->getMessage());
        }
    }
}
