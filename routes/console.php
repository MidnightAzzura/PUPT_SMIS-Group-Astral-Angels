<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Users;
use App\Models\Requests;
use App\Notifications\RequestApproved;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('test:email', function () {
    $this->info('🧪 Testing Email Functionality...');
    
    // Get a user (any user)
    $user = Users::first();
    if (!$user) {
        $this->error('❌ No users found in database');
        return;
    }
    
    $this->info("👤 Found user: {$user->First_Name} {$user->Last_Name}");
    
    // Check if user has email
    if (empty($user->email)) {
        $this->warn("⚠️  User doesn't have an email address, adding test email...");
        $user->email = 'test@example.com';
        $user->save();
    }
    
    $this->info("📧 User email: {$user->email}");
    
    // Create a mock request
    $request = new Requests([
        'user_id' => $user->user_id,
        'StockNumber' => 'TEST001',
        'supply_name' => 'Test Supply Item',
        'supply_quantity' => 5,
        'Purpose' => 'Testing email notifications',
        'Office' => $user->Office ?? 'Test Office',
        'status' => 'approved'
    ]);
    
    // Add user relationship manually for the notification
    $request->setRelation('user', $user);
    
    try {
        $this->info('📤 Sending test email notification...');
        
        // Send the notification
        $user->notify(new RequestApproved($request));
        
        $this->info('✅ Email notification sent successfully!');
        $this->info('📬 Check the email inbox for: ' . $user->email);
        $this->info('📁 Also check storage/logs/laravel.log for email logs');
        
    } catch (Exception $e) {
        $this->error('❌ Failed to send email: ' . $e->getMessage());
    }
})->describe('Test email notification functionality');

// Schedule the automatic expiration of approved requests
use Illuminate\Console\Scheduling\Schedule;

app(Schedule::class)->command('requests:expire')->hourly();
