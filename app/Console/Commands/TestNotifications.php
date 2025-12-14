<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Requests;
use App\Notifications\RequestApproved;
use App\Notifications\RequestDenied;
use App\Services\SidebarNotificationService;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notifications {--type=all : Type of notification test (approved, denied, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the notification system functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        
        $this->info('Testing notification system...');
        
        // Get a test user
        $user = User::first();
        if (!$user) {
            $this->error('No users found in database. Please create a user first.');
            return;
        }
        
        // Create a test request
        $request = new Requests([
            'user_id' => $user->user_id,
            'StockNumber' => 'TEST001',
            'supply_name' => 'Test Supply Item',
            'supply_quantity' => 5,
            'Purpose' => 'Testing notification system',
            'office' => $user->Office ?? 'Test Office',
            'status' => 'pending'
        ]);
        
        if ($type === 'approved' || $type === 'all') {
            $this->info('Testing RequestApproved notification...');
            try {
                $user->notify(new RequestApproved($request));
                $this->info('✅ RequestApproved notification sent successfully!');
            } catch (\Exception $e) {
                $this->error('❌ Failed to send RequestApproved notification: ' . $e->getMessage());
            }
        }
        
        if ($type === 'denied' || $type === 'all') {
            $this->info('Testing RequestDenied notification...');
            try {
                $user->notify(new RequestDenied($request, 'Test denial reason'));
                $this->info('✅ RequestDenied notification sent successfully!');
            } catch (\Exception $e) {
                $this->error('❌ Failed to send RequestDenied notification: ' . $e->getMessage());
            }
        }
        
        // Test notification service
        $this->info('Testing SidebarNotificationService...');
        try {
            $service = app(SidebarNotificationService::class);
            $counts = $service->getNotificationCounts($user->Office ?? 'Test Office');
            $this->info('✅ Notification counts retrieved successfully:');
            $this->table(['Type', 'Count'], [
                ['Total Unread', $counts['total_unread']],
                ['Approved', $counts['approved']],
                ['Denied', $counts['denied']]
            ]);
        } catch (\Exception $e) {
            $this->error('❌ Failed to get notification counts: ' . $e->getMessage());
        }
        
        $this->info('Notification system test completed!');
        return 0;
    }
}
