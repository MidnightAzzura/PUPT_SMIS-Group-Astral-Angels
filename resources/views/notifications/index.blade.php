@extends('layout.app')

@section('content')
<!-- Add CSRF token for JavaScript -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
<link rel="stylesheet" href="{{ asset('css/notification-badges.css') }}">
<link rel="stylesheet" href="{{ asset('css/collapsed-sidebar-badges.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-notification-fix.css') }}">
<link rel="stylesheet" href="{{ asset('css/notification-button-fixes.css') }}">
<link rel="stylesheet" href="{{ asset('css/popup-notifications.css') }}">
<link rel="stylesheet" href="{{ asset('css/universal-sidebar-scrollbar.css') }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode-switch.css') }}">
<link rel="stylesheet" href="{{ asset('css/user-settings-dropdown.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Include the bulletproof JavaScript -->
<script src="{{ asset('js/sidebar-fix.js') }}" defer></script>
<!-- Include the enhanced notifications script -->
<script src="{{ asset('js/enhanced-notifications.js') }}" defer></script>
<!-- Include popup notifications -->
<script src="{{ asset('js/popup-notifications.js') }}" defer></script>

<!-- Hide pagination completely for notifications -->
<style>
    /* Hide ALL pagination elements with maximum specificity */
    .notifications-table-container .pagination,
    .notifications-table-container nav[aria-label*="pagination"],
    .main-content .pagination,
    .pagination,
    nav[aria-label*="pagination"],
    .pagination-wrapper,
    .pagination-container,
    .paginate_links,
    .paginate_wrapper,
    .page-link,
    .page-item,
    [class*="pagination"],
    [class*="paginate"],
    [aria-label*="pagination"],
    [aria-label*="Previous"],
    [aria-label*="Next"] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }
    
    /* Hide pagination text indicators */
    *:contains("Previous"),
    *:contains("Next"),
    *:contains("Showing"),
    *:contains("results") {
        display: none !important;
    }
    
    /* Force hide any Laravel pagination */
    .laravel-pagination,
    .laravel-paginate {
        display: none !important;
    }
</style>
<!-- Immediate zero count badge fix -->
<script>
// Hide zero count badges immediately and remove (0) from text
(function() {
    function hideZeroBadgesAndText() {
        // Remove (0) from sidebar link text content
        const sidebarLinks = document.querySelectorAll('.sidebar a');
        sidebarLinks.forEach(link => {
            if (link.textContent) {
                // Remove (0) from the end of text content
                const textContent = link.textContent;
                if (textContent.includes('(0)')) {
                    // Find the span containing the text and remove (0)
                    const spans = link.querySelectorAll('span:not(.notification-badge):not([class*="badge"])');
                    spans.forEach(span => {
                        if (span.textContent.includes('(0)')) {
                            span.textContent = span.textContent.replace(/\s*\(0\)\s*/g, '');
                        }
                    });
                    
                    // Also remove from the link's direct text nodes
                    const walker = document.createTreeWalker(
                        link,
                        NodeFilter.SHOW_TEXT,
                        null,
                        false
                    );
                    
                    let node;
                    while (node = walker.nextNode()) {
                        if (node.textContent.includes('(0)')) {
                            node.textContent = node.textContent.replace(/\s*\(0\)\s*/g, '');
                        }
                    }
                }
            }
        });
        
        // SENIOR DEVELOPER FIX: Enhanced badge preservation logic  
        // Hide zero count badges - BUT PRESERVE BADGES WITH ACTUAL COUNTS
        const badges = document.querySelectorAll('.sidebar .notification-badge, .sidebar .badge, .sidebar [class*="badge"]');
        badges.forEach(badge => {
            if (badge && badge.textContent) {
                const count = badge.textContent.trim();
                const numericCount = parseInt(count.replace(/[()]/g, ''));
                
                // CRITICAL CHANGE: Only hide badges that are truly empty or have never shown content
                if (count === '' || count === '0' || count === '(0)') {
                    // Only hide if badge has never shown content > 0
                    if (!badge.hasAttribute('data-ever-had-count')) {
                        badge.style.display = 'none';
                    }
                } else if (numericCount > 0) {
                    // Mark badge as having had content and ensure it stays visible
                    badge.setAttribute('data-ever-had-count', 'true');
                    badge.style.display = 'inline-flex';
                    badge.style.visibility = 'visible';
                    badge.style.opacity = '1';
                } else if (isNaN(numericCount)) {
                    // Only hide badges with invalid content if they never had valid content
                    if (!badge.hasAttribute('data-ever-had-count')) {
                        badge.style.display = 'none';
                    }
                }
                // If badge previously had content > 0, NEVER hide it regardless of current value
            }
        });
    }
    
    // Run immediately
    hideZeroBadgesAndText();
    
    // Force remove all pagination elements
    function removePaginationElements() {
        const paginationSelectors = [
            '.pagination',
            'nav[aria-label*="pagination"]',
            '.pagination-wrapper',
            '.pagination-container',
            '.paginate_links',
            '.page-link',
            '.page-item',
            '[class*="pagination"]',
            '[class*="paginate"]',
            '[aria-label*="Previous"]',
            '[aria-label*="Next"]'
        ];
        
        paginationSelectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                el.remove();
            });
        });
        
        // Also hide any text containing pagination indicators
        const textNodes = document.createTreeWalker(
            document.body,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );
        
        const nodesToRemove = [];
        let node;
        while (node = textNodes.nextNode()) {
            if (node.textContent && 
                (node.textContent.includes('Previous') || 
                 node.textContent.includes('Next') || 
                 node.textContent.includes('Showing') && node.textContent.includes('results'))) {
                const parent = node.parentElement;
                if (parent && (parent.classList.contains('pagination') || 
                               parent.getAttribute('aria-label') && parent.getAttribute('aria-label').includes('pagination'))) {
                    nodesToRemove.push(parent);
                }
            }
        }
        
        nodesToRemove.forEach(el => el.remove());
    }
    
    // Run pagination removal immediately
    removePaginationElements();
    
    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            hideZeroBadgesAndText();
            removePaginationElements();
        });
    } else {
        hideZeroBadgesAndText();
        removePaginationElements();
    }
    
    // Run periodically to catch any dynamic updates
    setInterval(function() {
        hideZeroBadgesAndText();
        removePaginationElements();
    }, 1000);
    
    // Run when the page becomes visible (for handling tab switches)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(function() {
                hideZeroBadgesAndText();
                removePaginationElements();
            }, 100);
        }
    });
})();
</script>
<!-- Remove conflicting scripts that cause issues -->
<!-- <script src="{{ asset('js/notification-fix.js') }}"></script>
<script src="{{ asset('js/notification-direct-fix.js') }}"></script>
<script src="{{ asset('js/notification-fixes-2023.js') }}"></script> -->
<!-- FINAL NOTIFICATION FIX SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Final notification fix loading...');
    
    // Wait for DOM to be fully ready
    setTimeout(function() {
        console.log('🔧 Applying final notification fixes...');
        
        // Clear any existing functions and create new ones
        window.markAsRead = undefined;
        window.deleteNotification = undefined;
        
        // Create the definitive markAsRead function
        window.markAsRead = function(notificationId) {
            console.log('🔄 FINAL markAsRead called for:', notificationId);
            
            if (!notificationId) {
                console.error('❌ No notification ID provided');
                alert('Error: No notification ID provided');
                return;
            }
            
            // Find the notification row
            const notificationRow = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (!notificationRow) {
                console.error('❌ Notification row not found for ID:', notificationId);
                alert('Error: Notification not found');x
                return;
            }
            
            // Find the mark button
            const markBtn = notificationRow.querySelector('.btn-outline-success');
            if (!markBtn) {
                console.error('❌ Mark button not found');
                alert('Error: Mark button not found');
                return;
            }
            
            // Check for CSRF token
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfMeta) {
                console.error('❌ CSRF token meta tag not found');
                alert('Error: CSRF token not found. Please refresh the page.');
                return;
            }
            
            const csrfToken = csrfMeta.getAttribute('content');
            if (!csrfToken) {
                console.error('❌ CSRF token is empty');
                alert('Error: CSRF token is empty. Please refresh the page.');
                return;
            }
            
            // Show loading state
            markBtn.disabled = true;
            markBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // Prepare form data
            const formData = new FormData();
            formData.append('notification_ids[]', notificationId);
            formData.append('_token', csrfToken);
            
            console.log('� Sending AJAX request...');
            
            // Make the AJAX request
            fetch('/notifications/mark-read', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('📡 Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('✅ Response data:', data);
                
                if (data.success) {
                    // Update notification appearance
                    notificationRow.classList.remove('table-warning');
                    notificationRow.classList.add('table-light');
                    
                    // Update status badge
                    const statusBadge = notificationRow.querySelector('td:nth-child(2) .badge');
                    if (statusBadge) {
                        statusBadge.classList.remove('bg-primary');
                        statusBadge.classList.add('bg-secondary');
                        statusBadge.innerHTML = '<i class="fas fa-check"></i> Read';
                    }
                    
                    // Hide the mark button
                    markBtn.style.display = 'none';
                    
                    // Update counts if function exists
                    if (typeof updateNotificationCounts === 'function') {
                        updateNotificationCounts();
                    }
                    
                    // Show success message
                    showSuccessMessage('Notification marked as read successfully!');
                    
                    console.log('✅ Notification marked as read successfully');
                } else {
                    throw new Error(data.message || 'Server returned success: false');
                }
            })
            .catch(error => {
                console.error('❌ Error:', error);
                
                // Restore button state
                markBtn.disabled = false;
                markBtn.innerHTML = '<i class="fas fa-check"></i>';
                
                // Show error message
                showErrorMessage('Error marking notification as read: ' + error.message);
            });
        };
        
        // Create the definitive deleteNotification function
        window.deleteNotification = function(notificationId) {
            console.log('🗑️ FINAL deleteNotification called for:', notificationId);
            
            if (!confirm('Are you sure you want to delete this notification?')) {
                return;
            }
            
            if (!notificationId) {
                console.error('❌ No notification ID provided');
                alert('Error: No notification ID provided');
                return;
            }
            
            // Find the notification row
            const notificationRow = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (!notificationRow) {
                console.error('❌ Notification row not found for ID:', notificationId);
                alert('Error: Notification not found');
                return;
            }
            
            // Find the delete button
            const deleteBtn = notificationRow.querySelector('.btn-outline-danger');
            if (!deleteBtn) {
                console.error('❌ Delete button not found');
                alert('Error: Delete button not found');
                return;
            }
            
            // Check for CSRF token
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfMeta) {
                console.error('❌ CSRF token meta tag not found');
                alert('Error: CSRF token not found. Please refresh the page.');
                return;
            }
            
            const csrfToken = csrfMeta.getAttribute('content');
            if (!csrfToken) {
                console.error('❌ CSRF token is empty');
                alert('Error: CSRF token is empty. Please refresh the page.');
                return;
            }
            
            // Show loading state
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // Prepare form data
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', csrfToken);
            
            console.log('📡 Sending delete request...');
            
            // Make the AJAX request
            fetch(`/notifications/${notificationId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
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
                    // Fade out and remove the row
                    notificationRow.style.transition = 'opacity 0.5s ease';
                    notificationRow.style.opacity = '0';
                    
                    setTimeout(() => {
                        notificationRow.remove();
                    }, 500);
                    
                    // Update counts if function exists
                    if (typeof updateNotificationCounts === 'function') {
                        updateNotificationCounts();
                    }
                    
                    // Show success message
                    showSuccessMessage('Notification deleted successfully!');
                    
                    console.log('✅ Notification deleted successfully');
                } else {
                    throw new Error(data.message || 'Server returned success: false');
                }
            })
            .catch(error => {
                console.error('❌ Delete error:', error);
                
                // Restore button state
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                
                // Show error message
                showErrorMessage('Error deleting notification: ' + error.message);
            });
        };
        
        // Helper function to show success messages
        function showSuccessMessage(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container');
            if (container) {
                container.insertBefore(alertDiv, container.firstChild);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }
        }
        
        // Helper function to show error messages
        function showErrorMessage(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container');
            if (container) {
                container.insertBefore(alertDiv, container.firstChild);
                
                // Auto-dismiss after 8 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 8000);
            }
        }
        
        // Ensure buttons are properly clickable
        document.querySelectorAll('.btn-outline-success, .btn-outline-danger').forEach(btn => {
            btn.style.pointerEvents = 'auto';
            btn.style.cursor = 'pointer';
        });
        
        console.log('✅ Final notification fixes applied successfully');
        console.log('✅ Functions available:', {
            markAsRead: typeof window.markAsRead,
            deleteNotification: typeof window.deleteNotification
        });
    }, 1000);
});
</script>
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
    
    // Test button functionality
    setTimeout(() => {
        console.log('Testing button functionality...');
        
        // Test each mark as read button
        document.querySelectorAll('[onclick*="markAsRead"]').forEach((btn, index) => {
            console.log(`Mark as read button ${index + 1}:`, {
                element: btn,
                onclick: btn.getAttribute('onclick'),
                disabled: btn.disabled,
                visible: btn.offsetParent !== null
            });
        });
        
        // Test each delete button
        document.querySelectorAll('[onclick*="deleteNotification"]').forEach((btn, index) => {
            console.log(`Delete button ${index + 1}:`, {
                element: btn,
                onclick: btn.getAttribute('onclick'),
                disabled: btn.disabled,
                visible: btn.offsetParent !== null
            });
        });
        
        // Test if functions exist in global scope
        console.log('Functions available:');
        console.log('- markAsRead:', typeof window.markAsRead);
        console.log('- deleteNotification:', typeof window.deleteNotification);
        console.log('- markAllAsRead:', typeof window.markAllAsRead);
        
        // Test button functionality
        setTimeout(() => {
            console.log('🧪 Testing button functionality...');
            
            // Test each button for clickability
            document.querySelectorAll('.btn-outline-success, .btn-outline-danger').forEach((btn, index) => {
                const isSuccess = btn.classList.contains('btn-outline-success');
                const btnType = isSuccess ? 'Mark as Read' : 'Delete';
                
                console.log(`${btnType} button ${index + 1}:`, {
                    element: btn,
                    disabled: btn.disabled,
                    visible: btn.offsetParent !== null,
                    hasClick: btn.onclick !== null,
                    styles: {
                        pointerEvents: getComputedStyle(btn).pointerEvents,
                        cursor: getComputedStyle(btn).cursor,
                        display: getComputedStyle(btn).display
                    }
                });
            });
            
            // Add manual click test
            console.log('🔄 You can now test the buttons manually. Check console for click events.');
        }, 2000);
        
    }, 1000);
    
    // Monitor click events on notification items
    document.addEventListener('click', function(e) {
        const notificationItem = e.target.closest('.notification-card, .notification-item, [data-notification-id]');
        if (notificationItem) {
            console.log('Click on notification item:', {
                element: e.target,
                containsButton: e.target.closest('button, .btn, a') ? true : false,
                notificationId: notificationItem.dataset.notificationId || notificationItem.dataset.id,
                target: e.target.className,
                parentButton: e.target.closest('button')?.getAttribute('onclick')
            });
        }
    }, true);
});
</script>

    <button class="toggle-sidebar-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- UNIVERSAL SIDEBAR STRUCTURE - BULLETPROOF VERSION -->
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
            
            <!-- BULLETPROOF DROPDOWN BUTTON -->
            <a href="#" class="dropdown-btn" style="display: flex; align-items: center; margin-bottom: 10px;" role="button" aria-expanded="false">
                <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
                <i class="fa fa-caret-down" style="margin-left: 8px;"></i>
            </a>
            
            <!-- BULLETPROOF DROPDOWN CONTAINER -->
            <div class="dropdown-container" style="margin: 10px auto; text-align: center;" aria-hidden="true">
                @if(Auth::user()->role === 'User')
                    <a href="{{ route('requests.create') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                        <i class="fas fa-plus" style="margin-right: 8px;"></i> <span>Create New Request</span>
                    </a>
                @endif
                <a href="{{ route('requests.pending') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                    <i class="fas fa-clock" style="margin-right: 8px;"></i> <span>Pending</span>
                    @if(isset($notificationCounts) && $notificationCounts['pending'] > 0)
                        <span class="notification-badge warning">{{ $notificationCounts['pending'] }}</span>
                    @endif
                </a>
                <a href="{{ route('requests.approved') }}" style="display: flex; align-items: center; margin-bottom: 10px;" onclick="clearPageNotifications('approved')">
                    <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> 
                    <span>Approved</span>
                    @if(isset($notificationCounts) && $notificationCounts['approved'] > 0)
                        <span class="notification-badge success">{{ $notificationCounts['approved'] }}</span>
                    @endif
                </a>
                <a href="{{ route('requests.released') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                    <i class="fas fa-truck" style="margin-right: 8px;"></i> <span>Released</span>
                    @if(isset($notificationCounts) && $notificationCounts['released'] > 0)
                        <span class="notification-badge purple">{{ $notificationCounts['released'] }}</span>
                    @endif
                </a>
                <a href="{{ route('requests.disapproved') }}" style="display: flex; align-items: center; margin-bottom: 10px;" onclick="clearPageNotifications('disapproved')">
                    <i class="fas fa-times-circle" style="margin-right: 8px;"></i> 
                    <span>Disapproved</span>
                    @if(isset($notificationCounts) && $notificationCounts['denied'] > 0)
                        <span class="notification-badge danger">{{ $notificationCounts['denied'] }}</span>
                    @endif
                </a>
            </div>
            
            <a href="{{ route('reports') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                <i class="fas fa-chart-line" style="margin-right: 8px;"></i> <span>Reports</span>
            </a>
            
            <a href="{{ route('help.support') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                <i class="fas fa-question-circle" style="margin-right: 8px;"></i> <span>Help & Support</span>
            </a>
            
            <!-- NEW: Rate / Questionnaire Button -->
            <a id="rateSystemBtn" href="https://forms.gle/12SD5ZjPTXbsgqzC7" target="_blank" rel="noopener" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px; background: rgba(255,255,255,0.08); border-radius:5px;">
                <i class="fas fa-star-half-alt" style="margin-right: 8px;"></i> <span>Rate Our System</span>
            </a>
            
            <!-- Hidden forms for notification actions -->
            <form id="mark-read-form" action="{{ route('notifications.mark.read') }}" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="notification_ids[]" id="mark-read-id">
            </form>
            
            <form id="delete-notification-form" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
            
            <form id="mark-all-read-form" action="{{ route('notifications.mark.all.read') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
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
            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
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
                userButton.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    userMenu.classList.toggle('show');
                };
                document.addEventListener('click', function(e) {
                    if (!userButton.contains(e.target) && !userMenu.contains(e.target)) {
                        userMenu.classList.remove('show');
                    }
                });
            }
        });
    </script>
    
    <div class="main-content p-4">
        <!-- Add Welcome Header Section -->
        <div class="dashboard-header modern-header" style="background: linear-gradient(135deg, #800020, #600018) !important; background-color: #800020 !important; border-radius: 10px !important; margin-bottom: 1.5rem !important; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;">
            <div class="header-content" style="color: white !important; padding: 0.75rem 1.5rem !important; background: transparent !important;">
                <h2 class="welcome-text" style="color: #ffffff !important; font-size: 1.5rem !important; margin: 0 !important;">
                    @if(Auth::user()->role == 'SuperAdmin')
                        <i class="fas fa-crown text-warning me-2"></i>Welcome SuperAdmin,
                        <span class="user-name" style="color: #ffffff !important;">{{ Auth::user()->First_Name }} {{ Auth::user()->Middle_Initial }}. {{ Auth::user()->Last_Name }}!</span>
                    @else
                        <i class="fas fa-user-shield me-2"></i>Welcome {{ ucfirst(Auth::user()->role) }},
                        <span class="user-name" style="color: #ffffff !important;">{{ Auth::user()->First_Name }} {{ Auth::user()->Middle_Initial }}. {{ Auth::user()->Last_Name }}!</span>
                    @endif
                </h2>
                <p class="header-subtitle" style="color: white !important; margin: 0.25rem 0 0 0 !important; font-size: 0.9rem !important;">Your notifications center</p>
            </div>
        </div>
        
        <div class="header">
            <h2 style="color: #ffffff !important;"><i class="fas fa-bell me-2"></i>Notifications</h2>
            <div id="currentDateTime" class="text-center mb-3"></div>
        </div>
        
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
            <h5>{{ Auth::user()->Office }} Notifications 
                @if($filter != 'all')
                    <span class="badge {{ $filter == 'approved' ? 'bg-success' : ($filter == 'denied' ? 'bg-danger' : 'bg-primary') }}">
                        {{ ucfirst($filter) }}
                    </span>
                @endif
            </h5>
            <p class="text-muted">Total: {{ $notifications->count() }} notifications{{ $filter != 'all' ? ' matching filter' : '' }}</p>
                    </div>
                    <div class="col-md-6 text-end">                        <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i> Mark All as Read
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="refreshNotifications()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="forceRefresh()">
                            <i class="fas fa-redo-alt"></i> Force Refresh
                        </button>
                    </div>
                </div>
            </div>            <!-- Filter Tabs -->
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
            <!-- Modern Notification Messages -->
            <div class="modern-notifications-container">
                <div class="notifications-table-container">
                    @if(count($notifications) > 0)
                        <div class="table-responsive">
                            <table class="table notification-table">
                                <thead>
                                    <tr>
                                        <th width="3%">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                        <th width="12%">Status</th>
                                        <th width="12%">Type</th>
                                        <th width="35%">Message</th>
                                        <th width="10%">Office</th>
                                        <th width="8%">User ID</th>
                                        <th width="15%">Date/Time</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notifications as $notification)
                        <tr class="notification-row {{ $notification->is_read ? 'table-light' : 'table-warning' }}" 
                            data-notification-id="{{ $notification->notification_id }}" 
                            data-action="{{ $notification->action }}">
                            <td>
                                <input type="checkbox" 
                                       class="form-check-input notification-checkbox" 
                                       value="{{ $notification->notification_id }}">
                            </td>
                            <td>
                                @if($notification->is_read)
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-check"></i> Read
                                    </span>
                                @else
                                    <span class="badge bg-primary">
                                        <i class="fas fa-envelope"></i> Unread
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($notification->action === 'request_approved')
                                    <span class="badge bg-success">
                                        <i class="fas fa-thumbs-up"></i> Approved
                                    </span>
                                @elseif($notification->action === 'request_denied')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Denied
                                    </span>
                                @else
                                    <span class="badge bg-info">
                                        <i class="fas fa-info-circle"></i> Info
                                    </span>
                                @endif
                            </td>
                                    </span>
                                @elseif($notification->action === 'request_denied')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Denied
                                    </span>
                                @else
                                    <span class="badge bg-info">
                                        <i class="fas fa-info-circle"></i> Info
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="notification-message-cell">
                                    {{ Str::limit($notification->message, 80) }}
                                    @if(strlen($notification->message) > 80)
                                        <button class="btn btn-link btn-sm p-0 ms-1" 
                                                onclick="showFullMessage('{{ $notification->notification_id }}')"
                                                title="Show full message">
                                            <i class="fas fa-expand-alt"></i>
                                        </button>
                                    @endif
                                </div>
                                <div class="full-message d-none" id="full-message-{{ $notification->notification_id }}">
                                    {{ $notification->message }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-dark">{{ $notification->office }}</span>
                            </td>
                            <td>
                                @if($notification->user_id)
                                    <code>{{ $notification->user_id }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="date-time-cell">
                                    <div class="fw-bold">{{ $notification->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $notification->created_at->format('h:i A') }}</small>
                                    <br>
                                    <small class="text-success">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if(!$notification->is_read)
                                        <button class="btn btn-sm btn-outline-success" 
                                                onclick="markAsRead({{ $notification->notification_id }})"
                                                id="mark-btn-{{ $notification->notification_id }}"
                                                data-notification-id="{{ $notification->notification_id }}"
                                                title="Mark as read">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    <button class="btn btn-sm btn-outline-info" 
                                            onclick="viewNotificationDetails({{ $notification->notification_id }})"
                                            id="view-btn-{{ $notification->notification_id }}"
                                            data-notification-id="{{ $notification->notification_id }}"
                                            title="View details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="viewRelatedRequest('{{ $notification->action }}', '{{ $notification->office }}', {{ $notification->request_id ?? 'null' }})"
                                            id="view-request-btn-{{ $notification->notification_id }}"
                                            data-notification-id="{{ $notification->notification_id }}"
                                            data-action="{{ $notification->action }}"
                                            data-office="{{ $notification->office }}"
                                            data-request-id="{{ $notification->request_id ?? '' }}"
                                            title="View request">
                                        <i class="fas fa-external-link-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteNotification({{ $notification->notification_id }})"
                                            id="delete-btn-{{ $notification->notification_id }}"
                                            data-notification-id="{{ $notification->notification_id }}"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-notifications">
                                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No notifications found</h5>
                                    <p class="text-muted">You'll see notifications here when there are updates on your requests.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-notifications">
                            <div class="text-center py-5">
                                <i class="fas fa-bell-slash fa-4x mb-3 text-muted"></i>
                                <h3>No notifications found</h3>
                                <p class="text-muted">You'll see notifications here when there are updates on your requests.</p>
                            </div>
                        </div>
                    @endif
                </div>

            <!-- Individual Notification Functions -->
            <script>
            // Generate individual functions for each notification
            document.addEventListener('DOMContentLoaded', function() {
                console.log('🔧 Generating individual notification functions...');
                
                @foreach($notifications as $notification)
                // Individual function for notification {{ $notification->notification_id }}
                window.markNotificationAsRead_{{ $notification->notification_id }} = function() {
                    console.log('🔄 Individual markAsRead for notification {{ $notification->notification_id }}');
                    
                    const notificationId = '{{ $notification->notification_id }}';
                    const notificationRow = document.querySelector(`[data-notification-id="${notificationId}"]`);
                    const markBtn = document.getElementById('mark-btn-{{ $notification->notification_id }}');
                    
                    if (!markBtn) {
                        console.error('❌ Mark button not found for notification {{ $notification->notification_id }}');
                        return;
                    }
                    
                    if (!notificationRow) {
                        console.error('❌ Notification row not found for notification {{ $notification->notification_id }}');
                        return;
                    }
                    
                    // Show loading state
                    markBtn.disabled = true;
                    markBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    // Get CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (!csrfToken) {
                        console.error('❌ CSRF token not found');
                        alert('Error: CSRF token not found. Please refresh the page.');
                        markBtn.disabled = false;
                        markBtn.innerHTML = '<i class="fas fa-check"></i>';
                        return;
                    }
                    
                    // Prepare form data
                    const formData = new FormData();
                    formData.append('notification_ids[]', notificationId);
                    formData.append('_token', csrfToken);
                    
                    console.log('📡 Sending request to mark notification {{ $notification->notification_id }} as read...');
                    
                    // Make AJAX request
                    fetch('/notifications/mark-read', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        console.log('📡 Response for notification {{ $notification->notification_id }}:', response.status);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        
                        return response.json();
                    })
                    .then(data => {
                        console.log('✅ Success response for notification {{ $notification->notification_id }}:', data);
                        
                        if (data.success) {
                            // Update notification appearance
                            notificationRow.classList.remove('table-warning');
                            notificationRow.classList.add('table-light');
                            
                            // Update badge
                            const statusBadge = notificationRow.querySelector('td:nth-child(2) .badge');
                            if (statusBadge) {
                                statusBadge.classList.remove('bg-primary');
                                statusBadge.classList.add('bg-secondary');
                                statusBadge.innerHTML = '<i class="fas fa-check"></i> Read';
                            }
                            
                            // Hide the mark button
                            markBtn.style.display = 'none';
                            
                            // Update counts
                            if (typeof updateNotificationCounts === 'function') {
                                updateNotificationCounts();
                            }
                            
                            // Show success message
                            showNotificationMessage('Notification {{ $notification->notification_id }} marked as read!', 'success');
                            
                            console.log('✅ Notification {{ $notification->notification_id }} marked as read successfully');
                        } else {
                            throw new Error(data.message || 'Server returned success: false');
                        }
                    })
                    .catch(error => {
                        console.error('❌ Error marking notification {{ $notification->notification_id }} as read:', error);
                        
                        // Restore button state
                        markBtn.disabled = false;
                        markBtn.innerHTML = '<i class="fas fa-check"></i>';
                        
                        // Show error message
                        showNotificationMessage('Error marking notification {{ $notification->notification_id }} as read: ' + error.message, 'error');
                    });
                };
                
                // Individual delete function for notification {{ $notification->notification_id }}
                window.deleteNotificationItem_{{ $notification->notification_id }} = function() {
                    console.log('🗑️ Individual delete for notification {{ $notification->notification_id }}');
                    
                    if (!confirm('Are you sure you want to delete this notification?')) {
                        return;
                    }
                    
                    const notificationId = '{{ $notification->notification_id }}';
                    const notificationRow = document.querySelector(`[data-notification-id="${notificationId}"]`);
                    const deleteBtn = document.getElementById('delete-btn-{{ $notification->notification_id }}');
                    
                    if (!deleteBtn) {
                        console.error('❌ Delete button not found for notification {{ $notification->notification_id }}');
                        return;
                    }
                    
                    if (!notificationRow) {
                        console.error('❌ Notification row not found for notification {{ $notification->notification_id }}');
                        return;
                    }
                    
                    // Show loading state
                    deleteBtn.disabled = true;
                    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    // Get CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (!csrfToken) {
                        console.error('❌ CSRF token not found');
                        alert('Error: CSRF token not found. Please refresh the page.');
                        deleteBtn.disabled = false;
                        deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                        return;
                    }
                    
                    // Prepare form data
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    formData.append('_token', csrfToken);
                    
                    console.log('📡 Sending delete request for notification {{ $notification->notification_id }}...');
                    
                    // Make AJAX request
                    fetch(`/notifications/${notificationId}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        console.log('📡 Delete response for notification {{ $notification->notification_id }}:', response.status);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        
                        return response.json();
                    })
                    .then(data => {
                        console.log('✅ Delete success response for notification {{ $notification->notification_id }}:', data);
                        
                        if (data.success) {
                            // Fade out and remove the row
                            notificationRow.style.transition = 'opacity 0.5s ease';
                            notificationRow.style.opacity = '0';
                            
                            setTimeout(() => {
                                notificationRow.remove();
                                console.log('✅ Notification {{ $notification->notification_id }} row removed from DOM');
                            }, 500);
                            
                            // Update counts
                            if (typeof updateNotificationCounts === 'function') {
                                updateNotificationCounts();
                            }
                            
                            // Show success message
                            showNotificationMessage('Notification {{ $notification->notification_id }} deleted successfully!', 'success');
                            
                            console.log('✅ Notification {{ $notification->notification_id }} deleted successfully');
                        } else {
                            throw new Error(data.message || 'Server returned success: false');
                        }
                    })
                    .catch(error => {
                        console.error('❌ Error deleting notification {{ $notification->notification_id }}:', error);
                        
                        // Restore button state
                        deleteBtn.disabled = false;
                        deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                        
                        // Show error message
                        showNotificationMessage('Error deleting notification {{ $notification->notification_id }}: ' + error.message, 'error');
                    });
                };
                
                // Individual view function for notification {{ $notification->notification_id }}
                window.viewNotificationDetails_{{ $notification->notification_id }} = function() {
                    console.log('👁️ Individual view for notification {{ $notification->notification_id }}');
                    
                    const notificationRow = document.querySelector(`[data-notification-id="{{ $notification->notification_id }}"]`);
                    if (notificationRow) {
                        const message = notificationRow.querySelector('.notification-message-cell')?.textContent?.trim() || 'No message';
                        const office = notificationRow.querySelector('td:nth-child(5) .badge')?.textContent || 'Unknown';
                        const dateTime = notificationRow.querySelector('.date-time-cell')?.textContent?.replace(/\s+/g, ' ').trim() || 'Unknown';
                        
                        alert(`Notification {{ $notification->notification_id }} Details:
                        
Message: ${message}
Office: ${office}
Date/Time: ${dateTime}
Status: {{ $notification->is_read ? 'Read' : 'Unread' }}`);
                    }
                };
                
                @endforeach
                
                // Helper function for showing notification messages
                function showNotificationMessage(message, type) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show`;
                    alertDiv.innerHTML = `
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    
                    const container = document.querySelector('.container');
                    if (container) {
                        container.insertBefore(alertDiv, container.firstChild);
                        
                        // Auto-dismiss after 5 seconds
                        setTimeout(() => {
                            if (alertDiv.parentNode) {
                                alertDiv.remove();
                            }
                        }, 5000);
                    }
                }
                
                console.log('✅ Individual notification functions generated successfully');
            });
            </script>

            <!-- Bulk Actions -->
            @if($notifications->count() > 0)
            <div class="bulk-actions mt-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="selected-count">
                            <span id="selectedCount">0</span> notifications selected
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-success" 
                                    onclick="markSelectedAsRead()" 
                                    id="markSelectedBtn" 
                                    disabled>
                                <i class="fas fa-check-double"></i> Mark Selected as Read
                            </button>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="deleteSelected()" 
                                    id="deleteSelectedBtn" 
                                    disabled>
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to logout?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="confirmLogout()">Logout</button>
                </div>
            </div>
        </div>
    </div>
</body>

<style>
/* Style buttons to look like nav-links */
.nav-tabs button.nav-link {
    background: none;
    border: 1px solid transparent;
    border-bottom-color: #dee2e6;
    color: #495057;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 0.25rem 0.25rem 0 0;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}

.nav-tabs button.nav-link:hover {
    border-color: #e9ecef #e9ecef #dee2e6;
    color: #495057;
    background-color: #f8f9fa;
}

.nav-tabs button.nav-link.active {
    color: #495057;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}

.nav-tabs button.nav-link:focus {
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Notification Table Styles */
.notification-table {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.notification-table thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    border: none;
    padding: 15px 10px;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.notification-table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f1f3f4;
}

.notification-table tbody tr:hover {
    background-color: #f8f9ff;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.notification-table tbody td {
    padding: 12px 10px;
    vertical-align: middle;
    border: none;
}

.notification-row.table-warning {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
}

.notification-row.table-light {
    background-color: #f8f9fa;
    border-left: 4px solid #6c757d;
}

.notification-message-cell {
    line-height: 1.4;
    color: #333;
}

.date-time-cell {
    font-size: 0.9rem;
}

.btn-group .btn {
    margin: 0 1px;
    z-index: 1;
    position: relative;
}

/* Ensure buttons are clickable */
.notification-table .btn {
    pointer-events: all !important;
    cursor: pointer !important;
    z-index: 10;
    position: relative;
}

.notification-table .btn:disabled {
    pointer-events: none;
    opacity: 0.6;
}

/* Fix button group overlapping issues */
.btn-group {
    display: flex;
    gap: 2px;
}

.btn-group .btn {
    flex-shrink: 0;
}

.empty-notifications {
    padding: 60px 20px;
    text-align: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 15px;
    margin: 20px 0;
}

.empty-notifications i {
    opacity: 0.6;
}

/* Bulk Actions Styles */
.bulk-actions {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    border: 1px solid #dee2e6;
}

.selected-count {
    font-weight: 600;
    color: #495057;
}

/* Checkbox Styles */
.form-check-input {
    transform: scale(1.2);
    margin-top: 0;
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

/* Badge Animations */
.badge {
    transition: all 0.3s ease;
}

.badge:hover {
    transform: scale(1.05);
}

/* Responsive Design */
@media (max-width: 768px) {
    .notification-table {
        font-size: 0.875rem;
    }
    
    .notification-table thead th,
    .notification-table tbody td {
        padding: 8px 5px;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
}

@media (max-width: 576px) {
    .table-responsive {
        border: none;
    }
    
    .notification-table {
        margin-bottom: 0;
    }
    
    .bulk-actions .row {
        flex-direction: column;
    }
    
    .bulk-actions .col-md-4 {
        margin-top: 10px;
        text-align: center !important;
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
    updateDateTime(); // Initial call    // Sidebar toggle functionality
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
        
        // Save sidebar state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }// Dropdown and page initialization
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownBtn = document.querySelector('.dropdown-btn');
        const dropdownContainer = document.querySelector('.dropdown-container');

        if (dropdownBtn && dropdownContainer) {
            dropdownBtn.addEventListener('click', function(e) {
                e.preventDefault();
                dropdownContainer.style.display = dropdownContainer.style.display === 'block' ? 'none' : 'block';
                dropdownBtn.classList.toggle('active');
            });
        }        // Initialize notification tabs
        initializeNotificationTabs();
        
        // Set the active filter without triggering a click initially
        const activeTab = document.querySelector('.nav-link.active');
        if (activeTab) {
            console.log('Active tab found:', activeTab.id);
        }
        
        // Log notification data for debugging
        logNotificationItems();
    });
    
    // Log notification items for debugging
    function logNotificationItems() {
        const notifications = document.querySelectorAll('.notification-item');
        console.log('Notification items found:', notifications.length);
        
        notifications.forEach(notification => {
            console.log('Notification:', {
                id: notification.dataset.notificationId,
                action: notification.dataset.action,
                isRead: notification.classList.contains('read')
            });
        });
    }    // Initialize notification tabs with direct click handling
    function initializeNotificationTabs() {
        const tabs = document.querySelectorAll('#notificationTabs button');
        
        // Set up the filter functionality
        filterNotifications();
        
        // Apply the current filter on page load based on active tab
        const activeTab = document.querySelector('#notificationTabs button.active');
        if (activeTab) {
            const filter = activeTab.dataset.filter || activeTab.id.replace('-tab', '');
            console.log('Applying initial filter:', filter);
            applyFilter(filter);
        }
        
        // Add direct click listeners for debugging
        document.getElementById('approved-tab').addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Direct click on approved tab');
            filterByAction('request_approved');
        });
        
        document.getElementById('denied-tab').addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Direct click on denied tab');
            filterByAction('request_denied');
        });
    }
    
    // Apply filter function
    function applyFilter(filter) {
        const notifications = document.querySelectorAll('.notification-item');
        let matchCount = 0;
        
        notifications.forEach(notification => {
            const isRead = notification.classList.contains('read');
            const action = notification.dataset.action;
            
            let show = false;
            switch(filter) {
                case 'all':
                    show = true;
                    break;
                case 'unread':
                    show = !isRead;
                    break;
                case 'approved':
                    show = (action === 'request_approved');
                    break;
                case 'denied':
                    show = (action === 'request_denied');
                    break;
                default:
                    show = true;
            }
            
            notification.style.display = show ? 'block' : 'none';
            
            if (show) {
                matchCount++;
            }
        });
        
        console.log(`Found ${matchCount} notifications matching filter: ${filter}`);
        updateEmptyMessage(filter, matchCount);
    }
    
    // Direct filter by action type
    function filterByAction(actionType) {
        console.log('Filtering directly by action:', actionType);
        const notifications = document.querySelectorAll('.notification-item');
        let matchCount = 0;
        
        notifications.forEach(notification => {
            const action = notification.dataset.action;
            const show = (action === actionType);
            
            console.log('Checking:', {
                id: notification.dataset.notificationId,
                action: action,
                match: show
            });
            
            notification.style.display = show ? 'block' : 'none';
            
            if (show) {
                matchCount++;
            }
        });
        
        console.log(`Found ${matchCount} notifications matching action: ${actionType}`);
        updateEmptyMessage(actionType === 'request_approved' ? 'approved' : 'denied', matchCount);
    }// Filter notifications by tab with enhanced debugging
    function filterNotifications() {
        const tabs = document.querySelectorAll('#notificationTabs button');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Show which tab was clicked
                console.log('Tab clicked:', this.id);
                
                // Remove active class from all tabs and add to current
                tabs.forEach(t => {
                    t.classList.remove('active');
                    console.log('Removed active from:', t.id);
                });
                this.classList.add('active');
                console.log('Added active to:', this.id);
                
                const filter = this.id.replace('-tab', '');
                console.log('Filtering by:', filter);
                
                const notifications = document.querySelectorAll('.notification-item');
                console.log('Total notifications:', notifications.length);
                
                let matchCount = 0;
                
                notifications.forEach(notification => {
                    const isRead = notification.classList.contains('read');
                    const action = notification.dataset.action;
                    
                    // Debug the actual values
                    console.log('Checking notification:', {
                        id: notification.dataset.notificationId,
                        action: action,
                        isRead: isRead,
                        filter: filter
                    });
                    
                    let show = false;
                    switch(filter) {
                        case 'all':
                            show = true;
                            break;
                        case 'unread':
                            show = !isRead;
                            break;
                        case 'approved':
                            show = (action === 'request_approved');
                            console.log('Approved check:', action === 'request_approved', action, 'request_approved');
                            break;
                        case 'denied':
                            show = (action === 'request_denied');
                            console.log('Denied check:', action === 'request_denied', action, 'request_denied');
                            break;
                        default:
                            show = true;
                    }
                    
                    console.log('Should show:', show);
                    notification.style.display = show ? 'block' : 'none';
                    
                    if (show) {
                        matchCount++;
                    }
                });
                
                console.log(`Found ${matchCount} notifications matching filter: ${filter}`);
                
                // Update empty message display
                updateEmptyMessage(filter, matchCount);
            });
        });
    }

    // Debug helper function to check all notifications on the page
    function debugAllNotifications() {
        console.log('------- DEBUG ALL NOTIFICATIONS -------');
        const notificationTypes = {
            'request_approved': 0,
            'request_denied': 0,
            'other': 0
        };
        
        const notifications = document.querySelectorAll('.notification-item');
        console.log(`Total notifications on page: ${notifications.length}`);
        
        notifications.forEach((notification, index) => {
            const action = notification.dataset.action || 'unknown';
            const id = notification.dataset.notificationId || 'unknown';
            const isRead = notification.classList.contains('read');
            
            if (action === 'request_approved') {
                notificationTypes.request_approved++;
            } else if (action === 'request_denied') {
                notificationTypes.request_denied++;
            } else {
                notificationTypes.other++;
            }
            
            console.log(`Notification #${index + 1}:`, {
                id: id,
                action: action,
                isRead: isRead
            });
        });
        
        console.log('Notification count by type:', notificationTypes);
        console.log('------- END DEBUG -------');
    }
    
    // Run debug on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Update notification counts when page loads
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
          // Run initial debug
        setTimeout(debugAllNotifications, 1000);
        
        // Make sure sidebar is initialized properly
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            // Ensure any saved state is restored
            const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isSidebarCollapsed) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
        }
        
        // Other initialization code...
        // ...existing code...
    });
    
    // Mark single notification as read - MAIN FUNCTION
    function markAsRead(notificationId) {
        console.log('🔄 Starting markAsRead for notification:', notificationId);
        
        // Get the notification row
        const notificationRow = document.querySelector(`[data-notification-id="${notificationId}"]`);
        const markBtn = notificationRow?.querySelector('.btn-outline-success');
        
        if (!notificationRow) {
            console.error('❌ Notification row not found for ID:', notificationId);
            alert('Error: Notification not found');
            return;
        }
        
        // Show loading state
        if (markBtn) {
            markBtn.disabled = true;
            markBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('❌ CSRF token not found');
            alert('Error: Security token not found. Please refresh the page.');
            if (markBtn) {
                markBtn.disabled = false;
                markBtn.innerHTML = '<i class="fas fa-check"></i>';
            }
            return;
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('notification_ids[]', notificationId);
        formData.append('_token', csrfToken.getAttribute('content'));
        
        console.log('🔄 Sending AJAX request to mark notification as read');
        
        // Use fetch for the request
        fetch('{{ route("notifications.mark.read") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('📡 Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('✅ Server response:', data);
            
            if (data.success) {
                // Update notification appearance
                notificationRow.classList.remove('table-warning');
                notificationRow.classList.add('table-light');
                
                // Update badge
                const statusBadge = notificationRow.querySelector('td:nth-child(2) .badge');
                if (statusBadge) {
                    statusBadge.classList.remove('bg-primary');
                    statusBadge.classList.add('bg-secondary');
                    statusBadge.innerHTML = '<i class="fas fa-check"></i> Read';
                }
                
                // Hide the mark read button
                if (markBtn) {
                    markBtn.style.display = 'none';
                }
                
                // Update global notification count
                if (typeof updateNotificationCounts === 'function') {
                    updateNotificationCounts();
                }
                
                // Show success message
                if (typeof showTableAlert === 'function') {
                    showTableAlert('Notification marked as read successfully!', 'success');
                }
                
                console.log('✅ Notification marked as read successfully');
            } else {
                throw new Error(data.message || 'Server returned success:false');
            }
        })
        .catch(error => {
            console.error('❌ Error marking notification as read:', error);
            
            // Restore button state
            if (markBtn) {
                markBtn.disabled = false;
                markBtn.innerHTML = '<i class="fas fa-check"></i>';
            }
            
            // Show error message
            if (typeof showTableAlert === 'function') {
                showTableAlert('Error marking notification as read: ' + error.message, 'error');
            } else {
                alert('Error marking notification as read: ' + error.message);
            }
        });
    }    // Mark all notifications as read
    function markAllAsRead() {
        if (!confirm('Are you sure you want to mark all notifications as read?')) {
            return;
        }
        
        // Show loading indicator
        const markAllBtn = document.querySelector('.btn[onclick="markAllAsRead()"]');
        const originalBtnContent = markAllBtn ? markAllBtn.innerHTML : null;
        
        if (markAllBtn) {
            markAllBtn.disabled = true;
            markAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        }

        // Get the current office filter value for SuperAdmin (office segregation fix)
        const officeFilter = document.getElementById('office_filter');
        const selectedOffice = officeFilter ? officeFilter.value : 'all';

        // Get the form
        const form = document.getElementById('mark-all-read-form');
        
        // Submit using fetch with form data including office parameter
        const formData = new FormData(form);
        
        // Add office parameter if specific office is selected
        if (selectedOffice && selectedOffice !== 'all') {
            formData.append('office', selectedOffice);
        }
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Mark all as read response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP status ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Mark all as read response data:', data);
            
            if (data.success) {
                // Update all notification rows in the table
                const notificationRows = document.querySelectorAll('.notification-row');
                notificationRows.forEach(row => {
                    row.classList.remove('table-warning');
                    row.classList.add('table-light');
                    
                    // Update status badges
                    const statusBadge = row.querySelector('td:nth-child(2) .badge');
                    if (statusBadge && statusBadge.textContent.includes('Unread')) {
                        statusBadge.classList.remove('bg-primary');
                        statusBadge.classList.add('bg-secondary');
                        statusBadge.innerHTML = '<i class="fas fa-check"></i> Read';
                    }
                    
                    // Hide mark as read buttons
                    const markBtns = row.querySelectorAll('.btn-outline-success');
                    markBtns.forEach(btn => {
                        btn.style.display = 'none';
                    });
                });
                
                // Reset the mark all button
                if (markAllBtn) {
                    markAllBtn.disabled = false;
                    markAllBtn.innerHTML = '<i class="fas fa-check-double"></i> All Marked as Read';
                    
                    // Restore original text after a delay
                    setTimeout(() => {
                        markAllBtn.innerHTML = originalBtnContent;
                    }, 3000);
                }
                
                // Update notification counts in sidebar
                updateNotificationCounts();
                
                // Show success message
                showTableAlert('All notifications marked as read successfully!', 'success');
            } else {
                throw new Error(data.message || 'Failed to mark all notifications as read');
            }
        })
        .catch(error => {
            console.error('Error marking all as read:', error);
            
            // Fallback to direct form submission
            console.log('Attempting direct form submission for mark all as read');
            form.submit();
            
            // Show an info message to the user
            showTableAlert('Processing... Please wait', 'info');
            
            // Reset button state and reload
            if (markAllBtn) {
                markAllBtn.disabled = false;
                markAllBtn.innerHTML = originalBtnContent;
            }
            
            // Use our helper function to reload with success parameter
            reloadPageAfterAction(2000);
        });
    }    // Refresh notifications without page reload
    function refreshNotifications(force = false) {
        // Update notification counts dynamically
        updateNotificationCounts();
        
        // Optionally, you can fetch fresh notification data and update the DOM
        console.log('Notifications refreshed without page reload');
        
        // If force is true, reload the entire notifications list
        if (force) {
            const currentFilter = '{{ $filter }}';
            fetch(`{{ route('notifications.index') }}?filter=${currentFilter}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok) {
                    console.log('Notifications updated successfully');
                    // Update notification counts
                    updateNotificationCounts();
                }
            })
            .catch(error => {
                console.error('Error updating notifications:', error);
                // Only reload as fallback if there's an error
                const timestamp = new Date().getTime();
                window.location.href = `{{ route('notifications.index') }}?filter=${currentFilter}&t=${timestamp}`;
            });
        }
    }    // Update notification data without full page refresh
    function forceRefresh() {
        // Instead of redirecting, fetch fresh data and update the page
        const currentFilter = '{{ $filter }}';
        
        // Show loading indicator
        console.log('Updating notifications...');
        
        // Fetch fresh notification counts
        updateNotificationCounts();
        
        // You can extend this to fetch fresh notification list if needed
        fetch(`{{ route('notifications.index') }}?filter=${currentFilter}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                console.log('Notifications updated successfully');
                // Update notification counts
                updateNotificationCounts();
            }
        })
        .catch(error => {
            console.error('Error updating notifications:', error);
            // Only reload as fallback if there's an error
            const timestamp = new Date().getTime();
            window.location.href = `{{ route('notifications.index') }}?filter=${currentFilter}&t=${timestamp}`;
        });
    }// Enhanced update notification counts in sidebar with animations
    function updateNotificationCounts() {
        fetch('{{ route("notifications.counts") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update all notification badges with enhanced animations
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

    // Enhanced badge update function with smooth animations
    function updateBadgeWithAnimation(badge, count, type = 'default') {
        if (!badge) return;
        
        const currentCount = parseInt(badge.textContent) || 0;
        const newCount = parseInt(count) || 0;
        
        // If count changed, add update animation
        if (currentCount !== newCount) {
            badge.classList.add('updating');
            
            setTimeout(() => {
                if (newCount > 0) {
                    badge.textContent = newCount;
                    badge.style.display = 'inline-flex';
                    
                    // Add type-specific classes
                    badge.classList.remove('approved', 'denied', 'total', 'pending', 'released');
                    if (type === 'approved') badge.classList.add('approved');
                    else if (type === 'denied') badge.classList.add('denied');
                    else if (type === 'total') badge.classList.add('total');
                    
                    // Add pulse animation for new notifications
                    if (newCount > currentCount) {
                        badge.classList.add('pulse');
                        setTimeout(() => badge.classList.remove('pulse'), 2000);
                    }
                } else {
                    badge.classList.add('hiding');
                    setTimeout(() => {
                        badge.style.display = 'none';
                        badge.classList.remove('hiding');
                    }, 400);
                }
                
                badge.classList.remove('updating');
            }, 300);
        }
    }

    // Clear page notifications function (from other pages)
    function clearPageNotifications(page) {
        // This function can be called when navigating to specific notification pages
        console.log('Clearing notifications for:', page);
    }

    // Logout modal functions
    function showLogoutModal() {
        const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
        modal.show();
    }

    function confirmLogout() {
        document.getElementById('logout-form').submit();
    }

    // Update empty message based on current filter    function updateEmptyMessage(filter, matchCount) {
        // Find our table container first
        let tableContainer = document.querySelector('.notifications-table-container');
        if (!tableContainer) {
            // If container doesn't exist, create it in the notifications container
            const notificationsContainer = document.querySelector('.modern-notifications-container');
            if (notificationsContainer) {
                tableContainer = document.createElement('div');
                tableContainer.className = 'notifications-table-container';
                notificationsContainer.appendChild(tableContainer);
            } else {
                console.error('Notifications container not found');
                return;
            }
        }
        
        // Now find or create the empty message container
        let emptyContainer = document.querySelector('.empty-notifications');
        
        if (matchCount === 0) {
            // Create empty container if it doesn't exist
            if (!emptyContainer) {
                emptyContainer = document.createElement('div');
                emptyContainer.className = 'empty-notifications';
                tableContainer.appendChild(emptyContainer);
            }
            
            // Show appropriate message based on filter
            let message = 'No notifications found';
            let subMessage = 'You\'ll see notifications here when there are updates on your requests.';
            
            switch(filter) {
                case 'unread':
                    message = 'No unread notifications';
                    subMessage = 'All your notifications have been read.';
                    break;
                case 'approved':
                    message = 'No approved request notifications';
                    subMessage = 'There are no approved requests to display currently.';
                    break;
                case 'denied':
                    message = 'No denied request notifications';
                    subMessage = 'There are no denied requests to display currently.';
                    break;
            }
            
            emptyContainer.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x mb-3 text-muted"></i>
                    <h5>${message}</h5>
                    <p class="text-muted">${subMessage}</p>
                </div>
            `;
            emptyContainer.style.display = 'block';
        } else if (emptyContainer) {
            // Hide empty message if we have notifications
            emptyContainer.style.display = 'none';
        }    }

    // Listen for notification updates from other parts of the application
    document.addEventListener('notificationsUpdated', function(event) {
        console.log('Received notificationsUpdated event:', event.detail);
        if (event.detail && event.detail.counts) {
            updateBadgeWithAnimation(
                document.querySelector('#approvedBadge'), 
                event.detail.counts.approved, 
                'approved'
            );
            
            updateBadgeWithAnimation(
                document.querySelector('#deniedBadge'), 
                event.detail.counts.denied, 
                'denied'
            );
            
            updateBadgeWithAnimation(
                document.querySelector('#totalUnreadBadge'), 
                event.detail.counts.total_unread
            );
        }
        
        // Only refresh the list if we're on the notifications page
        if (window.location.pathname.includes('/notifications')) {
            refreshNotifications(true);
        }
    });
</script>

<script>
// Additional JavaScript for table functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded - initializing notification page features');
    
    // Check for CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found in page - notifications will fail');
        showTableAlert('Warning: CSRF token not found. Page may not function correctly.', 'warning');
    } else {
        console.log('CSRF token found:', csrfToken.getAttribute('content').substring(0, 10) + '...');
    }
    
    // Add backup event listeners for buttons in case onclick doesn't work
    setupBackupEventListeners();
    
    // Check if we need to reload the page after a notification action
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('reload_complete')) {
        showTableAlert('Operation completed successfully!', 'success');
        // Remove the parameter from URL without refreshing
        const newUrl = window.location.pathname + window.location.hash;
        history.replaceState(null, '', newUrl);
    }
    
    // Initialize checkbox functionality for the table
    initializeTableCheckboxes();
    
    // Initialize notification counters
    updateNotificationCounts();
    
    // Prevent form submissions from refreshing the page
    document.querySelectorAll('form').forEach(form => {
        if (!form.hasAttribute('data-allow-submit')) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
            });
        }
    });
    
    // Log initialization
    console.log('Notification page initialized successfully');
});

function setupBackupEventListeners() {
    console.log('Setting up backup event listeners for notification buttons...');
    
    // Backup event listeners for mark as read buttons
    document.querySelectorAll('.btn-outline-success').forEach(button => {
        // Remove existing event listeners by cloning
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        // Add new event listener
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Extract notification ID from onclick attribute or closest row
            const row = this.closest('[data-notification-id]');
            if (row) {
                const notificationId = row.getAttribute('data-notification-id');
                console.log('Backup handler: marking notification as read:', notificationId);
                markAsRead(notificationId);
            }
        });
    });
    
    // Backup event listeners for delete buttons
    document.querySelectorAll('.btn-outline-danger').forEach(button => {
        // Remove existing event listeners by cloning
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        // Add new event listener
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Extract notification ID from onclick attribute or closest row
            const row = this.closest('[data-notification-id]');
            if (row) {
                const notificationId = row.getAttribute('data-notification-id');
                console.log('Backup handler: deleting notification:', notificationId);
                deleteNotification(notificationId);
            }
        });
    });
    
    // Backup event listener for mark all as read button
    document.querySelectorAll('[onclick*="markAllAsRead"]').forEach(button => {
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Backup handler: marking all notifications as read');
            markAllAsRead();
        });
    });
    
    console.log('Backup event listeners set up successfully');
}

function initializeTableCheckboxes() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const notificationCheckboxes = document.querySelectorAll('.notification-checkbox');
    const selectedCountEl = document.getElementById('selectedCount');
    const markSelectedBtn = document.getElementById('markSelectedBtn');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    
    console.log('Initializing table checkboxes:', {
        selectAllCheckbox: !!selectAllCheckbox,
        notificationCheckboxes: notificationCheckboxes.length,
        selectedCountEl: !!selectedCountEl,
        markSelectedBtn: !!markSelectedBtn,
        deleteSelectedBtn: !!deleteSelectedBtn
    });
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            notificationCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    notificationCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('.notification-checkbox:checked').length;
        if (selectedCountEl) selectedCountEl.textContent = selectedCount;
        
        const hasSelection = selectedCount > 0;
        if (markSelectedBtn) markSelectedBtn.disabled = !hasSelection;
        if (deleteSelectedBtn) deleteSelectedBtn.disabled = !hasSelection;
        
        // Update select all checkbox state
        if (selectAllCheckbox) {
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < notificationCheckboxes.length;
            selectAllCheckbox.checked = selectedCount === notificationCheckboxes.length && selectedCount > 0;
        }
    }
}

// Override existing problematic functions
function markAllAsRead() {
    if (!confirm('Are you sure you want to mark all notifications as read?')) {
        return;
    }
    
    // Show loading indicator
    const markAllBtn = document.querySelector('.btn[onclick*="markAllAsRead()"]');
    const originalBtnContent = markAllBtn ? markAllBtn.innerHTML : null;
    
    if (markAllBtn) {
        markAllBtn.disabled = true;
        markAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }
    
    // Get the current office filter value for SuperAdmin (office segregation fix)
    const officeFilter = document.getElementById('office_filter');
    const selectedOffice = officeFilter ? officeFilter.value : 'all';
    
    // Create form data with office parameter
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    
    // Add office parameter if specific office is selected
    if (selectedOffice && selectedOffice !== 'all') {
        formData.append('office', selectedOffice);
    }
    
    // Make fetch request
    fetch('{{ route("notifications.mark.all.read") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server returned ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update all notification rows without page refresh
            document.querySelectorAll('.notification-row').forEach(row => {
                row.classList.remove('table-warning');
                row.classList.add('table-light');
                
                const statusBadge = row.querySelector('td:nth-child(2) .badge');
                if (statusBadge) {
                    statusBadge.className = 'badge bg-secondary';
                    statusBadge.innerHTML = '<i class="fas fa-check"></i> Read';
                }
                
                // Find and hide mark as read buttons
                const markButton = row.querySelector('.btn-outline-success');
                if (markButton) {
                    markButton.style.display = 'none';  // Hide instead of remove
                }
            });
            
            showTableAlert('All notifications marked as read', 'success');
            updateNotificationCounts();
        } else {
            showTableAlert('Error marking all notifications as read. Trying alternative method...', 'warning');
            // Fall back to page reload if the AJAX request wasn't successful
            window.location.href = '{{ route("notifications.index") }}?notif_action=markallread';
        }
        
        // Reset button
        if (markAllBtn) {
            markAllBtn.disabled = false;
            markAllBtn.innerHTML = originalBtnContent;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showTableAlert('Network error. Trying alternative method...', 'warning');
        
        // Reset button
        if (markAllBtn) {
            markAllBtn.disabled = false;
            markAllBtn.innerHTML = originalBtnContent;
        }
        
        // Fall back to page reload
        window.location.href = '{{ route("notifications.index") }}?notif_action=markallread';
    });
}    function refreshNotifications(skipAlert = false) {
        // Update notification counts dynamically
        updateNotificationCounts();
        
        // Get current filter
        const currentFilter = '{{ $filter }}';
        
        // Get last refresh timestamp if available
        let lastRefresh = '';
        const notificationTable = document.querySelector('.notification-table');
        if (notificationTable && notificationTable.dataset.lastRefresh) {
            lastRefresh = notificationTable.dataset.lastRefresh;
        }
        
        // Show loading indicator
        if (!skipAlert) {
            showTableAlert('Refreshing notifications...', 'info');
        }
        
        // Fetch fresh notification data and update the notification table
        fetch(`{{ route('notifications.index') }}?filter=${currentFilter}&json=true&since=${lastRefresh}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Failed to fetch notifications');
        })
        .then(data => {            if (data.html) {
                // Replace only the notification table content, not the entire container
                const container = document.querySelector('.modern-notifications-container');
                if (container) {
                    // Create a temporary div to hold the new HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    
                    // Find the table-responsive div or create a notification-specific container
                    const tableContent = tempDiv.querySelector('.table-responsive') || tempDiv.firstElementChild;
                    
                    // Clear existing table content and append the new one
                    // First, find or create our table container
                    let tableContainer = container.querySelector('.notifications-table-container');
                    if (!tableContainer) {
                        // If container doesn't exist, create it
                        tableContainer = document.createElement('div');
                        tableContainer.className = 'notifications-table-container';
                        container.appendChild(tableContainer);
                    }
                    
                    // Update only the table content
                    tableContainer.innerHTML = '';
                    if (tableContent) {
                        tableContainer.appendChild(tableContent);
                    } else if (tempDiv.querySelector('.empty-notifications')) {
                        tableContainer.appendChild(tempDiv.querySelector('.empty-notifications'));
                    }
                    
                    // Re-initialize any event handlers on the new content
                    initializeTableCheckboxes();
                    
                    if (!skipAlert) {
                        showTableAlert('Notifications refreshed successfully', 'success');
                    }
                    
                    // If we have no notifications, show the empty message
                    const notificationItems = container.querySelectorAll('.notification-item');
                    if (notificationItems.length === 0) {
                        updateEmptyMessage(currentFilter, 0);
                    }
                }
            } else {
                if (!skipAlert) {
                    showTableAlert('Refreshed notification counts', 'success');
                }
            }
        })
        .catch(error => {
            console.error('Error updating notifications:', error);
            if (!skipAlert) {
                showTableAlert('Error refreshing notifications. Please try again.', 'error');
            }
        });
    }    function forceRefresh() {
        // Show loading indicator
        showTableAlert('Forcing notification refresh...', 'info');
        
        // Call refreshNotifications with true to skip additional alerts
        refreshNotifications(true);
    }

function markSelectedAsRead() {
    const selectedIds = Array.from(document.querySelectorAll('.notification-checkbox:checked'))
        .map(checkbox => checkbox.value);
    
    if (selectedIds.length === 0) {
        showTableAlert('Please select notifications to mark as read', 'warning');
        return;
    }
    
    // Create form data
    const formData = new FormData();
    selectedIds.forEach(id => {
        formData.append('notification_ids[]', id);
    });
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch('{{ route("notifications.mark.read") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update all selected rows
            selectedIds.forEach(id => {
                const row = document.querySelector(`[data-notification-id="${id}"]`);
                if (row) {
                    row.classList.remove('table-warning');
                    row.classList.add('table-light');
                    
                    const statusBadge = row.querySelector('td:nth-child(2) .badge');
                    if (statusBadge) {
                        statusBadge.className = 'badge bg-secondary';
                        statusBadge.innerHTML = '<i class="fas fa-check"></i> Read';
                    }
                    
                    const markButton = row.querySelector('.btn-outline-success');
                    if (markButton) {
                        markButton.remove();
                    }
                    
                    const checkbox = row.querySelector('.notification-checkbox');
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                }
            });
            
            // Reset checkboxes and buttons
            const selectAllCheckbox = document.getElementById('selectAll');
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            
            document.getElementById('selectedCount').textContent = '0';
            document.getElementById('markSelectedBtn').disabled = true;
            document.getElementById('deleteSelectedBtn').disabled = true;
            
            showTableAlert(`${selectedIds.length} notifications marked as read`, 'success');
            updateNotificationCounts();
        } else {
            showTableAlert('Error marking notifications as read', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showTableAlert('Error marking notifications as read', 'error');
    });
}

function deleteSelected() {
    const selectedIds = Array.from(document.querySelectorAll('.notification-checkbox:checked'))
        .map(checkbox => checkbox.value);
    
    if (selectedIds.length === 0) {
        showTableAlert('Please select notifications to delete', 'warning');
        return;
    }
    
    if (!confirm(`Are you sure you want to delete ${selectedIds.length} notifications?`)) {
        return;
    }
    
    // Show loading state on delete buttons
    const deleteButtons = document.querySelectorAll('.notification-checkbox:checked')
        .forEach(checkbox => {
            const row = checkbox.closest('tr');
            const deleteBtn = row.querySelector('.btn-outline-danger');
            if (deleteBtn) {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            }
        });
    
    Promise.all(selectedIds.map(id => {
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        return fetch(`{{ url('/notifications') }}/${id}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
    ))
    .then(responses => Promise.all(responses.map(r => r.json())))
    .then(results => {
        const successCount = results.filter(r => r.success).length;
        
        if (successCount > 0) {
            // Remove successful deletions from DOM
            selectedIds.forEach(id => {
                const row = document.querySelector(`[data-notification-id="${id}"]`);
                if (row) {
                    row.remove();
                }
            });
            
            showTableAlert(`${successCount} notifications deleted`, 'success');
            updateNotificationCounts();
        }
        
        if (successCount < selectedIds.length) {
            showTableAlert(`${selectedIds.length - successCount} notifications could not be deleted`, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showTableAlert('Error deleting notifications', 'error');
    });
}

// Senior Developer Implementation: Smart Request View Function
function viewRelatedRequest(action, office, requestId) {
    console.log('🔍 View request triggered:', { action, office, requestId });
    
    // Get current user role for smart routing
    const userRole = '{{ strtolower(trim(Auth::user()->role)) }}';
    const currentUserOffice = '{{ Auth::user()->Office }}';
    
    console.log('👤 User context:', { userRole, currentUserOffice });
    
    // Show loading message
    showTableAlert('Redirecting to request...', 'info');
    
    try {
        let redirectUrl = '';
        
        // Smart routing based on user role and notification action
        if (userRole === 'superadmin') {
            // SuperAdmin Logic: Always goes to appropriate management page
            switch (action) {
                case 'request_pending':
                    redirectUrl = '{{ route("requests.pending") }}';
                    console.log('🔄 SuperAdmin -> Pending page');
                    break;
                case 'request_approved':
                    redirectUrl = '{{ route("requests.approved") }}';
                    console.log('✅ SuperAdmin -> Approved page');
                    break;
                case 'request_denied':
                case 'request_disapproved':
                    redirectUrl = '{{ route("requests.disapproved") }}';
                    console.log('❌ SuperAdmin -> Disapproved page');
                    break;
                default:
                    redirectUrl = '{{ route("requests.pending") }}';
                    console.log('🔄 SuperAdmin -> Default pending page');
            }
        } else {
            // Regular User Logic: Goes to their appropriate view based on status
            switch (action) {
                case 'request_pending':
                    // User can see their pending requests
                    redirectUrl = '{{ route("requests.pending") }}';
                    console.log('🔄 User -> Their pending requests');
                    break;
                case 'request_approved':
                    // User sees their approved requests
                    redirectUrl = '{{ route("requests.approved") }}';
                    console.log('✅ User -> Their approved requests');
                    break;
                case 'request_denied':
                case 'request_disapproved':
                    // User sees their disapproved requests
                    redirectUrl = '{{ route("requests.disapproved") }}';
                    console.log('❌ User -> Their disapproved requests');
                    break;
                default:
                    // Default to pending for users
                    redirectUrl = '{{ route("requests.pending") }}';
                    console.log('🔄 User -> Default pending page');
            }
        }
        
        // Add request ID as URL parameter if available for specific filtering
        if (requestId && requestId !== 'null' && requestId !== '') {
            const separator = redirectUrl.includes('?') ? '&' : '?';
            redirectUrl += `${separator}request_id=${requestId}`;
            console.log('🎯 Added request ID filter:', requestId);
        }
        
        // Add office filter for SuperAdmin
        if (userRole === 'superadmin' && office) {
            const separator = redirectUrl.includes('?') ? '&' : '?';
            redirectUrl += `${separator}office=${encodeURIComponent(office)}`;
            console.log('🏢 Added office filter:', office);
        }
        
        console.log('🚀 Redirecting to:', redirectUrl);
        
        // Perform the redirect
        window.location.href = redirectUrl;
        
    } catch (error) {
        console.error('❌ Error during request view:', error);
        showTableAlert('Error redirecting to request', 'error');
    }
}

function deleteNotification(notificationId) {
    if (!confirm('Are you sure you want to delete this notification?')) {
        return;
    }
    
    console.log('Deleting notification:', notificationId);
    
    const notificationRow = document.querySelector(`[data-notification-id="${notificationId}"]`);
    const deleteBtn = notificationRow?.querySelector('.btn-outline-danger');
    
    // Show loading state
    if (deleteBtn) {
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('CSRF token not found');
        showTableAlert('Error: CSRF token not found', 'error');
        if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
        }
        return;
    }
    
    // Create form data
    const formData = new FormData();
    formData.append('_method', 'DELETE');
    formData.append('_token', csrfToken);
    
    // Make the request using XMLHttpRequest for better compatibility
    const xhr = new XMLHttpRequest();
    xhr.open('POST', `{{ url('/notifications') }}/${notificationId}`, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            let response;
            try {
                response = JSON.parse(xhr.responseText);
            } catch (e) {
                console.error('Invalid JSON response');
                response = { success: false, message: 'Invalid response format' };
            }
            
            if (response.success) {
                console.log('Notification deleted successfully');
                
                // Remove the row from DOM with animation
                if (notificationRow) {
                    notificationRow.style.transition = 'opacity 0.3s ease';
                    notificationRow.style.opacity = '0';
                    setTimeout(() => {
                        notificationRow.remove();
                    }, 300);
                }
                
                showTableAlert('Notification deleted successfully', 'success');
                updateNotificationCounts();
            } else {
                console.error('Delete failed:', response.message);
                showTableAlert(response.message || 'Error deleting notification', 'error');
                
                // Restore button state
                if (deleteBtn) {
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                }
            }
        } else {
            console.error('HTTP error:', xhr.status);
            showTableAlert('Error deleting notification. Trying alternative method...', 'warning');
            
            // Restore button state
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
            }
            
            // Fallback to page reload with action
            window.location.href = '{{ route("notifications.index") }}?notif_action=delete&id=' + notificationId;
        }
    };
    
    xhr.onerror = function() {
        console.error('Network error during deletion');
        showTableAlert('Network error. Trying alternative method...', 'warning');
        
        // Restore button state
        if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
        }
        
        // Fallback to page reload with action
        window.location.href = '{{ route("notifications.index") }}?notif_action=delete&id=' + notificationId;
    };
    
    // Send the request
    xhr.send(formData);
}

function viewNotification(notificationId) {
    // Show notification details in a modal or expand view
    console.log('Viewing notification:', notificationId);
    const fullMessageDiv = document.getElementById(`full-message-${notificationId}`);
    if (fullMessageDiv) {
        fullMessageDiv.classList.toggle('d-none');
    }
}

function showFullMessage(notificationId) {
    const fullMessageDiv = document.getElementById(`full-message-${notificationId}`);
    if (fullMessageDiv) {
        fullMessageDiv.classList.remove('d-none');
    }
}

// Function to show popup notifications (uses new popup system)
function showTableAlert(message, type = 'info') {
    // Map old types to new types
    const typeMap = {
        'danger': 'error',
        'error': 'error',
        'warning': 'warning',
        'success': 'success',
        'info': 'info'
    };
    
    const mappedType = typeMap[type] || 'info';
    
    // Use the new popup notification system
    if (typeof showPopupNotification === 'function') {
        showPopupNotification(message, mappedType);
    } else {
        // Fallback to console if popup system not loaded
        console.log(`Notification: ${message} (${mappedType})`);
    }
}

// Function to reload the page with notification action completed
function reloadPageAfterAction(delay = 1000) {
    setTimeout(() => {
        const url = new URL(window.location);
        url.searchParams.set('reload_complete', 'true');
        window.location = url;
    }, delay);
}
    const row = document.querySelector(`[data-notification-id="${notificationId}"]`);
    if (row) {
        const message = row.querySelector('.notification-message-cell').textContent.trim();
        const office = row.querySelector('td:nth-child(5) .badge').textContent;
        const userId = row.querySelector('td:nth-child(6)').textContent.trim();
        const dateTime = row.querySelector('.date-time-cell').textContent.replace(/\s+/g, ' ').trim();
        
        alert(`Notification Details:\n\nMessage: ${message}\nOffice: ${office}\nUser ID: ${userId}\nDate/Time: ${dateTime}`);
    }
}

function showFullMessage(notificationId) {
    const fullMessageEl = document.getElementById(`full-message-${notificationId}`);
    const messageCell = fullMessageEl.previousElementSibling;
    
    if (fullMessageEl.classList.contains('d-none')) {
        fullMessageEl.classList.remove('d-none');
        messageCell.classList.add('d-none');
    } else {
        fullMessageEl.classList.add('d-none');
        messageCell.classList.remove('d-none');
    }
}

function showTableAlert(message, type = 'info') {
    // Use the global popup notification function
    if (typeof showPopupNotification === 'function') {
        showPopupNotification(message, type);
    }
}
</script>

<!-- User Settings Modal for Password Change -->
<div class="modal fade" id="userSettingsModal" tabindex="-1" aria-labelledby="userSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content modern-modal">
            <div class="modal-header" style="background: #800020 !important; color: white !important;">
                <h5 class="modal-title" id="userSettingsModalLabel">
                    <i class="fas fa-user-cog me-2"></i>User Settings - {{ Auth::user()->name }}
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

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content modern-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutConfirmModalLabel">
                    <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="color: #333; font-weight: 500; font-size: 16px; text-align: center;">Are you sure you want to logout from the system?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmLogoutBtn" onclick="confirmLogout()">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Logout confirmation function
    function confirmLogout() {
        const btn = document.getElementById('confirmLogoutBtn');
        const spinner = btn.querySelector('.spinner-border');
        const icon = btn.querySelector('.fas');
        
        // Show loading state
        spinner.classList.remove('d-none');
        icon.classList.add('d-none');
        btn.disabled = true;
        
        // Submit logout form
        document.getElementById('logout-form').submit();
    }
</script>

@endsection
