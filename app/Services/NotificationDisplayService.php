<?php

namespace App\Services;

use App\Models\NotificationCounter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationDisplayService
{
    /**
     * Get comprehensive notification counts for display across all pages
     */
    public static function getAllPageCounts($office = null)
    {
        try {
            $user = Auth::user();
            
            // Check if this is an admin/superadmin user
            if ($user && (strtolower($user->role) === 'admin' || strtolower($user->role) === 'superadmin')) {
                // For admin/superadmin, get counts from ALL offices
                return self::getAllOfficesPageCounts();
            }
            
            // For regular users, get counts from their office only
            $userOffice = $office ?? ($user ? $user->Office : null);
            
            if (!$userOffice) {
                return self::getEmptyCounts();
            }

            // FIXED: Use actual Notification table data instead of NotificationCounter
            $counts = [
                'pending' => 0,
                'approved' => 0,
                'released' => 0,
                'disapproved' => 0,
                'denied' => 0, // Alias for disapproved
                'total_unread' => 0,
                'notifications' => 0, // For main notifications page
            ];

            // Count actual unread notifications by action type for this office
            $counts['approved'] = \App\Models\Notification::where('office', $userOffice)
                                                         ->where('action', 'request_approved')
                                                         ->where('is_read', false)
                                                         ->count();

            $counts['disapproved'] = \App\Models\Notification::where('office', $userOffice)
                                                            ->whereIn('action', ['request_denied', 'request_disapproved'])
                                                            ->where('is_read', false)
                                                            ->count();
            
            $counts['denied'] = $counts['disapproved']; // Alias

            $counts['pending'] = \App\Models\Notification::where('office', $userOffice)
                                                        ->where('action', 'request_pending')
                                                        ->where('is_read', false)
                                                        ->count();

            $counts['released'] = \App\Models\Notification::where('office', $userOffice)
                                                         ->whereIn('action', ['request_ready', 'request_released'])
                                                         ->where('is_read', false)
                                                         ->count();

            // Calculate total unread notifications for this office
            $counts['total_unread'] = \App\Models\Notification::where('office', $userOffice)
                                                             ->where('is_read', false)
                                                             ->count();
            $counts['notifications'] = $counts['total_unread'];

            return $counts;
        } catch (\Exception $e) {
            Log::error("Failed to get notification counts: " . $e->getMessage());
            return self::getEmptyCounts();
        }
    }

    /**
     * Update notification counts when request status changes
     */
    public static function updateCountsForStatusChange($office, $fromStatus, $toStatus)
    {
        try {
            // Decrement the "from" counter
            if ($fromStatus) {
                $fromCounter = NotificationCounter::getCounter($office, $fromStatus);
                $fromCounter->decrementCounter();
            }

            // Increment the "to" counter
            if ($toStatus) {
                $toCounter = NotificationCounter::getCounter($office, $toStatus);
                $toCounter->incrementCounter();
            }

            Log::info("Updated counters for office: {$office}, from: {$fromStatus}, to: {$toStatus}");
        } catch (\Exception $e) {
            Log::error("Failed to update counters: " . $e->getMessage());
        }
    }

    /**
     * Clear notifications for a specific page
     */
    public static function clearPageNotifications($office, $page)
    {
        try {
            $counter = NotificationCounter::getCounter($office, $page);
            $counter->reset();
            
            Log::info("Cleared notifications for office: {$office}, page: {$page}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to clear page notifications: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get badge HTML for display in views
     */
    public static function getBadgeHtml($count, $type = 'default')
    {
        if ($count <= 0) {
            return '';
        }

        $badgeClass = match($type) {
            'approved' => 'notification-badge success',
            'denied', 'disapproved' => 'notification-badge danger',
            'pending' => 'notification-badge warning',
            'released' => 'notification-badge purple',
            default => 'notification-badge'
        };

        return "<span class=\"{$badgeClass}\">{$count}</span>";
    }

    /**
     * Get empty counts array
     */
    private static function getEmptyCounts()
    {
        return [
            'pending' => 0,
            'approved' => 0,
            'released' => 0,
            'disapproved' => 0,
            'denied' => 0,
            'total_unread' => 0,
            'notifications' => 0,
        ];
    }

    /**
     * Get notification counts from all offices (for admin/superadmin)
     */
    public static function getAllOfficesPageCounts()
    {
        try {
            // FIXED: Use actual Notification table data instead of NotificationCounter
            // This ensures accurate badge counts based on real notifications
            
            $counts = [
                'pending' => 0,
                'approved' => 0,
                'released' => 0,
                'disapproved' => 0,
                'denied' => 0, // Alias for disapproved
                'total_unread' => 0,
                'notifications' => 0, // For main notifications page
            ];

            // Count actual unread notifications by action type
            $counts['approved'] = \App\Models\Notification::where('action', 'request_approved')
                                                         ->where('is_read', false)
                                                         ->count();

            $counts['disapproved'] = \App\Models\Notification::whereIn('action', ['request_denied', 'request_disapproved'])
                                                            ->where('is_read', false)
                                                            ->count();
            
            $counts['denied'] = $counts['disapproved']; // Alias

            $counts['pending'] = \App\Models\Notification::where('action', 'request_pending')
                                                        ->where('is_read', false)
                                                        ->count();

            $counts['released'] = \App\Models\Notification::whereIn('action', ['request_ready', 'request_released'])
                                                         ->where('is_read', false)
                                                         ->count();

            // Calculate total unread notifications
            $counts['total_unread'] = \App\Models\Notification::where('is_read', false)->count();
            $counts['notifications'] = $counts['total_unread'];

            Log::info("Admin notification counts from actual notifications table", $counts);
            
            return $counts;
        } catch (\Exception $e) {
            Log::error("Failed to get all offices notification counts: " . $e->getMessage());
            return self::getEmptyCounts();
        }
    }

    /**
     * Initialize counters for a new office
     */
    public static function initializeOfficeCounters($office)
    {
        try {
            $pages = ['pending', 'approved', 'released', 'disapproved'];
            
            foreach ($pages as $page) {
                NotificationCounter::getCounter($office, $page);
            }
            
            Log::info("Initialized counters for office: {$office}");
        } catch (\Exception $e) {
            Log::error("Failed to initialize counters for office {$office}: " . $e->getMessage());
        }
    }

    /**
     * Recalculate all counters based on actual request data
     */
    public static function recalculateAllCounters()
    {
        try {
            // This would be implemented based on your actual request model
            // For now, we'll just log that it was called
            Log::info("Recalculating all notification counters");
            
            // You can implement this to count actual requests from your database
            // and update the counters accordingly
            
        } catch (\Exception $e) {
            Log::error("Failed to recalculate counters: " . $e->getMessage());
        }
    }
}
