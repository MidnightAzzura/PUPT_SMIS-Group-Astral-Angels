<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Requests;
use App\Models\Users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class ExpireApprovedRequestsTest extends TestCase
{
    use RefreshDatabase;

    protected $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->testUser = Users::create([
            'First_Name' => 'Test',
            'Last_Name' => 'User',
            'email' => 'test@example.com',
            'Office' => 'Test Office',
            'password' => bcrypt('password'),
        ]);
    }

    /** @test */
    public function it_expires_approved_requests_older_than_5_days()
    {
        // Create an approved request from 6 days ago
        $oldRequest = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST001',
            'supply_name' => 'Old Test Item',
            'supply_quantity' => 10,
            'Purpose' => 'Testing expiration',
            'Office' => 'Test Office',
            'status' => 'Approved',
            'approved_at' => Carbon::now()->subDays(6),
        ]);

        // Run the expiration command
        Artisan::call('requests:expire');

        // Refresh the model from database
        $oldRequest->refresh();

        // Assert the request is now expired
        $this->assertEquals('Expired', $oldRequest->status);
        $this->assertEquals('Not released within 5 days of approval', $oldRequest->expiration_reason);
        $this->assertNotNull($oldRequest->expires_at);
    }

    /** @test */
    public function it_does_not_expire_recent_approved_requests()
    {
        // Create an approved request from 3 days ago (within 5 days)
        $recentRequest = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST002',
            'supply_name' => 'Recent Test Item',
            'supply_quantity' => 5,
            'Purpose' => 'Testing no expiration',
            'Office' => 'Test Office',
            'status' => 'Approved',
            'approved_at' => Carbon::now()->subDays(3),
        ]);

        // Run the expiration command
        Artisan::call('requests:expire');

        // Refresh the model
        $recentRequest->refresh();

        // Assert the request is still approved
        $this->assertEquals('Approved', $recentRequest->status);
        $this->assertNull($recentRequest->expiration_reason);
        $this->assertNull($recentRequest->expires_at);
    }

    /** @test */
    public function it_does_not_expire_pending_requests()
    {
        // Create a pending request from 10 days ago
        $pendingRequest = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST003',
            'supply_name' => 'Pending Test Item',
            'supply_quantity' => 3,
            'Purpose' => 'Testing pending status',
            'Office' => 'Test Office',
            'status' => 'Pending',
            'approved_at' => null,
        ]);

        // Run the expiration command
        Artisan::call('requests:expire');

        // Refresh the model
        $pendingRequest->refresh();

        // Assert the request is still pending
        $this->assertEquals('Pending', $pendingRequest->status);
        $this->assertNull($pendingRequest->expiration_reason);
    }

    /** @test */
    public function it_does_not_expire_released_requests()
    {
        // Create a released request from 10 days ago
        $releasedRequest = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST004',
            'supply_name' => 'Released Test Item',
            'supply_quantity' => 8,
            'Purpose' => 'Testing released status',
            'Office' => 'Test Office',
            'status' => 'Released',
            'approved_at' => Carbon::now()->subDays(10),
        ]);

        // Run the expiration command
        Artisan::call('requests:expire');

        // Refresh the model
        $releasedRequest->refresh();

        // Assert the request is still released
        $this->assertEquals('Released', $releasedRequest->status);
        $this->assertNull($releasedRequest->expiration_reason);
    }

    /** @test */
    public function it_expires_multiple_old_requests_at_once()
    {
        // Create 3 old approved requests
        $oldRequests = [];
        for ($i = 1; $i <= 3; $i++) {
            $oldRequests[] = Requests::create([
                'user_id' => $this->testUser->user_id,
                'StockNumber' => "BULK{$i}",
                'supply_name' => "Bulk Test Item {$i}",
                'supply_quantity' => $i * 2,
                'Purpose' => 'Bulk testing',
                'Office' => 'Test Office',
                'status' => 'Approved',
                'approved_at' => Carbon::now()->subDays(7),
            ]);
        }

        // Run the expiration command
        Artisan::call('requests:expire');

        // Check all requests are expired
        foreach ($oldRequests as $request) {
            $request->refresh();
            $this->assertEquals('Expired', $request->status);
            $this->assertNotNull($request->expires_at);
        }
    }

    /** @test */
    public function it_handles_requests_exactly_5_days_old()
    {
        // Create a request exactly 5 days old
        $exactRequest = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'EXACT5',
            'supply_name' => 'Exact 5 Day Test',
            'supply_quantity' => 1,
            'Purpose' => 'Testing exact boundary',
            'Office' => 'Test Office',
            'status' => 'Approved',
            'approved_at' => Carbon::now()->subDays(5),
        ]);

        // Run the expiration command
        Artisan::call('requests:expire');

        // Refresh the model
        $exactRequest->refresh();

        // At exactly 5 days, it should NOT be expired (expires AFTER 5 days)
        $this->assertEquals('Approved', $exactRequest->status);
    }

    /** @test */
    public function command_output_shows_expired_count()
    {
        // Create 2 old requests
        Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'OUT1',
            'supply_name' => 'Output Test 1',
            'supply_quantity' => 1,
            'Purpose' => 'Testing output',
            'Office' => 'Test Office',
            'status' => 'Approved',
            'approved_at' => Carbon::now()->subDays(6),
        ]);

        Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'OUT2',
            'supply_name' => 'Output Test 2',
            'supply_quantity' => 2,
            'Purpose' => 'Testing output',
            'Office' => 'Test Office',
            'status' => 'Approved',
            'approved_at' => Carbon::now()->subDays(7),
        ]);

        // Run the command and capture output
        $exitCode = Artisan::call('requests:expire');

        // Check exit code is successful
        $this->assertEquals(0, $exitCode);

        // Check output contains success message
        $output = Artisan::output();
        $this->assertStringContainsString('2 approved request(s) have been expired', $output);
    }
}
