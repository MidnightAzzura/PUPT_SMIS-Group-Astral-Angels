<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Services\SidebarNotificationService;
use App\Services\NotificationDisplayService;
use App\Models\NotificationCounter;
use App\Models\Notification;

class NotificationDebugController extends Controller
{
    public function debug(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $debugData = [
            'user_info' => [
                'id' => $user->id,
                'role' => $user->role,
                'office' => $user->Office,
                'is_admin' => strtolower($user->role) === 'admin' || strtolower($user->role) === 'superadmin'
            ],
            'raw_data' => [],
            'service_results' => [],
            'view_composer_simulation' => []
        ];

        try {
            // Raw database data
            $debugData['raw_data']['notification_counters'] = NotificationCounter::all()->toArray();
            $debugData['raw_data']['notifications_summary'] = Notification::selectRaw('office, action, is_read, COUNT(*) as count')
                ->groupBy('office', 'action', 'is_read')
                ->get()
                ->toArray();

            // Test different services
            if (strtolower($user->role) === 'admin' || strtolower($user->role) === 'superadmin') {
                // Admin services
                $debugData['service_results']['sidebar_super_admin'] = SidebarNotificationService::getSuperAdminNotificationCounts();
                $debugData['service_results']['display_all_offices'] = NotificationDisplayService::getAllOfficesPageCounts();
                
                // Simulate View Composer logic for admin
                $notificationCounts = SidebarNotificationService::getSuperAdminNotificationCounts();
                $enhancedCounts = NotificationDisplayService::getAllOfficesPageCounts();
                $debugData['view_composer_simulation']['admin'] = array_merge($notificationCounts, $enhancedCounts);
                
            } else {
                // Regular user services
                $debugData['service_results']['sidebar_office'] = SidebarNotificationService::getNotificationCounts($user->Office);
                $debugData['service_results']['display_all_pages'] = NotificationDisplayService::getAllPageCounts();
                
                // Simulate View Composer logic for regular user
                $notificationCounts = NotificationDisplayService::getAllPageCounts();
                $legacyNotificationCounts = SidebarNotificationService::getNotificationCounts();
                $debugData['view_composer_simulation']['user'] = array_merge($legacyNotificationCounts, $notificationCounts);
            }

        } catch (\Exception $e) {
            $debugData['error'] = $e->getMessage();
            $debugData['trace'] = $e->getTraceAsString();
        }

        return response()->json($debugData);
    }
}
