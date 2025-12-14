<?php

namespace App\Providers;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            if ($user->role === 'SuperAdmin') {
                return true; // SuperAdmin can do anything
            }
        });

Gate::define('view-pending', function ($user) {
    try {
        if (!$user->hasPermissionTo('view-pending')) {
            throw new AuthorizationException('You do not have permission to view pending items.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('You dont have the permission to access "view-pending".');
    }
    return true ;
});

Gate::define('view-approved', function ($user) {
    try {
        if (!$user->hasPermissionTo('view-approved')) {
            throw new AuthorizationException('You do not have permission to view approved items.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('You dont have the permission to access  "view-approved".');
    }
    return true;
});

Gate::define('view-released', function ($user) {
    try {
        if (!$user->hasPermissionTo('view-released')) {
            throw new AuthorizationException('You do not have permission to view released items.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('You dont have the permission to access  "view-released".');
    }
    return true;
});

Gate::define('view-cancelled', function ($user) {
    try {
        if (!$user->hasPermissionTo('view-cancelled')) {
            throw new AuthorizationException('You do not have permission to view cancelled items.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('You dont have the permission to access  "view cancelled".');
    }
    return true;
});

Gate::define('add user', function ($user) {
    try {
        if (! $user->hasPermissionTo('add user')) {
            throw new AuthorizationException('You do not have permission to add users.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('Permission "add user" does not exist.');
    }
    return true;
});

Gate::define('edit user', function ($user) {
    try {
        if (! $user->hasPermissionTo('edit user')) {
            throw new AuthorizationException('You do not have permission to edit users.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('Permission "edit user" does not exist.');
    }
    return true;
});

Gate::define('delete user', function ($user) {
    try {
        if (! $user->hasPermissionTo('delete user')) {
            throw new AuthorizationException('You do not have permission to delete users.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('Permission "delete user" does not exist.');
    }
    return true;
});

Gate::define('Add-Supply', function ($user) {
    try {
        if (! $user->hasPermissionTo('Add-Supply')) {
            throw new AuthorizationException('You do not have permission to Add-Supply.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('Permission "Add-Supply" does not exist.');
    }
    return true;
});


Gate::define('Edit-Supply', function ($user) {
    try {
        if (! $user->hasPermissionTo('Edit-Supply')) {
            throw new AuthorizationException('You do not have permission to Edit-Supply.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('Permission "Edit-Supply" does not exist.');
    }
    return true;
});

Gate::define('Delete-Supply', function ($user) {
    try {
        if (! $user->hasPermissionTo('Delete-Supply')) {
            throw new AuthorizationException('You do not have permission to Delete-Supply.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('Permission "Delete-Supply" does not exist.');
    }
    return true;
});

Gate::define('Edit-Ris', function ($user) {
    try {
        if (! $user->hasPermissionTo('Edit-Ris')) {
            throw new AuthorizationException('You do not have permission to Edit-Ris.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('Permission "Edit-Ris" does not exist.');
    }
    return true;
});


Gate::define('Deny', function ($user) {
    try {
        if (! $user->hasPermissionTo('Deny')) {
            throw new AuthorizationException('You do not have permission to Deny.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('Permission "Deny" does not exist.');
    }
    return true;
});

Gate::define('Add-Category', function ($user) {
    try {
        if (! $user->hasPermissionTo('Add-Category')) {
            throw new AuthorizationException('You do not have permission to Add Category.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('Permission "Add Category" does not exist.');
    }
    return true;
});

Gate::define('Delete-Category', function ($user) {
    try {
        if (! $user->hasPermissionTo('Delete-Category')) {
            throw new AuthorizationException('You do not have permission to Delete Category.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('Permission "Delete Category" does not exist.');
    }
    return true;
});

Gate::define('Add-Unit', function ($user) {
    try {
        if (! $user->hasPermissionTo('Add-Unit')) {
            throw new AuthorizationException('You do not have permission to Add Unit.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('Permission "Add Unit" does not exist.');
    }
    return true;
});

Gate::define('Delete-Unit', function ($user) {
    try {
        if (! $user->hasPermissionTo('Delete-Unit')) {
            throw new AuthorizationException('You do not have permission to Delete Unit.');
        }
    } catch (PermissionDoesNotExist $e) {
        throw new AuthorizationException('Permission "Delete Unit" does not exist.');
    }
    return true;
});

    }
}
