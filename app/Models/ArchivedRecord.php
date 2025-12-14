<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchivedRecord extends Model
{
    protected $fillable = [
        'record_type',
        'original_id',
        'record_data',
        'reference',
        'description',
        'archived_by',
        'archived_at'
    ];

    protected $casts = [
        'record_data' => 'array',
        'archived_at' => 'datetime'
    ];

    /**
     * Get the user who archived this record
     */
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Scope for filtering by record type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('record_type', $type);
    }

    /**
     * Scope for recent archives
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('archived_at', '>=', now()->subDays($days));
    }
}
