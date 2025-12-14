<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Requests;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequestsModelTest extends TestCase
{
    use RefreshDatabase;

    protected $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testUser = Users::create([
            'First_Name' => 'Test',
            'Last_Name' => 'User',
            'email' => 'test@example.com',
            'Office' => 'Test Office',
            'password' => bcrypt('password'),
        ]);
    }

    /** @test */
    public function is_expired_returns_true_for_requests_older_than_5_days()
    {
        $request = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST001',
            'supply_name' => 'Test Item',
            'supply_quantity' => 5,
            'Purpose' => 'Testing',
            'Office' => 'Test Office',
            'status' => 'Approved',
            'approved_at' => Carbon::now()->subDays(6),
        ]);

        $this->assertTrue($request->isExpired());
    }

    /** @test */
    public function is_expired_returns_false_for_recent_approved_requests()
    {
        $request = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST002',
            'supply_name' => 'Test Item',
            'supply_quantity' => 5,
            'Purpose' => 'Testing',
            'Office' => 'Test Office',
            'status' => 'Approved',
            'approved_at' => Carbon::now()->subDays(3),
        ]);

        $this->assertFalse($request->isExpired());
    }

    /** @test */
    public function is_expired_returns_false_for_non_approved_requests()
    {
        $request = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST003',
            'supply_name' => 'Test Item',
            'supply_quantity' => 5,
            'Purpose' => 'Testing',
            'Office' => 'Test Office',
            'status' => 'Pending',
            'approved_at' => null,
        ]);

        $this->assertFalse($request->isExpired());
    }

    /** @test */
    public function days_until_expiration_returns_correct_number()
    {
        $request = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST004',
            'supply_name' => 'Test Item',
            'supply_quantity' => 5,
            'Purpose' => 'Testing',
            'Office' => 'Test Office',
            'status' => 'Approved',
            'approved_at' => Carbon::now()->subDays(2),
        ]);

        $daysLeft = $request->daysUntilExpiration();
        
        // Should have 3 days left (5 days total - 2 days passed)
        $this->assertEquals(3, $daysLeft);
    }

    /** @test */
    public function days_until_expiration_returns_zero_for_expired_requests()
    {
        $request = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST005',
            'supply_name' => 'Test Item',
            'supply_quantity' => 5,
            'Purpose' => 'Testing',
            'Office' => 'Test Office',
            'status' => 'Approved',
            'approved_at' => Carbon::now()->subDays(10),
        ]);

        $daysLeft = $request->daysUntilExpiration();
        
        // Should return 0 for expired requests
        $this->assertEquals(0, $daysLeft);
    }

    /** @test */
    public function days_until_expiration_returns_null_for_non_approved()
    {
        $request = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST006',
            'supply_name' => 'Test Item',
            'supply_quantity' => 5,
            'Purpose' => 'Testing',
            'Office' => 'Test Office',
            'status' => 'Pending',
            'approved_at' => null,
        ]);

        $daysLeft = $request->daysUntilExpiration();
        
        $this->assertNull($daysLeft);
    }

    /** @test */
    public function get_expiration_date_returns_correct_date()
    {
        $approvalDate = Carbon::now()->subDays(2);
        $expectedExpiration = $approvalDate->copy()->addDays(5);
        
        $request = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST007',
            'supply_name' => 'Test Item',
            'supply_quantity' => 5,
            'Purpose' => 'Testing',
            'Office' => 'Test Office',
            'status' => 'Approved',
            'approved_at' => $approvalDate,
        ]);

        $expirationDate = $request->getExpirationDate();
        
        $this->assertEquals(
            $expectedExpiration->format('Y-m-d H:i:s'),
            $expirationDate
        );
    }

    /** @test */
    public function get_expiration_date_returns_null_for_non_approved()
    {
        $request = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST008',
            'supply_name' => 'Test Item',
            'supply_quantity' => 5,
            'Purpose' => 'Testing',
            'Office' => 'Test Office',
            'status' => 'Pending',
            'approved_at' => null,
        ]);

        $expirationDate = $request->getExpirationDate();
        
        $this->assertNull($expirationDate);
    }

    /** @test */
    public function approved_at_and_expires_at_are_cast_to_datetime()
    {
        $request = Requests::create([
            'user_id' => $this->testUser->user_id,
            'StockNumber' => 'TEST009',
            'supply_name' => 'Test Item',
            'supply_quantity' => 5,
            'Purpose' => 'Testing',
            'Office' => 'Test Office',
            'status' => 'Approved',
            'approved_at' => now(),
            'expires_at' => now()->addDays(5),
        ]);

        $this->assertInstanceOf(Carbon::class, $request->approved_at);
        $this->assertInstanceOf(Carbon::class, $request->expires_at);
    }
}
