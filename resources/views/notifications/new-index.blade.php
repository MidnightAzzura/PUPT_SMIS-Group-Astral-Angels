@extends('layout.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
<link rel="stylesheet" href="{{ asset('css/notification-badges.css') }}">
<link rel="stylesheet" href="{{ asset('css/collapsed-sidebar-badges.css') }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode-switch.css') }}">
<link rel="stylesheet" href="{{ asset('css/user-settings-dropdown.css') }}">
<script src="{{ asset('js/enhanced-notifications.js') }}" defer></script>
<script src="{{ asset('js/notification-debug.js') }}" defer></script>
<script src="{{ asset('js/notification-fix.js') }}"></script>
<script src="{{ asset('js/notification-direct-fix.js') }}"></script>
<!-- Debug help for notification buttons -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Notification debug helper active');
    // Count buttons of each type
    const checkButtons = document.querySelectorAll('.notification-check-btn, .check_notif, .mark-read-btn, [onclick*="markRead"]').length;
    const deleteButtons = document.querySelectorAll('.notification-delete-btn, .delete_notif, .delete-btn, [onclick*="deleteNotif"]').length;
    console.log(`Found ${checkButtons} check buttons and ${deleteButtons} delete buttons`);
    
    // Log CSRF token availability
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    console.log('CSRF Token available:', csrfToken ? 'Yes' : 'No');
    
    // Monitor click events on notification items
    document.addEventListener('click', function(e) {
        const notificationItem = e.target.closest('.notification-card, .notification-item, [data-notification-id]');
        if (notificationItem) {
            console.log('Click on notification item:', {
                element: e.target,
                containsButton: e.target.closest('button, .btn, a') ? true : false,
                notificationId: notificationItem.dataset.notificationId || notificationItem.dataset.id
            });
        }
    }, true);
});
</script>

    <button class="toggle-sidebar-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <img src="{{ asset('Pup/puplogo.png') }}" style="max-width: 40%; height: auto; padding: 10px; margin: 20px auto; display: block;" alt="PUP Logo">
            <h3 style="margin-bottom: 20px;">SMIS</h3>
            
            <a href="{{ route(
                strtolower(trim(Auth::user()->role)) === 'superadmin'
                    ? 'Admin.admin'
                    : (strtolower(trim(Auth::user()->role)) === 'admin'
                        ? 'Admin.admin'
                        : 'dashboard.use')
            ) }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                <i class="fas fa-home" style="margin-right: 8px;"></i>
                <span>Home</span>
            </a>
            
            @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'SuperAdmin')
            <a href="{{ route('usersmanage.index') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                <i class="fas fa-users" style="margin-right: 8px;"></i> <span>User Management</span>
            </a>
            @endif
            
            <a href="{{ route('notifications.index') }}" style="display: flex; align-items: center; margin-bottom: 10px; background-color: rgba(255, 255, 255, 0.1);">
                <i class="fas fa-bell" style="margin-right: 8px;"></i> 
                <span>Notifications</span>
                @if(isset($notificationCounts) && $notificationCounts['total_unread'] > 0)
                    <span class="notification-badge">{{ $notificationCounts['total_unread'] }}</span>
                @endif
            </a>
            
            <a href="#" class="dropdown-btn" style="display: flex; align-items: center; margin-bottom: 10px;">
                <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
                <i class="fa fa-caret-down" style="margin-left: 8px;"></i>
            </a>
            
            <div class="dropdown-container" style="margin: 10px auto; text-align: center;">
                @if(Auth::user()->role === 'User')
                    <a href="{{ route('requests.create') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                        <i class="fas fa-plus" style="margin-right: 8px;"></i> <span>Create New Request</span>
                    </a>
                @endif
                <a href="{{ route('requests.pending') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                    <i class="fas fa-clock" style="margin-right: 8px;"></i> <span>Pending</span>
                </a>
                <a href="{{ route('requests.approved') }}" style="display: flex; align-items: center; margin-bottom: 10px;" onclick="clearPageNotifications('approved')">
                    <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> 
                    <span>Approved</span>
                </a>
                <a href="{{ route('requests.released') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                    <i class="fas fa-truck" style="margin-right: 8px;"></i> <span>Released</span>
                </a>
                <a href="{{ route('requests.disapproved') }}" style="display: flex; align-items: center; margin-bottom: 10px;" onclick="clearPageNotifications('disapproved')">
                    <i class="fas fa-times-circle" style="margin-right: 8px;"></i> 
                    <span>Disapproved</span>
                </a>
            </div>
            
            <a href="{{ route('reports') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                <i class="fas fa-chart-line" style="margin-right: 8px;"></i> <span>Reports</span>
            </a>
            <a href="{{ route('help.support') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                <i class="fas fa-question-circle" style="margin-right: 8px;"></i> <span>Help & Support</span>
            </a>
        </div>
    </div>
    
    <div class="main-content p-4">
        <!-- Welcome Header Section -->
        <div class="dashboard-header modern-header">
            <div class="header-content">
                <h2 class="welcome-text">
                    @if(Auth::user()->role == 'SuperAdmin')
                        <i class="fas fa-crown text-warning me-2"></i>Welcome SuperAdmin,
                        <span class="user-name">{{ Auth::user()->First_Name }} {{ Auth::user()->Middle_Initial }}. {{ Auth::user()->Last_Name }}!</span>
                    @else
                        <i class="fas fa-user-shield me-2"></i>Welcome {{ ucfirst(Auth::user()->role) }},
                        <span class="user-name">{{ Auth::user()->First_Name }} {{ Auth::user()->Middle_Initial }}. {{ Auth::user()->Last_Name }}!</span>
                    @endif
                </h2>
                <p class="header-subtitle">Your notifications center</p>
            </div>
        </div>
        
        <div class="header">
            <h2><i class="fas fa-bell me-2"></i>Notifications</h2>
            <div id="currentDateTime" class="text-center mb-3"></div>
        </div>
        
        <!-- User Settings Dropdown -->
        <div class="user-settings-dropdown" style="position: fixed !important; top: 15px !important; right: 15px !important; z-index: 99999 !important;">
            <button class="btn btn-sm btn-dark rounded-circle" id="userSettingsButton" style="width: 40px !important; height: 40px !important; background-color: #343a40 !important; border: 2px solid #fff !important; box-shadow: 0 4px 8px rgba(0,0,0,0.3) !important;" aria-expanded="false" aria-label="User settings and profile" title="Click for user settings">
                <i class="fas fa-user" style="color: #fff !important;"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end manual-dropdown" id="userSettingsDropdownMenu">
                <h6 class="dropdown-header">Settings</h6>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#userSettingsModal">
                    <i class="fas fa-user-cog me-2"></i> User Settings
                </a>
                <div class="dropdown-divider"></div>
                <div class="dropdown-item d-flex align-items-center justify-content-between" id="darkModeToggle">
                    <span class="d-flex align-items-center">
                        <i class="fas fa-moon me-2"></i>
                        <span class="mode-text">Dark Mode</span>
                    </span>
                    <label class="dark-mode-switch mb-0">
                        <input type="checkbox" id="darkModeSwitch">
                        <span class="dark-mode-slider"></span>
                    </label>
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
                <script src="{{ asset('js/theme-toggle.js') }}"></script>
            </div>
        </div>
        
        <!-- Logout Form (hidden) -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const userButton = document.getElementById('userSettingsButton');
                const userMenu = document.getElementById('userSettingsDropdownMenu');
                
                if (userButton && userMenu) {
                    // Direct click handler that ensures the dropdown appears
                    userButton.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log("User button clicked");
                        
                        // Visual feedback
                        this.classList.add('btn-clicked');
                        setTimeout(() => this.classList.remove('btn-clicked'), 300);
                        
                        // Toggle dropdown visibility with inline style for maximum compatibility
                        if (userMenu.style.display === 'block') {
                            userMenu.style.display = 'none';
                            userMenu.classList.remove('show');
                        } else {
                            userMenu.style.display = 'block';
                            userMenu.classList.add('show');
                        }
                    };
                    
                    // Close dropdown when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!userButton.contains(e.target) && !userMenu.contains(e.target)) {
                            userMenu.style.display = 'none';
                            userMenu.classList.remove('show');
                        }
                    });
                    
                    // Prevent dropdown from closing when clicking inside it
                    userMenu.addEventListener('click', function(e) {
                        // Allow clicks on links and buttons to work, but prevent the dropdown from closing
                        if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON' && !e.target.closest('a') && !e.target.closest('button')) {
                            e.stopPropagation();
                        }
                    });
                }
            });
        </script>
        
        <div class="container mt-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Notification Actions -->
            <div class="notification-actions mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <h5>
                            @if(Auth::user()->role === 'SuperAdmin' || Auth::user()->role === 'Admin')
                                SuperAdmin Notifications
                            @else
                                {{ Auth::user()->Office }} Notifications
                            @endif
                            @if($filter != 'all')
                                <span class="badge {{ $filter == 'approved' ? 'bg-success' : ($filter == 'denied' ? 'bg-danger' : 'bg-primary') }}">
                                    {{ ucfirst($filter) }}
                                </span>
                            @endif
                        </h5>
                        <p class="text-muted">
                            @if(Auth::user()->role === 'SuperAdmin' || Auth::user()->role === 'Admin')
                                Total: {{ $notifications->count() }} notifications{{ $filter != 'all' ? ' matching filter' : '' }} for SuperAdmin
                            @else
                                Total: {{ $notifications->count() }} notifications{{ $filter != 'all' ? ' matching filter' : '' }}
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i> Mark All as Read
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="refreshNotifications()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
            
            @if(Auth::user()->role === 'SuperAdmin')
            <!-- Office Filter Section for SuperAdmin -->
            <div class="filter-section mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-filter"></i> Advanced Filters</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('notifications.index') }}" method="get" class="filter-form">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="office_filter">Office</label>
                                    <select name="office" id="office_filter" class="form-select">
                                        <option value="all" {{ request('office', 'all') == 'all' ? 'selected' : '' }}>All Offices</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office }}" {{ request('office') == $office ? 'selected' : '' }}>{{ $office }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="date_from">From Date</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_to">To Date</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-3">
                                    <label>&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                        <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-undo"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="filter" value="{{ $filter }}">
                        </form>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Filter Tabs -->
            <div class="notification-filters mb-3">
                <ul class="nav nav-tabs" id="notificationTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link {{ $filter == 'all' ? 'active' : '' }}" id="all-tab" data-filter="all">
                            <i class="fas fa-list-ul"></i> All
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link {{ $filter == 'unread' ? 'active' : '' }}" id="unread-tab" data-filter="unread">
                            <i class="fas fa-envelope"></i> Unread
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link {{ $filter == 'approved' ? 'active' : '' }}" id="approved-tab" data-filter="approved">
                            <i class="fas fa-check-circle text-success"></i> Approved
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link {{ $filter == 'denied' ? 'active' : '' }}" id="denied-tab" data-filter="denied">
                            <i class="fas fa-times-circle text-danger"></i> Denied
                        </button>
                    </li>
                </ul>
            </div>
            
            <!-- Modern Notification System -->
            <div class="modern-notifications-container">
                @php
                    // Create a set to track seen messages to prevent duplicates
                    $seenMessages = [];
                @endphp
                
                @forelse($notifications as $notification)
                    @php
                        // Skip notifications with empty or invalid messages
                        if (empty(trim($notification->message)) || 
                            ($notification->action === 'notification' && empty(trim($notification->message)))) {
                            continue;
                        }
                        
                        // Create a signature for this notification based on content and time
                        $notifSignature = md5($notification->message . $notification->created_at->format('Y-m-d H:i') . $notification->action);
                        
                        // Skip if we've seen this exact notification already
                        if (in_array($notifSignature, $seenMessages)) {
                            continue;
                        }
                        $seenMessages[] = $notifSignature;
                    @endphp
                    
                    <div class="notification-card {{ $notification->is_read ? 'read' : 'unread' }}" 
                         data-notification-id="{{ $notification->notification_id }}" 
                         data-action="{{ $notification->action }}">
                        
                        <div class="notification-icon">
                            @if($notification->action === 'request_approved')
                                <i class="fas fa-check-circle"></i>
                            @elseif($notification->action === 'request_denied')
                                <i class="fas fa-times-circle"></i>
                            @else
                                <i class="fas fa-bell"></i>
                            @endif
                        </div>
                        
                        <div class="notification-content">
                            <div class="notification-header">
                                <h5 class="notification-title">
                                    @if($notification->action === 'request_approved')
                                        Request Approved
                                    @elseif($notification->action === 'request_denied')
                                        Request Denied
                                    @else
                                        Notification
                                    @endif
                                </h5>
                                
                                <div class="notification-meta">
                                    <span class="notification-time" title="{{ $notification->created_at }}">
                                        {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                    </span>
                                    
                                    @if(!$notification->is_read)
                                        <span class="unread-indicator" title="Unread"></span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="notification-message">
                                {{ $notification->message }}
                            </div>
                            
                            <div class="notification-footer">
                                <span class="office-badge">{{ $notification->office }}</span>
                                
                                <div class="notification-actions">
                                    @if(!$notification->is_read)
                                        <button class="btn btn-sm btn-outline-primary mark-read-btn" 
                                                onclick="markAsRead('{{ $notification->notification_id }}'); return false;" 
                                                title="Mark as read">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    
                                    <button class="btn btn-sm btn-outline-danger delete-btn" 
                                            onclick="deleteNotification('{{ $notification->notification_id }}'); return false;" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <h4>No notifications found</h4>
                        <p>You'll see your notifications here when there are updates on your requests.</p>
                        
                        @if($filter != 'all')
                            <a href="{{ route('notifications.index') }}" class="btn btn-outline-primary mt-2">
                                <i class="fas fa-list"></i> View All Notifications
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>
            
            <!-- Simple Pagination -->
            @if($notifications instanceof \Illuminate\Pagination\LengthAwarePaginator && $notifications->hasPages())
            <div class="pagination-container mt-4">
                <nav aria-label="Notification pagination" class="d-flex justify-content-center">
                    <div class="pagination-wrapper">
                        @if($notifications->onFirstPage())
                            <button class="btn btn-outline-secondary pagination-btn" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                        @else
                            <a href="{{ $notifications->previousPageUrl() }}" class="btn btn-primary pagination-btn">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        @endif
                        
                        <span class="pagination-info">
                            Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}
                        </span>
                        
                        @if($notifications->hasMorePages())
                            <a href="{{ $notifications->nextPageUrl() }}" class="btn btn-primary pagination-btn">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <button class="btn btn-outline-secondary pagination-btn" disabled>
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                </nav>
            </div>
            @elseif(isset($notifications) && method_exists($notifications, 'total') && $notifications->total() > 10)
            <!-- Fallback pagination for debugging -->
            <div class="pagination-container mt-4">
                <nav aria-label="Notification pagination" class="d-flex justify-content-center">
                    <div class="pagination-wrapper">
                        <span class="pagination-info">
                            Showing {{ $notifications->count() }} of {{ $notifications->total() }} notifications
                        </span>
                    </div>
                </nav>
            </div>
            @endif
            
            <!-- Real-time Notification Alert -->
            <div id="realtime-notification" class="realtime-notification hidden">
                <div class="realtime-notification-content">
                    <div class="realtime-notification-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="realtime-notification-message">New notification received</div>
                </div>
                <button class="realtime-notification-close" onclick="closeRealTimeNotification()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 0;">
                <div class="modal-header" style="background-color: #800000; color: white; border-radius: 0;">
                    <h5 class="modal-title" id="logoutModalLabel">
                        <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="mb-0">Are you sure you want to logout from the system?</p>
                </div>
                <div class="modal-footer justify-content-center" style="border-top: none;">
                    <button type="button" class="btn" style="background-color: #6c757d; color: white; border-radius: 0; min-width: 120px; text-transform: uppercase; font-weight: 500;" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn" style="background-color: #dc3545; color: white; border-radius: 0; min-width: 120px; text-transform: uppercase; font-weight: 500;" onclick="confirmLogout()">
                        Confirm Logout
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mark All as Read Modal -->
    <div class="modal fade" id="markAllReadModal" tabindex="-1" aria-labelledby="markAllReadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #800000; color: white;">
                    <h5 class="modal-title" id="markAllReadModalLabel">
                        <i class="fas fa-check-double me-2"></i>Mark All as Read
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to mark all notifications as read?</p>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn" style="background-color: #800000; color: white;" onclick="confirmMarkAllRead()">
                        <i class="fas fa-check me-1"></i>Yes, Mark All as Read
                    </button>
                </div>
            </div>
        </div>
    </div>

<style>
/* Modern Notification System Styles */
.modern-notifications-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 30px;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
}

.notification-card {
    display: flex;
    background-color: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    padding: 16px;
}

.notification-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.notification-card.unread {
    border-left: 4px solid #007bff;
    background-color: #f8fbff;
}

.notification-card.read {
    border-left: 4px solid #e9ecef;
    opacity: 0.85;
}

.notification-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 15px;
    flex-shrink: 0;
}

.notification-card.unread .notification-icon {
    background-color: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

.notification-card.read .notification-icon {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}

.notification-card[data-action="request_approved"] .notification-icon {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.notification-card[data-action="request_denied"] .notification-icon {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.notification-icon i {
    font-size: 1.5rem;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    position: relative;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.notification-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 600;
    color: #1a1d20;
    letter-spacing: -0.01em;
    transition: color 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.notification-card:hover .notification-title {
    color: #0d6efd;
}

.notification-meta {
    display: flex;
    align-items: center;
    position: relative;
}

.notification-time {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
    background: rgba(0,0,0,0.03);
    padding: 3px 8px;
    border-radius: 12px;
    transition: background 0.2s ease;
}

.notification-card:hover .notification-time {
    background: rgba(13,110,253,0.07);
}

.unread-indicator {
    width: 10px;
    height: 10px;
    background-color: #0d6efd;
    border-radius: 50%;
    margin-left: 10px;
    display: inline-block;
    position: relative;
    animation: pulse 2s infinite;
    box-shadow: 0 0 0 rgba(13,110,253,0.4);
}

.notification-card[data-action="request_approved"] .unread-indicator {
    background-color: #198754;
    animation: pulse-success 2s infinite;
    box-shadow: 0 0 0 rgba(25,135,84,0.4);
}

.notification-card[data-action="request_denied"] .unread-indicator {
    background-color: #dc3545;
    animation: pulse-danger 2s infinite;
    box-shadow: 0 0 0 rgba(220,53,69,0.4);
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(13,110,253,0.4);
    }
    70% {
        box-shadow: 0 0 0 6px rgba(13,110,253,0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(13,110,253,0);
    }
}

@keyframes pulse-success {
    0% {
        box-shadow: 0 0 0 0 rgba(25,135,84,0.4);
    }
    70% {
        box-shadow: 0 0 0 6px rgba(25,135,84,0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(25,135,84,0);
    }
}

@keyframes pulse-danger {
    0% {
        box-shadow: 0 0 0 0 rgba(220,53,69,0.4);
    }
    70% {
        box-shadow: 0 0 0 6px rgba(220,53,69,0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(220,53,69,0);
    }
}

/* Real-time Notification Alert */
.realtime-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
    padding: 15px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 320px;
    z-index: 1050;
    transform: translateX(400px);
    opacity: 0;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.realtime-notification.show {
    transform: translateX(0);
    opacity: 1;
}

.realtime-notification.hidden {
    display: none;
}

.realtime-notification-content {
    display: flex;
    align-items: center;
    gap: 15px;
}

.realtime-notification-icon {
    width: 40px;
    height: 40px;
    background-color: rgba(0, 123, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #007bff;
    font-size: 1.2rem;
}

.realtime-notification-message {
    font-weight: 600;
    color: #343a40;
}

.realtime-notification-close {
    background: none;
    border: none;
    color: #adb5bd;
    cursor: pointer;
    padding: 5px;
    font-size: 0.8rem;
    transition: color 0.2s ease;
}

.realtime-notification-close:hover {
    color: #343a40;
}

/* Animations for new notifications */
@keyframes slideIn {
    0% {
        transform: translateX(-10px);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

.notification-new {
    animation: slideIn 0.3s ease forwards;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .notification-card {
        flex-direction: column;
    }
    
    .notification-icon {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .notification-header {
        flex-direction: column;
    }
    
    .notification-meta {
        margin-top: 5px;
    }
    
    .notification-footer {
        flex-direction: column;
        gap: 10px;
    }
    
    .notification-actions {
        width: 100%;
        justify-content: flex-end;
    }
    
    .realtime-notification {
        width: calc(100% - 40px);
    }
}

/* Pagination Styles */
.pagination-container {
    margin-top: 20px;
    margin-bottom: 20px;
}

.pagination-wrapper {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
}

.pagination-btn {
    min-width: 120px;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
    text-decoration: none;
    border: 2px solid transparent;
}

.pagination-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
}

.pagination-btn.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.pagination-btn.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.pagination-btn.btn-outline-secondary {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #6c757d;
    cursor: not-allowed;
}

.pagination-info {
    font-weight: 600;
    color: #495057;
    padding: 10px 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    min-width: 140px;
    text-align: center;
}

@media (max-width: 768px) {
    .pagination-wrapper {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .pagination-btn {
        width: 100%;
        min-width: auto;
    }
    
    .pagination-info {
        width: 100%;
        min-width: auto;
    }
}

@media (max-width: 480px) {
    .pagination-wrapper {
        padding: 15px;
    }
    
    .pagination-btn {
        padding: 8px 16px;
        font-size: 0.9rem;
    }
    
    .pagination-info {
        font-size: 0.9rem;
        padding: 8px 16px;
    }
}
</style>

<script>
    // Display current date and time
    function updateDateTime() {
        const now = new Date();
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        };
        document.getElementById('currentDateTime').textContent = now.toLocaleDateString('en-US', options);
    }

    // Update time every second
    setInterval(updateDateTime, 1000);
    updateDateTime(); // Initial call

    // Sidebar toggle functionality
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
    }
    
    // Dropdown initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Handle all dropdown buttons
        document.querySelectorAll('.dropdown-btn').forEach(function(btn, index) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                
                // Get the next sibling element (the dropdown container)
                const dropdownContainer = this.nextElementSibling;
                
                // Toggle the display of the dropdown container
                if (dropdownContainer.style.display === 'block') {
                    dropdownContainer.style.display = 'none';
                    localStorage.setItem('dropdownState-' + index, 'closed');
                } else {
                    dropdownContainer.style.display = 'block';
                    localStorage.setItem('dropdownState-' + index, 'open');
                }
            });
            
            // Restore dropdown state from localStorage
            const dropdownContainer = btn.nextElementSibling;
            const dropdownState = localStorage.getItem('dropdownState-' + index);
            
            if (dropdownState === 'open') {
                btn.classList.add('active');
                dropdownContainer.style.display = 'block';
            } else {
                dropdownContainer.style.display = 'none';
            }
        });
        
        // Initialize notification tabs
        initializeNotificationTabs();
        
        // Update notification counts
        updateNotificationCounts();
        
        // Check URL parameters for action completion
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('action_completed')) {
            const actionType = urlParams.get('action_completed');
            if (actionType === 'approve') {
                showTableAlert('Request approved successfully! Notification has been sent.', 'success');
            } else if (actionType === 'deny') {
                showTableAlert('Request denied successfully! Notification has been sent.', 'success');
            }
        }
    });
    
    // Initialize notification tabs
    function initializeNotificationTabs() {
        const tabs = document.querySelectorAll('#notificationTabs button');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs and add to current
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Get filter value from data attribute
                const filter = this.dataset.filter;
                
                // Redirect to filtered view
                window.location.href = `{{ route('notifications.index') }}?filter=${filter}`;
            });
        });
    }
    
    // Mark notification as read
    function markAsRead(notificationId) {
        console.log('Marking notification as read:', notificationId);
        
        // Show loading indicator or disable button if needed
        const button = document.querySelector(`[data-notification-id="${notificationId}"] .mark-read-btn`);
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }
        
        fetch('{{ route("notifications.mark.read") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                notification_ids: [notificationId]
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Mark as read response:', data);
            
            if (data.success) {
                // Update UI
                const notificationCard = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationCard) {
                    notificationCard.classList.remove('unread');
                    notificationCard.classList.add('read');
                    
                    // Remove unread indicator
                    const unreadIndicator = notificationCard.querySelector('.unread-indicator');
                    if (unreadIndicator) unreadIndicator.remove();
                    
                    // Remove mark as read button
                    const markReadBtn = notificationCard.querySelector('.mark-read-btn');
                    if (markReadBtn) markReadBtn.remove();
                }
                
                // Update notification counts
                updateNotificationCounts();
                
                // Show success message
                showTableAlert('Notification marked as read', 'success');
            } else {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-check"></i>';
                }
                showTableAlert('Error marking notification as read: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error marking as read:', error);
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-check"></i>';
            }
            showTableAlert('Error marking notification as read', 'error');
        });
    }
    
    // Mark all notifications as read
    function markAllAsRead() {
        // Update modal text based on selected office (for SuperAdmin)
        const officeFilter = document.getElementById('office_filter');
        const modalBody = document.querySelector('#markAllReadModal .modal-body');
        
        if (officeFilter) {
            const selectedOffice = officeFilter.value;
            const selectedOfficeText = officeFilter.options[officeFilter.selectedIndex].text;
            
            if (selectedOffice === 'all') {
                modalBody.innerHTML = `
                    <p>Are you sure you want to mark all notifications as read for <strong>ALL OFFICES</strong>?</p>
                    <p class="text-muted small">This action cannot be undone and will affect all offices system-wide.</p>
                `;
            } else {
                modalBody.innerHTML = `
                    <p>Are you sure you want to mark all notifications as read for <strong>${selectedOfficeText}</strong>?</p>
                    <p class="text-muted small">This action cannot be undone and will only affect notifications for this office.</p>
                `;
            }
        } else {
            // Regular user - show their office
            modalBody.innerHTML = `
                <p>Are you sure you want to mark all notifications as read?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            `;
        }
        
        // Show the confirmation modal instead of using browser confirm
        const modal = new bootstrap.Modal(document.getElementById('markAllReadModal'));
        modal.show();
    }
    
    // Confirm mark all as read action
    function confirmMarkAllRead() {
        // Hide the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('markAllReadModal'));
        modal.hide();
        
        // Show loading indicator
        showTableAlert('Processing...', 'info');
        
        // Get the current office filter value for SuperAdmin
        const officeFilter = document.getElementById('office_filter');
        const selectedOffice = officeFilter ? officeFilter.value : 'all';
        
        // Prepare request body with office filter
        const requestBody = {};
        if (selectedOffice && selectedOffice !== 'all') {
            requestBody.office = selectedOffice;
        }
        
        fetch('{{ route("notifications.mark.all.read") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestBody)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Mark all as read response:', data);
            
            if (data.success) {
                // Update all notification cards in the UI immediately
                document.querySelectorAll('.notification-card.unread').forEach(card => {
                    card.classList.remove('unread');
                    card.classList.add('read');
                    
                    // Remove unread indicator
                    const unreadIndicator = card.querySelector('.unread-indicator');
                    if (unreadIndicator) unreadIndicator.remove();
                    
                    // Remove mark as read button
                    const markReadBtn = card.querySelector('.mark-read-btn');
                    if (markReadBtn) markReadBtn.remove();
                });
                
                // Update notification counts
                updateNotificationCounts();
                
                // Show success message
                showTableAlert(`${data.message || 'All notifications marked as read'}`, 'success');
                
                // Auto-refresh disabled per user request
                // Force a page refresh after a short delay to ensure persistence
                // setTimeout(() => {
                //     console.log('Refreshing page to ensure persistence...');
                //     window.location.reload();
                // }, 2000);
                
                console.log('✅ All notifications marked as read - no auto-refresh');
                
            } else {
                showTableAlert(data.message || 'Error marking all notifications as read', 'error');
            }
        })
        .catch(error => {
            console.error('Error marking all as read:', error);
            showTableAlert('Error marking all notifications as read: ' + error.message, 'error');
        });
    }
    
    // Delete notification
    function deleteNotification(notificationId) {
        console.log('🗑️ Delete notification called for ID:', notificationId);
        
        if (!notificationId) {
            console.error('❌ No notification ID provided');
            alert('Error: No notification ID provided');
            return;
        }
        
        if (!confirm('Are you sure you want to delete this notification?')) {
            console.log('User cancelled deletion');
            return;
        }
        
        // Find and update button
        const button = document.querySelector(`[data-notification-id="${notificationId}"] .delete-btn`);
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.error('❌ CSRF token not found');
            alert('Error: Security token not found. Please refresh the page.');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-trash"></i>';
            }
            return;
        }
        
        console.log('📡 Sending delete request for notification ID:', notificationId);
        
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('📡 Delete response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('✅ Delete response data:', data);
            
            if (data.success) {
                // Remove the notification card from DOM with animation
                const card = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (card) {
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(-100%)';
                    
                    setTimeout(() => {
                        card.remove();
                        
                        // Check if no notifications left
                        if (document.querySelectorAll('.notification-card').length === 0) {
                            // Auto-refresh disabled per user request
                            // Show empty state without reloading
                            console.log('No notifications left - showing empty state without reload');
                            
                            // Create empty state display
                            const container = document.querySelector('.modern-notifications-container');
                            if (container) {
                                container.innerHTML = `
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-bell-slash"></i>
                                        </div>
                                        <h4>No notifications found</h4>
                                        <p>You'll see your notifications here when there are updates on your requests.</p>
                                    </div>
                                `;
                            }
                        }
                    }, 300);
                } else {
                    console.warn('⚠️ Notification card not found in DOM');
                }
                
                // Update notification counts
                if (typeof updateNotificationCounts === 'function') {
                    updateNotificationCounts();
                }
                
                // Show success message
                showTableAlert(data.message || 'Notification deleted successfully', 'success');
                
                console.log('✅ Notification deleted successfully');
            } else {
                throw new Error(data.message || 'Server returned success: false');
            }
        })
        .catch(error => {
            console.error('❌ Error deleting notification:', error);
            
            // Restore button state
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-trash"></i>';
            }
            
            // Show error message
            showTableAlert('Error deleting notification: ' + error.message, 'error');
        });
    }
    
    // Refresh notifications
    function refreshNotifications() {
        // Update notification counts
        updateNotificationCounts();
        
        // Manual refresh only - no auto-reload
        showTableAlert('Notification counts updated', 'success');
        console.log('📊 Notification counts refreshed without page reload');
        
        // Optional: Uncomment the lines below if you want manual refresh to reload the page
        // showTableAlert('Refreshing notifications...', 'info');
        // setTimeout(() => {
        //     window.location.reload();
        // }, 1000);
    }
    
    // Track processed notifications to prevent duplicates
    const processedNotifications = new Set();
    
    // Update notification counts with deduplication
    function updateNotificationCounts() {
        fetch('{{ route("notifications.counts") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Generate a unique identifier for this notification update
                const updateId = Date.now().toString();
                
                // Skip if we've already processed a very recent update (within 3 seconds)
                const recentUpdates = Array.from(processedNotifications).filter(id => 
                    parseInt(id) > Date.now() - 3000
                );
                
                if (recentUpdates.length > 0) {
                    console.log('Skipping duplicate notification update');
                    return;
                }
                
                // Mark this update as processed
                processedNotifications.add(updateId);
                
                // Cleanup old processed notifications (keep only last 10)
                while (processedNotifications.size > 10) {
                    processedNotifications.delete(processedNotifications.values().next().value);
                }
                
                // Update notification badges with animations
                updateBadgeWithAnimation(
                    document.querySelector('a[href*="approved"] .notification-badge'), 
                    data.counts.approved, 
                    'approved'
                );
                
                updateBadgeWithAnimation(
                    document.querySelector('a[href*="disapproved"] .notification-badge'), 
                    data.counts.denied, 
                    'denied'
                );
                
                updateBadgeWithAnimation(
                    document.querySelector('a[href*="notifications"] .notification-badge'), 
                    data.counts.total_unread, 
                    'total'
                );
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    // Show alert message
    function showTableAlert(message, type = 'info') {
        // Create a Bootstrap alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insert at the top of the main content
        const mainContent = document.querySelector('.container');
        if (mainContent) {
            mainContent.insertBefore(alertDiv, mainContent.firstChild);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv && alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    }
    
    // Show real-time notification
    function showRealTimeNotification(message, type = 'default') {
        const notification = document.getElementById('realtime-notification');
        const notificationMessage = notification.querySelector('.realtime-notification-message');
        const notificationIcon = notification.querySelector('.realtime-notification-icon i');
        
        // Set message and icon
        notificationMessage.textContent = message;
        
        if (type === 'approved') {
            notificationIcon.className = 'fas fa-check-circle';
            notification.querySelector('.realtime-notification-icon').style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
            notification.querySelector('.realtime-notification-icon').style.color = '#28a745';
        } else if (type === 'denied') {
            notificationIcon.className = 'fas fa-times-circle';
            notification.querySelector('.realtime-notification-icon').style.backgroundColor = 'rgba(220, 53, 69, 0.1)';
            notification.querySelector('.realtime-notification-icon').style.color = '#dc3545';
        } else {
            notificationIcon.className = 'fas fa-bell';
            notification.querySelector('.realtime-notification-icon').style.backgroundColor = 'rgba(0, 123, 255, 0.1)';
            notification.querySelector('.realtime-notification-icon').style.color = '#007bff';
        }
        
        // Show notification
        notification.classList.remove('hidden');
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Hide after 5 seconds
        setTimeout(() => {
            closeRealTimeNotification();
        }, 5000);
    }
    
    // Close real-time notification
    function closeRealTimeNotification() {
        const notification = document.getElementById('realtime-notification');
        notification.classList.remove('show');
        
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 300);
    }
    
    // Function to clear page notifications
    function clearPageNotifications(page) {
        fetch('{{ route("notifications.clear.page") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                page: page
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update notification counts
                updateNotificationCounts();
            }
        })
        .catch(error => console.error('Error clearing notifications:', error));
    }
    
    // Logout modal functions
    function showLogoutModal() {
        const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
        modal.show();
    }

    function confirmLogout() {
        document.getElementById('logout-form').submit();
    }
    
    // Listen for notification updates with debounce protection
    let lastEventTime = 0;
    document.addEventListener('notificationsUpdated', function(event) {
        console.log('Notification update event received', event.detail);
        
        // Debounce protection - ignore events that come too quickly (within 2 seconds)
        const now = Date.now();
        if (now - lastEventTime < 2000) {
            console.log('Debouncing notification event - too soon after last one');
            return;
        }
        lastEventTime = now;
        
        if (event.detail && event.detail.counts) {
            // Update badges and show real-time notification
            updateNotificationCounts();
            
            // Only show the popup notification for the event (not during regular polling)
            if (event.detail.type === 'request_approved') {
                showRealTimeNotification('A request has been approved!', 'approved');
            } else if (event.detail.type === 'request_denied') {
                showRealTimeNotification('A request has been denied!', 'denied');
            } else {
                showRealTimeNotification('New notification received');
            }
        }
    });
    
    // Use a variable to track whether polling is currently happening
    let isPolling = false;
    
    // Auto-refresh polling disabled per user request
    // Poll for new notifications every 30 seconds with protection against overlapping
    // setInterval(() => {
    //     // Skip if we're already processing a poll request
    //     if (isPolling) return;
    //     
    //     isPolling = true;
    //     updateNotificationCounts();
    //     
    //     // Reset the flag after polling is done (with a timeout as a safeguard)
    //     setTimeout(() => {
    //         isPolling = false;
    //     }, 5000); // 5 second timeout as a safety
    // }, 30000);
    
    console.log('🔕 Auto-refresh disabled - notifications will only update on user actions');
</script>

<!-- User Settings Modal -->
<div class="modal fade" id="userSettingsModal" tabindex="-1" aria-labelledby="userSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content modern-modal">
            <div class="modal-header" style="background: #800020 !important; color: white !important;">
                <h5 class="modal-title" id="userSettingsModalLabel">
                    <i class="fas fa-user-cog me-2"></i>User Settings - {{ Auth::user()->First_Name }} {{ Auth::user()->Last_Name }}
                </h5>
                <button type="button" class="btn-close btn-close-white visible-close-btn" data-bs-dismiss="modal" aria-label="Close" style="border:0;background:transparent;">
                    <i class="fa-solid fa-xmark" aria-hidden="true" style="color:#fff; font-size:1.05rem;"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm" action="{{ route('user.change-password') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="currentPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="currentPasswordFeedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="newPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="newPasswordFeedback"></div>
                        <div class="form-text">Password must be at least 8 characters and include numbers and letters.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" name="new_password_confirmation" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="confirmPasswordFeedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="savePasswordChanges">
                    <i class="fas fa-save me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
