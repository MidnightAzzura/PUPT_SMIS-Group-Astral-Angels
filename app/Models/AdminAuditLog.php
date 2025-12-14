<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAuditLog extends Model
{
    protected $table = 'admin_audit_logs';
    
    protected $fillable = [
        'admin_id',
        'admin_name',
        'admin_role',
        'action_type',
        'target_type',
        'target_id',
        'target_name',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
        'performed_at'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'performed_at' => 'datetime'
    ];

    protected $dates = [
        'performed_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the admin who performed the action
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('performed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        
        if ($startDate) {
            return $query->where('performed_at', '>=', $startDate . ' 00:00:00');
        }
        
        if ($endDate) {
            return $query->where('performed_at', '<=', $endDate . ' 23:59:59');
        }
        
        return $query;
    }

    /**
     * Scope to filter by action type
     */
    public function scopeActionType($query, $actionType)
    {
        if ($actionType && $actionType !== 'all') {
            return $query->where('action_type', $actionType);
        }
        
        return $query;
    }

    /**
     * Scope to filter by target type
     */
    public function scopeTargetType($query, $targetType)
    {
        if ($targetType && $targetType !== 'all') {
            return $query->where('target_type', $targetType);
        }
        
        return $query;
    }

    /**
     * Scope to filter by admin
     */
    public function scopeByAdmin($query, $adminId)
    {
        if ($adminId && $adminId !== 'all') {
            return $query->where('admin_id', $adminId);
        }
        
        return $query;
    }

    /**
     * Get formatted action type for display
     */
    public function getFormattedActionTypeAttribute()
    {
        $actionTypes = [
            'CREATE' => 'Created',
            'UPDATE' => 'Updated',
            'DELETE' => 'Deleted',
            'APPROVE' => 'Approved',
            'DENY' => 'Denied',
            'RELEASE' => 'Released',
            'RESTORE' => 'Restored',
            'LOGIN' => 'Logged In',
            'LOGOUT' => 'Logged Out'
        ];

        return $actionTypes[$this->action_type] ?? $this->action_type;
    }

    /**
     * Get action color for UI display
     */
    public function getActionColorAttribute()
    {
        $colors = [
            'CREATE' => 'success',
            'UPDATE' => 'warning',
            'DELETE' => 'danger',
            'APPROVE' => 'success',
            'DENY' => 'danger',
            'RELEASE' => 'info',
            'RESTORE' => 'primary',
            'LOGIN' => 'info',
            'LOGOUT' => 'secondary'
        ];

        return $colors[$this->action_type] ?? 'secondary';
    }

    /**
     * Get differences between old and new values
     */
    public function getChangesAttribute()
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        $oldValues = is_array($this->old_values) ? $this->old_values : [];
        $newValues = is_array($this->new_values) ? $this->new_values : [];

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }
}
