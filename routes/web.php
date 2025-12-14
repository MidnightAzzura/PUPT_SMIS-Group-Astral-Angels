<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\SupplyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ForgetPassController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\faqController;
use App\Http\Controllers\AdFaqController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\StandaloneDatabaseController;
use App\Http\Controllers\SimpleDatabaseController;
use App\Http\Controllers\NuclearDatabaseController;
use App\Http\Controllers\UserSettingsController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// CSRF Token Refresh Route - Enhanced Fix for 419 Page Expired Errors
Route::get('/refresh-csrf', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'status' => 'success',
        'timestamp' => now()->timestamp
    ]);
})->name('refresh.csrf');

// Additional session check route
Route::get('/session-check', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId()
    ]);
})->middleware('web');

// Password Reset Routes
Route::get('/forgetpass', [ForgetPassController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgetpass', [ForgetPassController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [ForgetPassController::class, 'showResetForm'])->name('email.reset');
Route::post('reset-password', [ForgetPassController::class, 'store'])->name('password.update');

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // User Dashboard
    Route::get('/dashboard', [DashboardController::class, 'use'])->name('dashboard.use');
});

Route::middleware(['auth'])->group(function () {
    // Admin Dashboard
    Route::get('/Admin', [AdminController::class, 'index'])->name('Admin.admin');
});

/*
|--------------------------------------------------------------------------
| User Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // View users (accessible to anyone authenticated)
    Route::get('/usersmanage', [UserController::class, 'index'])->name('usersmanage.index');
    
    // Add user (requires permission)
    Route::post('/usersmanage', [UserController::class, 'store'])
         ->name('usersmanage.store')
         ->middleware('can:add-user');
    
    // Update user (requires permission)
    Route::match(['put', 'patch'], '/usersmanage/{id}', [UserController::class, 'update'])
         ->name('usersmanage.update')
         ->middleware('can:edit-user');
    
    // Delete user (requires permission)
    Route::delete('/users/{id}', [UserController::class, 'destroy'])
         ->name('users.destroy')
         ->middleware('can:delete-user');
         
    // User Settings - Password Change (available to all authenticated users)
    Route::post('/user/change-password', [UserSettingsController::class, 'changePassword'])
         ->name('user.change-password');
         
    // User Settings - Theme Preference (available to all authenticated users)
    Route::post('/user/save-theme', [UserSettingsController::class, 'saveThemePreference'])
         ->name('user.save-theme');
});

// NUCLEAR OPTION - X10000000 Senior Dev ZERO DEPENDENCY Solution
// COMPLETELY OUTSIDE ALL MIDDLEWARE GROUPS
Route::post('/nuclear/backup', [NuclearDatabaseController::class, 'backup'])
     ->name('nuclear.backup');
     
Route::post('/nuclear/restore', [NuclearDatabaseController::class, 'restore'])
     ->name('nuclear.restore');

// Also keep the original routes but fix them
Route::post('/database/backup', [NuclearDatabaseController::class, 'backup'])
     ->name('database.backup');
     
Route::post('/database/restore', [NuclearDatabaseController::class, 'restore'])
     ->name('database.restore');

Route::middleware(['auth'])->group(function () {         
    // Role permissions
    Route::patch('/roles/{role}/permissions', [RolePermissionController::class, 'updatepermission'])
         ->name('roles.permissions.update');
});

/*
|--------------------------------------------------------------------------
| Supply Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/supply', [SupplyController::class, 'index'])->name('admin.supply.index');
    Route::post('/admin/supply/store', [SupplyController::class, 'store'])->name('admin.supply.store');
    Route::put('/admin/supply/{id}', [SupplyController::class, 'update'])->name('admin.supply.update');
    Route::delete('/admin/supply/{id}', [SupplyController::class, 'destroy'])->name('admin.supply.destroy');
    Route::get('/admin/check-stocknumber', [SupplyController::class, 'checkStockNumber'])->name('admin.check.stocknumber');

    Route::middleware('can:Add-Category')->group(function () {
        // Category management
        Route::post('/admin/category/store', [SupplyController::class, 'storeCategory'])
            ->name('admin.category.store');
        Route::delete('/admin/category/{name}', [SupplyController::class, 'destroyCategory'])
            ->name('admin.category.destroy')
            ->middleware('can:Delete-Category');

        // Unit management
        Route::post('/admin/unit/store', [SupplyController::class, 'storeUnit'])
            ->name('admin.unit.store')
            ->middleware('can:Add-Unit');
        Route::delete('/admin/unit/{name}', [SupplyController::class, 'destroyUnit'])
            ->name('admin.unit.destroy')
            ->middleware('can:Delete-Unit');
    });

    // API route for printing all supplies
    Route::get('/api/supplies/all', [SupplyController::class, 'getAllSupplies'])->name('api.supplies.all');

    // Supply history endpoint (AJAX)
    Route::get('admin/supply/{stock}/history', [SupplyController::class, 'history'])
        ->name('admin.supply.history')
        ->middleware('auth'); // ensure auth as needed
});

/*
|--------------------------------------------------------------------------
| Request/Order Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Create request routes
    Route::get('/requests/create', [OrderController::class, 'create'])->name('requests.create');
    Route::post('/requests/store', [RequestController::class, 'store'])->name('requests.store');
    
    // General order routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'handleRequest'])->name('orders.handleRequest');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    
    // Request action routes
    Route::post('/requests/accept{stockNumber}', [RequestController::class, 'accept'])->name('requests.accept');
    Route::post('/requests/deny/{stockNumber}', [RequestController::class, 'deny'])->name('requests.deny');
    Route::post('/requests/{stockNumber}/approve', [RequestController::class, 'approve'])->name('requests.approve');
    
    // New route for denying a group of requests
    Route::post('/requests/deny-group', [RequestController::class, 'denyGroup'])->name('requests.denyGroup');
    
    // Permission-based request viewing
    Route::middleware('can:view-pending')->group(function () {
        Route::get('/requests/pending', [RequestController::class, 'pending'])->name('requests.pending');
    });
    
    Route::middleware('can:view-approved')->group(function () {
        Route::get('/requests/approved', [RequestController::class, 'approved'])->name('requests.approved');
    });
    
    Route::middleware('can:view-released')->group(function () {
        Route::get('/requests/released', [RequestController::class, 'released'])->name('requests.released');
    });
    
    Route::middleware('can:view-cancelled')->group(function () {
        Route::get('/requests/disapproved', [RequestController::class, 'disapproved'])->name('requests.disapproved');
    });

    // RIS-related routes with Edit-Ris permission
Route::middleware('can:Edit-Ris')->group(function () {
    Route::get('/requests/editRIS/{stockNumber}', [RequestController::class, 'editRIS'])->name('requests.editRIS');
    Route::post('/requests/updateRIS/{stockNumber}', [RequestController::class, 'updateRIS'])->name('requests.updateRIS');
});

// Deny permission routes
Route::middleware('can:Deny')->group(function () {
    Route::post('/requests/deny/{stockNumber}', [RequestController::class, 'deny'])->name('requests.deny');
    Route::post('/requests/deny-group', [RequestController::class, 'denyGroup'])->name('requests.denyGroup');
});
    
    // RIS-related routes
    // Route::get('/requests/editRIS/{stockNumber}', [RequestController::class, 'editRIS'])->name('requests.editRIS');
    // Route::post('/requests/updateRIS/{stockNumber}', [RequestController::class, 'updateRIS'])->name('requests.updateRIS');
    Route::get('/requests/{stockNumber}/download-ris', [RequestController::class, 'downloadRIS'])->name('requests.downloadRIS');
    Route::get('/requests/{stockNumber}/print-ris', [RequestController::class, 'printRIS'])->name('requests.printRIS');
    Route::get('/requests/{stockNumber}/mark-as-released', [RequestController::class, 'markAsReleased'])->name('requests.markAsReleased');
    Route::post('/requests/update-status-and-download', [RequestController::class, 'updateStatusAndDownload'])->name('requests.updateStatusAndDownload');
    Route::get('/requests/export-ris', [RequestController::class, 'exportRIS'])->name('requests.exportRIS');
    Route::get('/export-ris', [RequestController::class, 'exportRIS'])->name('exportRIS');
});

/*
|--------------------------------------------------------------------------
| Report Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [ReportController::class, 'Logs'])->name('reports');
    Route::post('/reports/delete-all', [ReportController::class, 'deleteLogs'])->name('reports.deleteLogs');
    Route::post('/reports/delete-filtered', [ReportController::class, 'deleteFiltered'])->name('reports.deleteFiltered');
    Route::get('/reports/export', [ReportController::class, 'exportLogs'])->name('reports.export');
    
    // Archive Management Routes
    Route::get('/reports/archived', [ReportController::class, 'getArchivedRecords'])->name('reports.getArchivedRecords');
    Route::post('/reports/archive-filtered', [ReportController::class, 'archiveFiltered'])->name('reports.archiveFiltered');
    Route::post('/reports/restore-record', [ReportController::class, 'restoreRecord'])->name('reports.restoreRecord');
    Route::post('/reports/restore-multiple', [ReportController::class, 'restoreMultipleRecords'])->name('reports.restoreMultipleRecords');
});

/*
|--------------------------------------------------------------------------
| Help & Support Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/help-support', function () {
        return Auth::user()->role === 'Admin' || Auth::user()->role === 'SuperAdmin' 
            ? view('AdHelp.AdFaq')
            : view('Help.faq');
    })->name('help.support');
});

/*
|--------------------------------------------------------------------------
| API Routes for Real-time Updates
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/api/released-updates', [RequestController::class, 'checkReleasedUpdates'])->name('api.released.updates');
});

/*
|--------------------------------------------------------------------------
| Notification Routes
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationDebugController;

Route::middleware(['auth'])->group(function () {
    // Get notification counts for current user
    Route::get('/api/notifications/counts', [NotificationController::class, 'getCounts'])->name('notifications.counts');
    
    // DEBUG: Compare different notification services
    Route::get('/api/notifications/debug', [NotificationDebugController::class, 'debug'])->name('notifications.debug');
    
    // Clear notifications for a specific page
    Route::post('/api/notifications/clear-page', [NotificationController::class, 'clearPage'])->name('notifications.clear.page');
    
    // Recalculate counters (admin only)    Route::post('/api/notifications/recalculate', [NotificationController::class, 'recalculateCounters'])->name('notifications.recalculate');
    
    // API endpoints for creating notifications directly
    Route::post('/api/notifications/create-approval', [NotificationController::class, 'createApprovalNotification'])->name('notifications.create.approval');
    Route::post('/api/notifications/create-denial', [NotificationController::class, 'createDenialNotification'])->name('notifications.create.denial');

    // View notifications page
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    
    // Mark notifications as read
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark.read');
    
    // Mark all notifications as read
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark.all.read');
    
    // Delete specific notification
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    
    // Get batch details for notification
    Route::get('/notifications/{id}/batch-details', [NotificationController::class, 'getBatchDetails'])->name('notifications.batch.details');
    
    // Test notifications page (for development/testing)
    Route::get('/test-notifications', function() {
        return view('test-notifications');
    })->name('test.notifications');
    
    // Test notification creation
    Route::get('/test-admin-notifications', function() {
        $user = Auth::user();
        
        // Test admin notification counts
        $adminCounts = \App\Services\SidebarNotificationService::getSuperAdminNotificationCounts();
        $allNotifications = \App\Models\Notification::all();
        
        return response()->json([
            'user_role' => $user->role,
            'user_office' => $user->Office ?? 'NO OFFICE',
            'admin_counts' => $adminCounts,
            'total_notifications_in_db' => $allNotifications->count(),
            'sample_notifications' => $allNotifications->take(5)->toArray()
        ]);
    })->name('test.admin.notifications');
    
    Route::post('/test-create-notification', function() {
        $user = Auth::user();
        
        // Create a test notification
        $notification = \App\Models\Notification::create([
            'action' => 'request_approved',
            'user_id' => $user->id,
            'office' => $user->Office,
            'message' => 'Test notification: Your request has been approved.',
            'is_read' => false
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Test notification created',
            'notification' => $notification
        ]);
    })->name('test.notification.create');
});