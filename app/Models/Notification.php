<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SupplyRequest;
use App\Models\User;
use App\Models\Requests;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';
    
    protected $fillable = [
        'user_id',
        'admin_id',
        'target_user_id',
        'request_id',
        'office',
        'action',
        'message',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'read_at' => 'datetime'
    ];

    /**
     * Get the user that owns the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin that created the notification
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the target user of the notification
     */
    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * Get the related request
     */
    public function request()
    {
        return $this->belongsTo(Requests::class, 'request_id');
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for office notifications
     */
    public function scopeForOffice($query, $office)
    {
        return $query->where('office', $office)->orWhere('office', null);
    }

    /**
     * Scope for user notifications
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Get all notifications with optional filtering
     *
     * @param array $filters Optional filters: office, user_id, is_read, action
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getNotifications($filters = [])
    {
        $query = self::query();
        
        if (isset($filters['office'])) {
            $query->forOffice($filters['office']);
        }
        
        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }
        
        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }
        
        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get a paginated list of notifications
     *
     * @param array $filters Optional filters: office, user_id, is_read, action
     * @param int $perPage Number of items per page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedNotifications($filters = [], $perPage = 15)
    {
        $query = self::query();
        
        if (isset($filters['office'])) {
            $query->forOffice($filters['office']);
        }
        
        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }
        
        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }
        
        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get notifications count with filters
     *
     * @param array $filters Optional filters: office, user_id, is_read, action
     * @return int
     */
    public static function getNotificationsCount($filters = [])
    {
        $query = self::query();
        
        if (isset($filters['office'])) {
            $query->forOffice($filters['office']);
        }
        
        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }
        
        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }
        
        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        
        return $query->count();
    }

    /**
     * Get notifications by action type
     *
     * @param string $actionType The action type to filter by (e.g., 'request_approved', 'request_denied')
     * @param string|null $office Optional office filter
     * @param int $perPage Number of items per page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function getNotificationsByAction($actionType, $office = null, $perPage = 15)
    {
        $query = self::query();
        
        // Filter by action type
        $query->where('action', $actionType);
        
        // Filter by office if specified
        if ($office) {
            $query->forOffice($office);
        }
        
        // Log the query for debugging
        \Log::info('Getting notifications by action', [
            'action' => $actionType,
            'office' => $office,
            'perPage' => $perPage
        ]);
        
        // Execute and return paginated results
        $results = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Log the results
        \Log::info('Found notifications', [
            'count' => $results->count(),
            'total' => $results->total(),
            'current_page' => $results->currentPage()
        ]);
        
        return $results;
    }
}
