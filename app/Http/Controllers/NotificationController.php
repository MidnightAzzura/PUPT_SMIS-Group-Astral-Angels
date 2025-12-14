<?php

namespace App\Http\Controllers;

use App\Services\SidebarNotificationService;
use App\Models\Notification;
use App\Models\Requests;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(SidebarNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get request details for notification
     */
    protected function getRequestDetails($requestId)
    {
        $request = Requests::with(['user', 'items'])->find($requestId);
        if (!$request) {
            throw new \Exception('Request not found');
        }
        return $request;
    }

    /**
     * Get notification counts for the current user
     */
    public function getCounts(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Use the EXACT same logic as NotificationComposer to ensure consistency
        if (strtolower($user->role) === 'admin' || strtolower($user->role) === 'superadmin') {
            // For admin/superadmin, get system-wide notification counts
            // Use the SAME service as the View Composer
            try {
                $enhancedCounts = \App\Services\NotificationDisplayService::getAllOfficesPageCounts();
                
                // Also get legacy counts for backward compatibility (but enhanced takes priority)
                try {
                    $notificationCounts = \App\Services\SidebarNotificationService::getSuperAdminNotificationCounts();
                    // Merge both, giving priority to enhanced counts (same as View Composer)
                    $counts = array_merge($notificationCounts, $enhancedCounts);
                } catch (\Exception $e) {
                    // If legacy counts fail, just use the enhanced counts
                    $counts = $enhancedCounts;
                    \Log::warning("Legacy notification counts failed for admin: " . $e->getMessage());
                }
            } catch (\Exception $e) {
                // Fallback for any error
                $counts = [
                    'approved' => 0,
                    'denied' => 0,
                    'disapproved' => 0,
                    'pending' => 0,
                    'released' => 0,
                    'total_unread' => 0
                ];
                \Log::error("Admin notification counts failed: " . $e->getMessage());
            }
        } else {
            // For regular users, get office-specific counts
            // Use the EXACT same logic as NotificationComposer
            try {
                $notificationCounts = \App\Services\NotificationDisplayService::getAllPageCounts();
                
                // Also include the original service for backward compatibility (same as View Composer)
                $legacyNotificationCounts = $this->notificationService->getNotificationCounts($user->Office);
                
                // Merge both for maximum compatibility (same as View Composer)
                $counts = array_merge($legacyNotificationCounts, $notificationCounts);
            } catch (\Exception $e) {
                // Fallback to basic counts if there's an error
                $counts = [
                    'approved' => 0,
                    'denied' => 0,
                    'disapproved' => 0,
                    'pending' => 0,
                    'released' => 0,
                    'total_unread' => 0
                ];
                \Log::error("User notification counts failed: " . $e->getMessage());
            }
        }
        
        \Log::info('API notification counts', [
            'user_role' => $user->role,
            'user_office' => $user->Office,
            'counts' => $counts,
            'timestamp' => now()->toDateTimeString()
        ]);
        
        return response()->json([
            'success' => true,
            'counts' => $counts
        ]);
    }    /**
     * Display notifications page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Get filter parameter
        $filter = $request->input('filter', 'all');
        $officeFilter = $request->input('office', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        
        // Check if this is a JSON/AJAX request
        $isJsonRequest = $request->ajax() || $request->wantsJson() || $request->input('json') === 'true';

        // Log for debugging
        \Log::info('Loading notifications', [
            'office' => $user->Office,
            'user' => $user->id,
            'role' => $user->role,
            'filter' => $filter,
            'office_filter' => $officeFilter,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'isJsonRequest' => $isJsonRequest
        ]);

        // Build query with role-based filtering
        $query = Notification::query();
        
        // FIXED: SuperAdmin should only see notifications targeted to them, not all notifications
        if ($user->role === 'SuperAdmin' || $user->role === 'Admin') {
            // SuperAdmin only sees notifications specifically targeted to them
            $query->where('user_id', $user->id);
            
            // If office filter is applied, also include notifications for that office (for admin oversight)
            if ($request->has('office') && $request->office !== 'all') {
                $query->orWhere('office', $request->office);
            }
        } else {
            // Regular users see notifications for their office AND specifically addressed to them
            $query->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('office', $user->office);
            });
        }
        
        // Apply status filter
        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'approved') {
            $query->where('action', 'request_approved');
        } elseif ($filter === 'denied') {
            $query->where('action', 'request_denied');
        }
        
        // Get notifications with pagination (10 per page)
        $notifications = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Preserve query parameters for pagination links
        $notifications->appends($request->query());

        // Log the notification count and details
        $firstNotification = $notifications->first();
        \Log::info('Fetched notifications', [
            'count' => $notifications->count(),
            'filter' => $filter,
            'role' => $user->role,
            'office' => $user->Office,
            'first_item' => $firstNotification ? [
                'id' => $firstNotification->id ?? 'not_set',
                'notification_id' => $firstNotification->notification_id ?? 'not_set',
                'action' => $firstNotification->action ?? 'not_set'
            ] : 'no_notifications'
        ]);
        
        // Get notification counts for sidebar (role-based)
        if ($user->role === 'superadmin') {
            $notificationCounts = $this->notificationService->getSuperAdminNotificationCounts();
        } else {
            $notificationCounts = $this->notificationService->getNotificationCounts($user->Office);
        }
        
        // Get distinct notification actions for filtering (role-based)
        $notificationActionsQuery = Notification::query();
        if ($user->role !== 'superadmin') {
            $notificationActionsQuery->where('office', $user->Office);
        }
        $notificationActions = $notificationActionsQuery->distinct()->pluck('action')->toArray();
        
        // Get distinct offices for SuperAdmin filtering
        $offices = [];
        if ($user->role === 'superadmin') {
            $offices = Notification::distinct()->pluck('office')->filter()->toArray();
        }        // If this is a JSON request, return the notification table HTML as JSON
        if ($isJsonRequest) {
            try {
                $view = view('notifications.partials.notification_table', 
                    compact('notifications', 'notificationCounts', 'filter', 'notificationActions', 'offices')
                )->render();
                
                return response()->json([
                    'success' => true,
                    'html' => $view,
                    'counts' => $notificationCounts
                ]);
            } catch (\Exception $e) {
                \Log::error('Error rendering notification table: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'error' => 'Error rendering notification table',
                    'counts' => $notificationCounts
                ]);
            }
        }

        // Otherwise return the full view
        return view('notifications.new-index', compact('notifications', 'notificationCounts', 'filter', 'notificationActions', 'offices', 'officeFilter', 'dateFrom', 'dateTo'));
    }

    /**
     * Clear notifications for a specific page
     */
    public function clearPage(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $page = $request->input('page');
        if (!$page) {
            return response()->json(['error' => 'Page parameter required'], 400);
        }

        // Map page names to actions
        $actionMap = [
            'approved' => 'request_approved',
            'denied' => 'request_denied',
            'disapproved' => 'request_denied'
        ];

        $action = $actionMap[$page] ?? null;
        if (!$action) {
            return response()->json(['error' => 'Invalid page parameter'], 400);
        }

        // Mark notifications as read for this office and action
        Notification::where('office', $user->Office)
            ->where('action', $action)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Notifications cleared for ' . $page
        ]);
    }    /**
     * Mark specific notifications as read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notificationIds = $request->input('notification_ids', []);
        if (empty($notificationIds)) {
            return response()->json(['error' => 'No notification IDs provided'], 400);
        }

        // Log for debugging
        \Log::info('Marking notifications as read', [
            'ids' => $notificationIds,
            'office' => $user->Office,
            'user' => $user->id
        ]);        // Mark notifications as read for this office
        $updated = Notification::whereIn('notification_id', $notificationIds)
            ->where('office', $user->Office)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now()
            ]);

        // Log result
        \Log::info('Mark as read result', [
            'updated_count' => $updated
        ]);

        return response()->json([
            'success' => true,
            'message' => $updated . ' notifications marked as read'
        ]);
    }

    /**
     * Mark all notifications as read for the current user's office or selected office (SuperAdmin)
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Build the query based on user role
        $query = Notification::where('is_read', false);
        
        // Determine which office to mark as read
        if (strtolower($user->role) === 'superadmin') {
            // For SuperAdmin, check if a specific office is selected
            $selectedOffice = $request->input('office');
            
            if ($selectedOffice && $selectedOffice !== 'all') {
                // Mark notifications for the selected office only
                $query->where('office', $selectedOffice);
                $context = "for office: {$selectedOffice}";
            } else {
                // If no specific office selected, mark ALL notifications (legacy behavior)
                // This maintains backward compatibility when SuperAdmin selects "All Offices"
                $context = "for all offices";
            }
        } else {
            // For regular users, only mark notifications for their office
            $query->where('office', $user->Office);
            $context = "for office: {$user->Office}";
        }

        $updated = $query->update([
            'is_read' => true,
            'read_at' => Carbon::now()
        ]);

        \Log::info('Mark all as read executed', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_office' => $user->Office,
            'selected_office' => $request->input('office', 'not_specified'),
            'updated_count' => $updated,
            'context' => $context
        ]);

        return response()->json([
            'success' => true,
            'message' => $updated . ' notifications marked as read ' . $context
        ]);
    }

    /**
     * Recalculate all counters (admin only)
     */
    public function recalculateCounters(): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !in_array(strtolower($user->role), ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // This could be used to fix any inconsistencies in notification counts
        // For now, we'll just return success as the counts are calculated dynamically
        
        return response()->json([
            'success' => true,
            'message' => 'Counters recalculated successfully'
        ]);
    }

    /**
     * Delete a specific notification
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Build query based on user role
        $query = Notification::where('notification_id', $id);
        
        // If user is not SuperAdmin, only allow deleting notifications from their office
        if (strtolower($user->role) !== 'superadmin') {
            $query->where('office', $user->Office);
        }

        $notification = $query->first();

        if (!$notification) {
            \Log::warning('Notification not found for deletion', [
                'notification_id' => $id,
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_office' => $user->Office
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Notification not found or access denied'
            ], 404);
        }

        try {
            \Log::info('Deleting notification', [
                'notification_id' => $notification->notification_id,
                'user_id' => $user->id,
                'user_role' => $user->role,
                'notification_office' => $notification->office
            ]);
            
            $notification->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting notification', [
                'notification_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to create a notification for request approval
     */
    public function createApprovalNotification(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'batchKey' => 'required|string',
            'action' => 'required|string',
            'targetOffice' => 'required|string',
            'requestId' => 'required|integer',
            'targetUserId' => 'required|integer'
        ]);

        try {
            // Parse the batch key to extract normalized purpose and timestamp
            $parts = explode('_', $validated['batchKey']);
            $normalizedPurpose = $parts[0];
            
            // Create a notification
            $notification = new Notification();
            $notification->user_id = $validated['targetUserId']; // The user who made the request
            $notification->admin_id = $user->id; // The admin who approved
            $notification->target_user_id = $validated['targetUserId'];
            $notification->request_id = $validated['requestId'];
            $notification->office = $validated['targetOffice'];
            $notification->action = $validated['action'];
            $notification->message = "Your request has been " . ($validated['action'] === 'request_approved' ? 'approved' : 'denied') . ": " . ucfirst($normalizedPurpose);
            $notification->is_read = false;
            $notification->save();
            
            // Get updated counts
            $counts = $this->notificationService->getNotificationCounts($user->Office);
            
            return response()->json([
                'success' => true,
                'message' => 'Approval notification created successfully',
                'notification_id' => $notification->id,
                'counts' => $counts
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating approval notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to create a notification for request denial
     */
    public function createDenialNotification(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'batchKey' => 'required|string',
            'action' => 'required|string',
            'targetOffice' => 'required|string',
            'requestId' => 'required|integer',
            'targetUserId' => 'required|integer'
        ]);

        try {
            // Parse the batch key to extract normalized purpose and timestamp
            $parts = explode('_', $validated['batchKey']);
            $normalizedPurpose = $parts[0];
            
            // Create a notification
            $notification = new Notification();
            $notification->user_id = $validated['targetUserId']; // The user who made the request
            $notification->admin_id = $user->id; // The admin who denied
            $notification->target_user_id = $validated['targetUserId'];
            $notification->request_id = $validated['requestId'];
            $notification->office = $validated['targetOffice'];
            $notification->action = $validated['action'];
            $notification->message = "Your request has been denied: " . ucfirst($normalizedPurpose);
            $notification->is_read = false;
            $notification->save();
            
            // Get updated counts
            $counts = $this->notificationService->getNotificationCounts($user->Office);
            
            return response()->json([
                'success' => true,
                'message' => 'Denial notification created successfully',
                'notification_id' => $notification->id,
                'counts' => $counts
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating denial notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch new notifications via AJAX
     */
    public function fetchNewNotifications(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $lastRefresh = $request->input('last_refresh');
        
        $query = Notification::query();
        
        // Apply office restrictions based on user role
        if ($user->role === 'SuperAdmin' || $user->role === 'Admin') {
            // FIXED: SuperAdmin only sees notifications targeted to them
            $query->where('user_id', $user->id);
            
            // If office filter is applied, also include notifications for that office (for admin oversight)
            if ($request->has('office') && $request->office !== 'all') {
                $query->orWhere('office', $request->office);
            }
        } else {
            // Regular users only see notifications for their office
            $query->where('office', $user->Office);
        }
        
        // Only fetch notifications created after the last refresh
        if ($lastRefresh) {
            $query->where('created_at', '>', $lastRefresh);
        }
        
        $newNotifications = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'count' => $newNotifications->count(),
            'notifications' => $newNotifications
        ]);
    }

    /**
     * Get batch details for a notification
     */
    public function getBatchDetails($notificationId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Find the notification
            $notification = Notification::where('notification_id', $notificationId)->first();
            
            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            // Check if user has permission to view this notification
            if ($user->role !== 'SuperAdmin' && $user->role !== 'Admin' && $notification->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // Extract batch information from the message
            $batchInfo = null;
            $requestIds = [];
            
            if (preg_match('/\[BATCH:(.*?)\]/', $notification->message, $matches)) {
                $batchData = json_decode($matches[1], true);
                if ($batchData && isset($batchData['request_ids'])) {
                    $batchInfo = $batchData;
                    $requestIds = $batchData['request_ids'];
                }
            }

            // If no batch info found, try to find requests based on timing and user
            if (empty($requestIds)) {
                // For non-batch notifications or fallback, just show the single request
                if ($notification->request_id) {
                    $requestIds = [$notification->request_id];
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No request details found for this notification'
                    ], 404);
                }
            }

            // Get the actual request details
            $requests = Requests::with(['user'])->whereIn('id', $requestIds)->get();
            
            if ($requests->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request details not found'
                ], 404);
            }

            // Generate HTML for the batch details
            $html = view('notifications.partials.batch_details', [
                'notification' => $notification,
                'requests' => $requests,
                'batchInfo' => $batchInfo
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching batch details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading batch details: ' . $e->getMessage()
            ], 500);
        }
    }
}
