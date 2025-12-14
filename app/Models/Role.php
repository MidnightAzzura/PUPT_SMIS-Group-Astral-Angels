<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App Role Model - Extends Spatie Role for compatibility
 * This ensures the 'role' binding always resolves correctly
 */
class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
    ];

    /**
     * Get users with this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(config('auth.providers.users.model', \App\Models\User::class));
    }
}
