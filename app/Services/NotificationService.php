<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationCounter;
use App\Models\Requests;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $file;

    public function __construct()
    {
        // Save the file in the storage folder for backward compatibility
        $this->file = storage_path('app/notifications.json');
    }

    /**
     * Load notifications from JSON file (backward compatibility)
     */
    public function load()
    {
        if (file_exists($this->file)) {
            $data = file_get_contents($this->file);
            return json_decode($data, true) ?: [];
        }
        return [];
    }

    /**
     * Save notifications to JSON file (backward compatibility)
     */
    public function save($notifications)
    {
        // Ensure the storage/app directory exists
        $directory = dirname($this->file);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        file_put_contents($this->file, json_encode($notifications, JSON_PRETTY_PRINT));
    }

    /**
     * Create a notification for request status change
     * 
     * @param mixed $data Request object or array of notification data
     * @param string|null $type Notification type (only used when $data is a Request object)
     * @param string|null $message Optional message override
     * @return Notification
     */
    public function createRequestNotification($data, $type = null, $message = null)
    {
        try {
            $notification = null;

            if (is_array($data)) {
                // Handle array input (new format)
                $notification = Notification::create([
                    'user_id' => $data['user_id'],
                    'admin_id' => $data['admin_id'],
                    'target_user_id' => $data['target_user_id'],
                    'request_id' => $data['request_id'],
                    'office' => $data['office'],
                    'action' => $data['action'],
                    'message' => $data['message'],
                    'is_read' => false
                ]);

                \Log::info('Created notification (new format)', [
                    'notification_id' => $notification->id,
                    'request_id' => $data['request_id'],
                    'user_id' => $data['user_id'],
                    'admin_id' => $data['admin_id']
                ]);

                // Send email if this is an action notification
                if (isset($data['action']) && in_array($data['action'], ['request_approved', 'request_denied'])) {
                    $this->sendEmailNotification($data['request_id'], $data['action'], $data['message']);
                }
            } else {
                // Handle Request object (legacy format)
                $defaultMessage = $this->getNotificationMessage($type, $data);
                $office = $data->Office ?? ($data->user ? $data->user->Office : 'Unknown');
                
                \Log::info('Creating notification (legacy format)', [
                    'request_id' => $data->id,
                    'type' => $type,
                    'user_id' => $data->user_id,
                    'office' => $office
                ]);
                
                $notification = Notification::create([
                    'action' => $type,
                    'user_id' => $data->user_id,
                    'office' => $office,
                    'message' => $message ?? $defaultMessage,
                    'is_read' => false,
                    'request_id' => $data->id
                ]);

                // Send email notification for legacy format
                $this->sendEmailNotification($data->id, $type, $message ?? $defaultMessage);
            }

            // Update counters based on status
            if ($notification) {
                $this->updateCountersForRequest($notification->office, $notification->action);
            }

            return $notification;
        } catch (\Exception $e) {
            \Log::error('Failed to create notification', [
                'error' => $e->getMessage(),
                'data' => is_array($data) ? json_encode($data) : 'Request object',
                'type' => $type,
                'stack_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update notification counters for request status change
     */
    public function updateCountersForRequest($office, $type)
    {
        if (!$office || !$type) {
            Log::warning('Cannot update counters - missing office or type', [
                'office' => $office,
                'type' => $type
            ]);
            return;
        }
        
        Log::info('Updating counters', [
            'office' => $office,
            'type' => $type
        ]);
        
        switch ($type) {            case 'request_approved':
                // Increment approved counter
                $counter = NotificationCounter::getCounter($office, 'approved');
                $counter->incrementCounter();
                
                // Decrement pending counter if exists
                $pendingCounter = NotificationCounter::getCounter($office, 'pending');
                $pendingCounter->decrementCounter();
                break;
                  case 'request_denied':
            case 'request_disapproved':
                // Increment disapproved counter
                $counter = NotificationCounter::getCounter($office, 'disapproved');
                $counter->incrementCounter();
                
                // Decrement pending counter if exists
                $pendingCounter = NotificationCounter::getCounter($office, 'pending');
                $pendingCounter->decrementCounter();
                break;
                  case 'request_ready':
            case 'request_released':
                // Increment released counter
                $counter = NotificationCounter::getCounter($office, 'released');
                $counter->incrementCounter();
                
                // Decrement approved counter if exists
                $approvedCounter = NotificationCounter::getCounter($office, 'approved');
                $approvedCounter->decrementCounter();
                break;
                  case 'request_pending':
                // Increment pending counter
                $counter = NotificationCounter::getCounter($office, 'pending');
                $counter->incrementCounter();
                break;
        }
    }

    /**
     * Clear notifications for a specific page visit
     */
    public function clearPageNotifications($office, $page)
    {
        try {
            // Mark notifications as read for this office/page combination
            $user = Auth::user();
            
            if ($user && $user->office === $office) {
                // Clear counter for this office and page
                $counter = NotificationCounter::getCounter($office, $page);
                $counter->reset();
                
                // Mark related notifications as read
                Notification::where('office', $office)
                    ->where('is_read', false)
                    ->whereIn('type', $this->getNotificationTypesForPage($page))
                    ->update([
                        'is_read' => true,
                        'read_at' => now()
                    ]);
                    
                Log::info("Cleared notifications for office: {$office}, page: {$page}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to clear page notifications: " . $e->getMessage());
        }
    }

    /**
     * Get notification counts for sidebar
     */
    public function getNotificationCounts($office = null, $userRole = null)
    {
        $counts = [];
        
        if (!$office && Auth::user()) {
            $office = Auth::user()->office;
            $userRole = Auth::user()->role;
        }

        if ($office) {
            $pages = ['pending', 'approved', 'released', 'disapproved'];
            
            foreach ($pages as $page) {
                $counter = NotificationCounter::getCounter($office, $page);
                $counts[$page] = $counter->count;
            }
        }

        // For admin/superadmin, get counts from all offices
        if (in_array($userRole, ['admin', 'superadmin'])) {
            $adminCounts = [];
            $offices = Users::distinct()->pluck('office')->filter();
            
            foreach ($offices as $officeItem) {
                foreach (['pending', 'approved', 'released', 'disapproved'] as $page) {
                    $counter = NotificationCounter::getCounter($officeItem, $page);
                    if (!isset($adminCounts[$page])) {
                        $adminCounts[$page] = 0;
                    }
                    $adminCounts[$page] += $counter->count;
                }
            }
            
            $counts['admin_totals'] = $adminCounts;
        }

        return $counts;
    }

    /**
     * Send email notification
     */
    protected function sendEmailNotification($requestId, $type, $message)
    {
        try {
            $request = Requests::find($requestId);
            if (!$request) {
                Log::warning("Cannot send email - request not found", ['request_id' => $requestId]);
                return;
            }

            $user = Users::find($request->user_id);
            if (!$user || !$user->email) {
                Log::warning("Cannot send email - user not found or no email", ['user_id' => $request->user_id]);
                return;
            }

            $subject = $this->getEmailSubject($type);
            $emailData = [
                'user' => $user,
                'request' => $request,
                'message' => $message,
                'type' => $type
            ];

            // Here you would implement your email sending logic
            // For now, we'll just log it
            Log::info("Email notification would be sent", [
                'to' => $user->email,
                'subject' => $subject,
                'request_id' => $requestId,
                'type' => $type
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to send email notification", [
                'error' => $e->getMessage(),
                'request_id' => $requestId,
                'type' => $type,
                'stack_trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get notification title based on type
     */
    protected function getNotificationTitle($type)
    {
        return match($type) {
            'request_approved' => 'Request Approved',
            'request_denied', 'request_disapproved' => 'Request Disapproved',
            'request_ready', 'request_released' => 'Request Ready for Release',
            'request_pending' => 'New Request Submitted',
            default => 'Request Update'
        };
    }

    /**
     * Get notification message based on type
     */    protected function getNotificationMessage($type, $request)
    {
        // Get supply name from the request (use supply_name or SupplyDescription)
        $supplyName = $request->supply_name ?? $request->SupplyDescription ?? 'requested item';
        
        return match($type) {
            'request_approved' => "Your request for {$supplyName} has been approved.",
            'request_denied', 'request_disapproved' => "Your request for {$supplyName} has been disapproved.",
            'request_ready', 'request_released' => "Your request for {$supplyName} is ready for release.",
            'request_pending' => "New request for {$supplyName} submitted.",
            default => "Update on your request for {$supplyName}."        };
    }

    /**
     * Get email subject based on type
     */
    protected function getEmailSubject($type)
    {
        return match($type) {
            'request_approved' => 'SMIS - Request Approved',
            'request_denied', 'request_disapproved' => 'SMIS - Request Disapproved',
            'request_ready', 'request_released' => 'SMIS - Request Ready for Release',
            'request_pending' => 'SMIS - New Request Submitted',
            default => 'SMIS - Request Update'
        };
    }

    /**
     * Get notification types for a specific page
     */
    protected function getNotificationTypesForPage($page)
    {
        return match($page) {
            'pending' => ['request_pending'],
            'approved' => ['request_approved'],
            'released' => ['request_ready', 'request_released'],
            'disapproved' => ['request_denied', 'request_disapproved'],
            default => []
        };
    }

    /**
     * Recalculate all counters based on actual request data
     */    public function recalculateCounters()
    {
        try {
            $offices = Users::distinct()->pluck('office')->filter();
            
            foreach ($offices as $office) {
                // Get actual counts from requests (case-insensitive)
                $pendingCount = Requests::where('Office', $office)
                    ->whereRaw('LOWER(status) = ?', ['pending'])
                    ->count();
                $approvedCount = Requests::where('Office', $office)
                    ->whereRaw('LOWER(status) = ?', ['approved'])
                    ->count();
                $releasedCount = Requests::where('Office', $office)
                    ->whereRaw('LOWER(status) = ?', ['released'])
                    ->count();
                $disapprovedCount = Requests::where('Office', $office)
                    ->whereRaw('LOWER(status) IN (?, ?, ?, ?)', ['disapproved', 'denied', 'cancelled', 'expired'])
                    ->count();
                
                // Update counters
                NotificationCounter::getCounter($office, 'pending')->setCount($pendingCount);
                NotificationCounter::getCounter($office, 'approved')->setCount($approvedCount);
                NotificationCounter::getCounter($office, 'released')->setCount($releasedCount);
                NotificationCounter::getCounter($office, 'disapproved')->setCount($disapprovedCount);
            }
            
            Log::info("All notification counters recalculated successfully");
        } catch (\Exception $e) {
            Log::error("Failed to recalculate counters: " . $e->getMessage());
        }
    }
}