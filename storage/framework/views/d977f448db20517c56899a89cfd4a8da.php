<!DOCTYPE html>
<html lang="en">
<script>
    /**
     * CRITICAL: Load theme BEFORE page renders to prevent flicker
     * This runs IMMEDIATELY - no waiting for DOMContentLoaded
     * NOW: Only applies dark mode if explicitly saved by user
     */
    (function() {
        const savedTheme = localStorage.getItem('astral7-theme-preference');
        const html = document.documentElement;
        
        // Only apply dark mode if user has explicitly chosen it
        // Default is light mode
        if (savedTheme === 'dark') {
            html.setAttribute('data-theme', 'dark');
            html.style.transition = 'none';
            html.style.backgroundColor = '#1a202c';
            html.style.color = '#f7fafc';
            html.classList.add('dark-theme-loading');
        } else {
            // Explicitly set light mode
            html.setAttribute('data-theme', 'light');
            html.style.transition = 'none';
            html.style.backgroundColor = '#ffffff';
            html.style.color = '#212529';
        }
        
        // Re-enable transitions after page loads
        window.addEventListener('load', function() {
            html.style.transition = '';
            html.classList.remove('dark-theme-loading');
        });
    })();
</script>
<style>
    /* Prevent white flash on page load */
    html.dark-theme-loading {
        background-color: #1a202c !important;
    }
    html.dark-theme-loading body {
        background-color: #1a202c !important;
        color: #f7fafc !important;
    }
    /* Force ALL white backgrounds to dark immediately */
    html.dark-theme-loading * {
        transition: none !important;
    }
    html.dark-theme-loading table,
    html.dark-theme-loading .table,
    html.dark-theme-loading .table-responsive,
    html.dark-theme-loading tbody,
    html.dark-theme-loading tr,
    html.dark-theme-loading td {
        background-color: #2d3748 !important;
        color: #f7fafc !important;
    }
    html.dark-theme-loading thead,
    html.dark-theme-loading th {
        background-color: #1a202c !important;
        color: #f7fafc !important;
    }
    html.dark-theme-loading .modal-content,
    html.dark-theme-loading .modal-body {
        background-color: #2d3748 !important;
        color: #f7fafc !important;
    }
    html.dark-theme-loading .card,
    html.dark-theme-loading .search-filter {
        background-color: #2d3748 !important;
        color: #f7fafc !important;
    }
</style>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'PUPT_SMIS'); ?></title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('Pup/Astral.png')); ?>">    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Dark Mode CSS - Load FIRST for flicker prevention -->
    <link rel="stylesheet" href="<?php echo e(asset('css/dark-mode.css')); ?>">
    <!-- Universal Sidebar Scrollbar System - Applied to ALL pages -->
    <link rel="stylesheet" href="<?php echo e(asset('css/universal-sidebar-scrollbar.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/sidebar-fix.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/notification-badges.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/admin-notification-badges.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/admin-notification-fix.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/popup-notifications.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/dark-mode-switch.css')); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <!-- Universal Sidebar JavaScript -->
    <script src="<?php echo e(asset('js/sidebar-fix.js')); ?>" defer></script>
    <!-- Enhanced notification system -->
    <script src="<?php echo e(asset('js/enhanced-notifications.js')); ?>" defer></script>
    <!-- Modern Popup Notifications -->
    <script src="<?php echo e(asset('js/popup-notifications.js')); ?>" defer></script>
    <!-- Theme Toggle System - Load early -->
    <script src="<?php echo e(asset('js/theme-toggle-system.js')); ?>"></script>
</head>
<body class="bg-light">
    <div class="container">
        <?php echo $__env->yieldContent('content'); ?>    </div>
    
    <!-- Bootstrap JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Notification update listener -->
    <script>
    document.addEventListener('notificationsUpdated', function(event) {
        console.log('Notification update event received', event.detail);
        
        // Update notification badges if the function exists
        if (typeof updateNotificationCounts === 'function') {
            updateNotificationCounts();
        }
        
        // Update sidebar badges if needed
        const notificationBadges = document.querySelectorAll('.notification-badge');
        if (event.detail && event.detail.counts) {
            // Update approved notification badge
            const approvedBadges = document.querySelectorAll('a[href*="approved"] .notification-badge');
            approvedBadges.forEach(badge => {
                if (badge) badge.textContent = event.detail.counts.approved || 0;
                if (parseInt(event.detail.counts.approved) === 0) badge.style.display = 'none';
                else badge.style.display = 'inline-block';
            });
            
            // Update denied notification badge
            const deniedBadges = document.querySelectorAll('a[href*="disapproved"] .notification-badge');
            deniedBadges.forEach(badge => {
                if (badge) badge.textContent = event.detail.counts.denied || 0;
                if (parseInt(event.detail.counts.denied) === 0) badge.style.display = 'none';
                else badge.style.display = 'inline-block';
            });
            
            // Update total notification badge
            const totalBadges = document.querySelectorAll('a[href*="notifications"] .notification-badge');
            totalBadges.forEach(badge => {
                if (badge) badge.textContent = event.detail.counts.total_unread || 0;
                if (parseInt(event.detail.counts.total_unread) === 0) badge.style.display = 'none';
                else badge.style.display = 'inline-block';
            });
        }
    });
    
    // Check for flash script
    <?php if(session('script')): ?>
        <?php echo session('script'); ?>

    <?php endif; ?>
    </script>
</body>
</html> 

<!--admin-->
<?php /**PATH D:\Web Dev 3-1\Activity 1_Dela Cruz\SMIS41\resources\views/layout/app.blade.php ENDPATH**/ ?>