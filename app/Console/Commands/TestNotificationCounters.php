<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NotificationCounter;
use App\Models\Users;
use App\Services\NotificationService;
use App\Services\SidebarNotificationService;
use App\Services\NotificationDisplayService;

class TestNotificationCounters extends Command
{
    protected $signature = 'test:notification-counters {--create-test-data}';
    protected $description = 'Test notification counter functionality';

    public function handle()
    {
        $this->info('🧪 Testing Notification Counter System');
        $this->line('═══════════════════════════════════════');
        
        // Get sample admin user
        $adminUser = Users::where('role', 'SuperAdmin')->orWhere('role', 'Admin')->first();
        if (!$adminUser) {
            $this->error('No admin user found!');
            return;
        }
        
        $this->info("Testing with user: {$adminUser->name} ({$adminUser->role}) - Office: {$adminUser->Office}");
        
        // Create test data if requested
        if ($this->option('create-test-data')) {
            $this->createTestData($adminUser->Office);
        }
        
        // Test 1: Check existing counters
        $this->line("\n1. Checking existing notification counters:");
        $counters = NotificationCounter::all();
        if ($counters->count() > 0) {
            foreach ($counters as $counter) {
                $this->line("   📊 {$counter->office} - {$counter->page}: {$counter->count}");
            }
        } else {
            $this->warn("   No notification counters found in database");
        }
        
        // Test 2: Test counter creation/increment
        $this->line("\n2. Testing counter creation and increment:");
        $testOffice = $adminUser->Office;
        $testPage = 'pending';
        
        $counter = NotificationCounter::getCounter($testOffice, $testPage);
        $originalCount = $counter->count;
        $this->line("   Original count for {$testOffice} - {$testPage}: {$originalCount}");
        
        $counter->incrementCounter();
        $this->line("   After increment: {$counter->count}");
        
        // Reset back to original
        $counter->setCount($originalCount);
        $this->line("   Reset to original: {$counter->count}");
        
        // Test 3: Test service methods
        $this->line("\n3. Testing notification services:");
        
        // Test SidebarNotificationService
        try {
            $adminCounts = SidebarNotificationService::getSuperAdminNotificationCounts();
            $this->line("   SidebarNotificationService (admin counts):");
            foreach ($adminCounts as $key => $value) {
                $this->line("     {$key}: {$value}");
            }
        } catch (\Exception $e) {
            $this->error("   SidebarNotificationService failed: " . $e->getMessage());
        }
        
        // Test NotificationDisplayService
        try {
            $displayCounts = NotificationDisplayService::getAllOfficesPageCounts();
            $this->line("   NotificationDisplayService (all offices):");
            foreach ($displayCounts as $key => $value) {
                $this->line("     {$key}: {$value}");
            }
        } catch (\Exception $e) {
            $this->error("   NotificationDisplayService failed: " . $e->getMessage());
        }
        
        // Test 4: Simulate API endpoint
        $this->line("\n4. Simulating API endpoint response:");
        \Auth::login($adminUser);
        
        $notificationService = new NotificationService();
        if (strtolower($adminUser->role) === 'admin' || strtolower($adminUser->role) === 'superadmin') {
            try {
                $notificationCounts = SidebarNotificationService::getSuperAdminNotificationCounts();
                $enhancedCounts = NotificationDisplayService::getAllOfficesPageCounts();
                $apiCounts = array_merge($notificationCounts, $enhancedCounts);
            } catch (\Exception $e) {
                $apiCounts = ['error' => $e->getMessage()];
            }
        } else {
            $apiCounts = $notificationService->getNotificationCounts($adminUser->Office);
        }
        
        $this->line("   API would return:");
        foreach ($apiCounts as $key => $value) {
            $this->line("     {$key}: {$value}");
        }
        
        $this->info("\n✅ Notification counter test completed!");
    }
    
    private function createTestData($office)
    {
        $this->line("\n📝 Creating test notification counter data:");
        
        $testData = [
            'pending' => 3,
            'approved' => 2,
            'released' => 1,
            'disapproved' => 1
        ];
        
        foreach ($testData as $page => $count) {
            $counter = NotificationCounter::getCounter($office, $page);
            $counter->setCount($count);
            $this->line("   Set {$office} - {$page}: {$count}");
        }
        
        $this->info("✅ Test data created successfully!");
    }
}
