<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class SidebarNotificationService
{    /**
     * Get notification counts for the authenticated user's office
     */
    public static function getNotificationCounts($office = null)
    {
        // If no office provided, get from authenticated user
        if (!$office) {
            $user = Auth::user();
            if (!$user || !$user->Office) {
                return [
                    'approved' => 0,
                    'denied' => 0,
                    'pending' => 0,
                    'released' => 0,
                    'total_unread' => 0
                ];
            }
            $office = $user->Office;
        }

        // Get counts for different notification types
        $approvedCount = Notification::where('office', $office)
                                   ->where('action', 'request_approved')
                                   ->where('is_read', false)
                                   ->count();

        $deniedCount = Notification::where('office', $office)
                                 ->whereIn('action', ['request_denied', 'request_disapproved'])
                                 ->where('is_read', false)
                                 ->count();

        $pendingCount = Notification::where('office', $office)
                                   ->where('action', 'request_pending')
                                   ->where('is_read', false)
                                   ->count();

        $releasedCount = Notification::where('office', $office)
                                    ->whereIn('action', ['request_ready', 'request_released'])
                                    ->where('is_read', false)
                                    ->count();

        $totalUnread = Notification::where('office', $office)
                                 ->where('is_read', false)
                                 ->count();

        return [
            'approved' => $approvedCount,
            'denied' => $deniedCount,
            'disapproved' => $deniedCount, // Alias for denied
            'pending' => $pendingCount,
            'released' => $releasedCount,
            'total_unread' => $totalUnread
        ];
    }

    /**
     * Get notification counts for SuperAdmin (all offices)
     */
    public static function getSuperAdminNotificationCounts()
    {
        // Get counts for different notification types across all offices
        $approvedCount = Notification::where('action', 'request_approved')
                                   ->where('is_read', false)
                                   ->count();

        $deniedCount = Notification::whereIn('action', ['request_denied', 'request_disapproved'])
                                 ->where('is_read', false)
                                 ->count();

        $pendingCount = Notification::where('action', 'request_pending')
                                   ->where('is_read', false)
                                   ->count();

        $releasedCount = Notification::whereIn('action', ['request_ready', 'request_released'])
                                    ->where('is_read', false)
                                    ->count();

        $totalUnread = Notification::where('is_read', false)
                                 ->count();

        return [
            'approved' => $approvedCount,
            'denied' => $deniedCount,
            'disapproved' => $deniedCount, // Alias for denied
            'pending' => $pendingCount,
            'released' => $releasedCount,
            'total_unread' => $totalUnread
        ];
    }

    /**
     * Mark notifications as read for a specific office and action
     */
    public static function markAsRead($office, $action = null)
    {
        $query = Notification::where('office', $office)
                           ->where('is_read', false);

        if ($action) {
            $query->where('action', $action);
        }

        return $query->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Get recent notifications for the authenticated user's office
     */
    public static function getRecentNotifications($limit = 10)
    {
        $user = Auth::user();
        
        if (!$user || !$user->Office) {
            return collect();
        }

        return Notification::where('office', $user->Office)
                         ->orderBy('created_at', 'desc')
                         ->limit($limit)
                         ->get();
    }    /**
     * Create a notification for a request action
     */
    public function createRequestNotification($request, $action)
    {
        try {
            // Determine the message and target based on action
            $message = '';
            $targetUserId = null;
            $targetOffice = '';
            
            switch ($action) {
                case 'request_pending':
                    // When office creates request, notify SuperAdmin ONLY
                    $message = "New request submitted: {$request->supply_name} from {$request->user->Office}";
                    // Find SuperAdmin user - prioritize 'SuperAdmin' role over 'Admin'
                    $superAdmin = \App\Models\User::where('role', 'SuperAdmin')->first();
                    if (!$superAdmin) {
                        // Fallback to Admin role if no SuperAdmin found
                        $superAdmin = \App\Models\User::where('role', 'Admin')->first();
                    }
                    if (!$superAdmin) {
                        // Final fallback: create notification for admin office
                        $targetUserId = 1; // Assuming SuperAdmin has ID 1, adjust if needed
                        $targetOffice = 'Admin';
                    } else {
                        $targetUserId = $superAdmin->id;
                        $targetOffice = $superAdmin->Office ?? 'Admin';
                    }
                    break;
                    
                case 'request_approved':
                    // When SuperAdmin approves, notify the requesting office ONLY (but NOT SuperAdmin)
                    $message = "Your request for {$request->supply_name} has been approved.";
                    $targetUserId = $request->user_id; // The user who made the request
                    $targetOffice = $request->user->Office ?? 'Unknown';
                    
                    // IMPORTANT: If the requesting user IS the SuperAdmin, don't create notification
                    $superAdmin = \App\Models\User::where('role', 'SuperAdmin')->first();
                    if (!$superAdmin) {
                        $superAdmin = \App\Models\User::where('role', 'Admin')->first();
                    }
                    if ($superAdmin && $targetUserId == $superAdmin->id) {
                        \Log::info('Skipping approval notification - SuperAdmin approving their own request', [
                            'request_id' => $request->id,
                            'admin_id' => $superAdmin->id
                        ]);
                        return null; // Don't create notification
                    }
                    break;
                    
                case 'request_denied':
                    // When SuperAdmin denies, notify the requesting office ONLY (but NOT SuperAdmin)
                    $message = "Your request for {$request->supply_name} has been denied.";
                    $targetUserId = $request->user_id; // The user who made the request
                    $targetOffice = $request->user->Office ?? 'Unknown';
                    
                    // IMPORTANT: If the requesting user IS the SuperAdmin, don't create notification
                    $superAdmin = \App\Models\User::where('role', 'SuperAdmin')->first();
                    if (!$superAdmin) {
                        $superAdmin = \App\Models\User::where('role', 'Admin')->first();
                    }
                    if ($superAdmin && $targetUserId == $superAdmin->id) {
                        \Log::info('Skipping denial notification - SuperAdmin denying their own request', [
                            'request_id' => $request->id,
                            'admin_id' => $superAdmin->id
                        ]);
                        return null; // Don't create notification
                    }
                    break;
                    
                default:
                    $message = "Your request for {$request->supply_name} has been updated.";
                    $targetUserId = $request->user_id;
                    $targetOffice = $request->user->Office ?? 'Unknown';
            }

            // Log the notification creation details
            \Log::info('Creating notification record', [
                'target_user_id' => $targetUserId,
                'target_office' => $targetOffice,
                'action' => $action,
                'message' => $message,
                'request_id' => $request->id
            ]);

            // Create the notification record with proper targeting
            $notificationData = [
                'user_id' => $targetUserId,
                'office' => $targetOffice,
                'action' => $action,
                'message' => $message,
                'request_id' => $request->id,
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // If this is a batch notification, create a detailed message with supply names
            if (isset($request->batch_id) && isset($request->batch_request_ids)) {
                $batchCount = $request->batch_count ?? count($request->batch_request_ids);
                
                // Get all requests in this batch to extract supply names
                $batchRequests = \App\Models\Requests::whereIn('id', $request->batch_request_ids)->get();
                
                // Instead of using supply names, use the request purpose for a cleaner message
                $requestPurpose = $request->Purpose ?? $request->purpose ?? 'various office supplies';
                
                // Create detailed user-friendly message with purpose instead of supply names
                switch ($action) {
                    case 'request_pending':
                        if ($batchCount == 1) {
                            $notificationData['message'] = "New request submitted: {$requestPurpose} from {$request->user->Office}";
                        } else {
                            $notificationData['message'] = "New batch  request submitted: {$requestPurpose} from {$request->user->Office}";
                        }
                        break;
                    case 'request_approved':
                        if ($batchCount == 1) {
                            $notificationData['message'] = "Your request has been approved: {$requestPurpose}";
                        } else {
                            $notificationData['message'] = "Your batch request has been approved: {$requestPurpose}";
                        }
                        break;
                    case 'request_denied':
                        if ($batchCount == 1) {
                            $notificationData['message'] = "Your request has been denied: {$requestPurpose}";
                        } else {
                            $notificationData['message'] = "Your batch request has been denied: {$requestPurpose}";
                        }
                        break;
                    default:
                        if ($batchCount == 1) {
                            $notificationData['message'] = "Request updated: {$requestPurpose}";
                        } else {
                            $notificationData['message'] = "Batch request updated: {$requestPurpose}";
                        }
                }
                
                // Since we want to remove batch metadata, we'll just keep the clean message
                // No longer appending batch data to the message
                
                \Log::info('Creating batch notification with purpose', [
                    'batch_id' => $request->batch_id ?? 'N/A',
                    'request_ids' => $request->batch_request_ids ?? [],
                    'batch_count' => $batchCount,
                    'purpose' => $requestPurpose,
                    'display_message' => $notificationData['message']
                ]);
            }
            
            // Add view_enabled flag to determine if this notification should have a view button
            // Typically, notifications about requests should be viewable
            $notificationData['view_enabled'] = true;
            
            // Add details_url if not already present - this is what the view button will link to
            if (!isset($notificationData['details_url']) && isset($request->id)) {
                // Construct the URL based on request type and action
                if ($action == 'request_pending') {
                    $notificationData['details_url'] = "/admin/requests/view/" . $request->id;
                } else {
                    $notificationData['details_url'] = "/requests/view/" . $request->id;
                }
            }
            
            $notification = Notification::create($notificationData);

            // Enhanced logging to include view button related properties
            \Log::info('Notification created with proper targeting', [
                'notification_id' => $notification->id ?? 'unknown',
                'action' => $notification->action,
                'target_user' => $targetUserId,
                'target_office' => $targetOffice,
                'view_enabled' => $notification->view_enabled ?? false,
                'details_url' => $notification->details_url ?? null,
                'created_at' => $notification->created_at
            ]);

            return $notification;
        } catch (\Exception $e) {
            \Log::error('Failed to create notification: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
