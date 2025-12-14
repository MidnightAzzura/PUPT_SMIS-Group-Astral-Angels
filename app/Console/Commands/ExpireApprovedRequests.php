<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Requests;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpireApprovedRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requests:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically expire approved requests that have not been released within 5 days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now('Asia/Manila');
        $expirationThreshold = $now->copy()->subDays(5);
        
        $this->info('Checking for approved requests older than 5 days...');
        
        // Find approved requests older than 5 days based on updated_at
        // This ensures we're checking from when the request was last modified (approved)
        $expiredRequests = Requests::where('status', 'Approved')
            ->where('updated_at', '<=', $expirationThreshold)
            ->get();
        
        if ($expiredRequests->isEmpty()) {
            $this->info('No requests to expire.');
            return Command::SUCCESS;
        }
        
        $expiredCount = 0;
        
        foreach ($expiredRequests as $request) {
            $request->status = 'Expired';
            $request->updated_by = 'System Auto-Expiration';
            $request->updated_at = $now;
            $request->save();
            
            $expiredCount++;
            
            Log::info("Request #{$request->id} expired automatically", [
                'request_id' => $request->id,
                'stock_number' => $request->StockNumber,
                'approved_at' => $request->updated_at,
                'expired_at' => $now
            ]);
            
            $this->line("Expired Request #{$request->id} - Stock: {$request->StockNumber}");
        }
        
        $this->info("Successfully expired {$expiredCount} approved request(s)");
        
        return Command::SUCCESS;
    }
}
