<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class AdminAuditService
{
    /**
     * Log when a request is approved.
     *
     * @param  mixed  $request
     * @return void
     */
    public static function logRequestApproved($request)
    {
        try {
            Log::info('AdminAudit: Request approved', [
                'request_id' => $request->id ?? null,
                'stock_number' => $request->StockNumber ?? ($request->stock_number ?? null),
                'user_id' => $request->user_id ?? ($request->user->id ?? null),
                'status' => $request->status ?? 'Approved',
                'timestamp' => now()->toDateTimeString(),
            ]);
        } catch (Exception $e) {
            Log::error('AdminAudit: Failed to log approval - ' . $e->getMessage());
        }
    }

    /**
     * Log when a request is denied.
     *
     * @param  mixed  $request
     * @return void
     */
    public static function logRequestDenied($request)
    {
        try {
            Log::warning('AdminAudit: Request denied', [
                'request_id' => $request->id ?? null,
                'stock_number' => $request->StockNumber ?? ($request->stock_number ?? null),
                'user_id' => $request->user_id ?? ($request->user->id ?? null),
                'status' => $request->status ?? 'Disapproved',
                'timestamp' => now()->toDateTimeString(),
            ]);
        } catch (Exception $e) {
            Log::error('AdminAudit: Failed to log denial - ' . $e->getMessage());
        }
    }

    /**
     * Log when a request is released.
     *
     * @param  mixed  $request
     * @return void
     */
    public static function logRequestReleased($request)
    {
        try {
            Log::info('AdminAudit: Request released', [
                'request_id' => $request->id ?? null,
                'stock_number' => $request->StockNumber ?? ($request->stock_number ?? null),
                'user_id' => $request->user_id ?? ($request->user->id ?? null),
                'status' => $request->status ?? 'released',
                'timestamp' => now()->toDateTimeString(),
            ]);
        } catch (Exception $e) {
            Log::error('AdminAudit: Failed to log release - ' . $e->getMessage());
        }
    }
}
