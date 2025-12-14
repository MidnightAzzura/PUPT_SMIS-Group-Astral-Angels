<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Services\SidebarNotificationService;
use App\Services\NotificationDisplayService;

class NotificationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $user = auth()->user();
        
        // Check if user is admin or superadmin
        if ($user && (strtolower($user->role) === 'admin' || strtolower($user->role) === 'superadmin')) {
            // For admin/superadmin, get system-wide notification counts
            $notificationCounts = SidebarNotificationService::getSuperAdminNotificationCounts();
            
            // Also get enhanced counts for broader compatibility
            try {
                $enhancedCounts = NotificationDisplayService::getAllOfficesPageCounts();
                // Merge both, giving priority to enhanced counts
                $finalCounts = array_merge($notificationCounts, $enhancedCounts);
            } catch (\Exception $e) {
                // If enhanced counts fail, just use the basic admin counts
                $finalCounts = $notificationCounts;
                \Log::warning("Enhanced notification counts failed for admin: " . $e->getMessage());
            }
        } else {
            // For regular users, get office-specific counts
            try {
                $notificationCounts = NotificationDisplayService::getAllPageCounts();
                
                // Also include the original service for backward compatibility
                $legacyNotificationCounts = SidebarNotificationService::getNotificationCounts();
                
                // Merge both for maximum compatibility
                $finalCounts = array_merge($legacyNotificationCounts, $notificationCounts);
            } catch (\Exception $e) {
                // Fallback to basic counts if there's an error
                $finalCounts = [
                    'approved' => 0,
                    'denied' => 0,
                    'disapproved' => 0,
                    'pending' => 0,
                    'released' => 0,
                    'total_unread' => 0
                ];
                \Log::warning("Notification counts failed for user: " . $e->getMessage());
            }
        }
        
        // Debug logging for admin users
        if ($user && (strtolower($user->role) === 'admin' || strtolower($user->role) === 'superadmin')) {
            \Log::info("Admin notification counts: ", $finalCounts);
        }
        
        $view->with('notificationCounts', $finalCounts);
    }
}
