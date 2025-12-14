

<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/admin.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/modern-admin.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/admin-notification-badges.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dark-mode.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dark-mode-switch.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/admin-notification-fix.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/inventory-print.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/user-settings-dropdown.css')); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Unit input-group styling */
        .input-group .btn-group { display: inline-flex; align-items: stretch; }
        #addUnitButton { border-top-left-radius: 0; border-bottom-left-radius: 0; }
        #deleteUnitInlineBtn { border-left: 1px solid rgba(0,0,0,0.08); background: #fff; color: #c53030; }
        #deleteUnitInlineBtn:hover { background: #fee2e2; color: #9b2c2c; }
        /* Make the select and buttons compact on small screens */
        @media (max-width: 575px) {
            .input-group .form-control { min-width: 110px; }
            .input-group .btn { padding: .4rem .6rem; font-size: .85rem; }
        }

        /* Toast container style for unit actions */
        #unitToastContainer .toast { min-width: 260px; box-shadow: 0 6px 18px rgba(0,0,0,0.12); border-radius: 8px; overflow: hidden; }
        #unitToastContainer .toast .toast-body { font-weight: 600; }
    </style>

<body>
    <button class="toggle-sidebar-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <img src="<?php echo e(asset('Pup/puplogo.png')); ?>" style="width: 60px; height: 60px; padding: 0; margin: 20px auto;" alt="PUP Logo">
            <h3 style="margin-bottom: 20px;">SMIS</h3>
            <a href="<?php echo e(route(Auth::user()->role === 'superadmin' ? 'SuperAdmin.dashboard' : 'Admin.admin')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-home" style="margin-right: 8px;"></i> <span>Home</span>
            </a>
            <a href="<?php echo e(route('usersmanage.index')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-users" style="margin-right: 8px;"></i> <span>User Management</span>
            </a>        
            <a href="<?php echo e(route('notifications.index')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-bell" style="margin-right: 8px;"></i> 
                <span>Notifications</span>
                <span class="notification-badge" style="display: <?php echo e((isset($notificationCounts) && $notificationCounts['total_unread'] > 0) ? 'inline-flex' : 'none'); ?>;">
                    <?php echo e($notificationCounts['total_unread'] ?? 0); ?>

                </span>
                
                <?php if(isset($notificationCounts)): ?>
                    <small style="color: white; font-size: 10px;">(<?php echo e($notificationCounts['total_unread'] ?? 0); ?>)</small>
                <?php else: ?>
                    <small style="color: red; font-size: 10px;">(NO DATA)</small>
                <?php endif; ?>
            </a>
            <a href="#" class="dropdown-btn" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
                <i class="fa fa-caret-down" style="margin-left: auto;"></i>
            </a>
            <div class="dropdown-container" style="margin: 10px auto;">
                
                <?php if(Auth::user()->role === 'SuperAdmin' && Route::has('requests.create')): ?>
                <a href="<?php echo e(route('requests.create')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-plus-circle" style="margin-right: 8px;"></i>
                    <span>Create New Request</span>
                </a>
                <?php endif; ?>
                <a href="<?php echo e(route('requests.pending')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-clock" style="margin-right: 8px;"></i> 
                    <span>Pending</span>
                </a>            
                <a href="<?php echo e(route('requests.approved')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> 
                    <span>Approved</span>
                </a>
                <a href="<?php echo e(route('requests.released')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-truck" style="margin-right: 8px;"></i> 
                    <span>Released</span>
                </a>          
                <a href="<?php echo e(route('requests.disapproved')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-times-circle" style="margin-right: 8px;"></i> 
                    <span>Disapproved</span>
                </a>
            </div>
            <a href="<?php echo e(route('reports')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-chart-line" style="margin-right: 8px;"></i> <span>Reports</span>
            </a>
            <a href="<?php echo e(route('help.support')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-question-circle" style="margin-right: 8px;"></i> <span>Help & Support</span>
            </a>
            <!-- NEW: Rate / Questionnaire Button (replace href with your Google Form link) -->
            <a id="rateSystemBtn" href="https://forms.gle/12SD5ZjPTXbsgqzC7" target="_blank" rel="noopener" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px; background: rgba(255,255,255,0.08); border-radius:5px;">
                <i class="fas fa-star-half-alt" style="margin-right: 8px;"></i> <span>Rate Our System</span>
            </a>
        </div>
    </div>

    <!-- Display Validation Errors -->
    <?php if($errors->any()): ?>
      <div class="alert alert-danger modern-alert">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <ul class="mb-0">
              <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li><?php echo e($error); ?></li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </ul>
      </div>
    <?php endif; ?>

    <!-- Display Success Messages -->
    <?php if(session('success')): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modern-modal">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">
                        <i class="fas fa-check-circle text-white me-2"></i>Success
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                    <p class="alert alert-success" style="font-size: 1.1rem; font-weight: 500; color: #155724; text-align: center;">
                        <?php echo e(session('success')); ?>

                    </p>
                    <p class="text-muted text-center mt-2" style="font-size: 0.9rem;">
                        <i class="fas fa-clock me-1"></i>This message will automatically close in a few seconds
                    </p>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // CRITICAL FIX: Clean up any leftover modal artifacts before showing success modal
            function cleanupModalArtifacts() {
                // Remove all existing modal backdrops
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                
                // Remove modal-open class from body
                document.body.classList.remove('modal-open');
                
                // Reset body padding
                document.body.style.removeProperty('padding-right');
                document.body.style.removeProperty('overflow');
                
                // Reset any modal states
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                    modal.setAttribute('aria-hidden', 'true');
                    modal.removeAttribute('aria-modal');
                });
            }

            // Clean up before showing success modal
            cleanupModalArtifacts();
            
            // Short delay to ensure cleanup is complete
            setTimeout(function() {
                var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
                
                // Auto-hide after 3 seconds
                setTimeout(function () {
                    successModal.hide();
                    // Clean up again after hiding success modal
                    setTimeout(cleanupModalArtifacts, 300);
                }, 3000);
            }, 100);
        });
    </script>
    
    <!-- SENIOR DEVELOPER BADGE SYSTEM - BULLETPROOF SOLUTION -->
    <link href="<?php echo e(asset('css/admin-notification-fix.css')); ?>" rel="stylesheet">
    <script src="<?php echo e(asset('js/badge-initialization.js')); ?>"></script>
    <script src="<?php echo e(asset('js/enhanced-admin-notifications.js')); ?>"></script>
    <!-- Legacy fallback scripts -->
    <script src="<?php echo e(asset('js/simple-badge-manager.js')); ?>"></script>
    <script>
    // Manual override functions for testing
    window.testBadges = function() {
        window.setBadgeCounts({
            pending: 3,
            approved: 6, 
            released: 6,
            disapproved: 6
        });
    };
    
    window.clearBadges = function() {
        // SENIOR DEVELOPER FIX: Don't actually clear badges, just reset their preservation state
        console.log('Clear badges called - resetting preservation state only');
        
        document.querySelectorAll('.notification-badge').forEach(badge => {
            // Remove preservation attributes to allow hiding if truly empty
            badge.removeAttribute('data-has-content');
            badge.removeAttribute('data-preserved-count');
            
            // Only hide if the badge text is actually 0 or empty
            const count = parseInt(badge.textContent) || 0;
            if (count === 0) {
                badge.style.display = 'none';
            }
        });
    };

    // Emergency restore function for debugging
    window.emergencyRestoreBadges = function() {
        console.log('EMERGENCY RESTORE - Forcing all badges visible with test data');
        
        const testCounts = {
            pending: 18,
            approved: 4,
            released: 0,
            disapproved: 0,
            total_unread: 25
        };

        // Apply test counts to badges
        if (window.badgePreservationManager) {
            window.badgePreservationManager.applyCountsToBadges(testCounts);
        }

        // Force visibility of all badges with content
        document.querySelectorAll('.notification-badge').forEach(badge => {
            const count = parseInt(badge.textContent) || 0;
            if (count > 0) {
                badge.style.display = 'inline-flex';
                badge.style.visibility = 'visible';
                badge.style.opacity = '1';
                badge.setAttribute('data-has-content', 'true');
            }
        });
    };
    </script>
    <?php endif; ?>

    <!-- User Settings Dropdown - Fixed Position with Manual Implementation -->
    <div class="user-settings-dropdown">
        <button class="btn btn-sm btn-dark rounded-circle" id="userSettingsButton" style="width: 38px; height: 38px;" aria-expanded="false" aria-label="User settings and profile" title="Click for user settings">
            <i class="fas fa-user"></i>
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
            
            <!-- Theme Toggle Script -->
            <script src="<?php echo e(asset('js/theme-toggle.js')); ?>"></script>
        </div>
    </div>
    
    <!-- Logout Form (hidden) -->
    <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
        <?php echo csrf_field(); ?>
    </form>
    
    <!-- Manual dropdown implementation that doesn't rely on Bootstrap -->
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
                userButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    // Visual feedback
                    this.classList.add('btn-clicked');
                    setTimeout(() => this.classList.remove('btn-clicked'), 300);
                    
                    // Toggle the menu
                    userMenu.classList.toggle('show');
                    this.setAttribute('aria-expanded', userMenu.classList.contains('show'));
                });
                
                // Close dropdown when clicking elsewhere
                document.addEventListener('click', function(e) {
                    if (!userButton.contains(e.target) && !userMenu.contains(e.target)) {
                        userMenu.classList.remove('show');
                        userButton.setAttribute('aria-expanded', 'false');
                    }
                });
                
                // Add click handler to dropdown items
                userMenu.querySelectorAll('.dropdown-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        // Add visual feedback
                        this.classList.add('item-active');
                        setTimeout(() => this.classList.remove('item-active'), 200);
                        
                        const action = this.textContent.trim().toLowerCase();
                        console.log(`${action} menu item clicked`);
                        
                        // Close the dropdown
                        userMenu.classList.remove('show');
                        userButton.setAttribute('aria-expanded', 'false');
                    });
                });
                
                // Try bootstrap way as fallback
                try {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                        new bootstrap.Dropdown(userButton);
                    }
                } catch (e) {
                    console.log('Using manual dropdown implementation');
                }
            }
        });
    </script>
    
    <div class="main-content">
        <div class="dashboard-header modern-header">
            <!-- The global dark-mode script will convert #800020 to deep maroon to match the system brand in dark mode -->
            <div class="header-content" style="color: white !important; padding: 0.75rem 1.5rem !important;">
                <div>
                    <h2 class="welcome-text" style="color: #ffffff !important; font-size: 1.5rem !important; margin: 0 !important;">
                        <?php if(Auth::user()->role == 'SuperAdmin'): ?>
                            <i class="fas fa-crown text-warning me-2" style="color: #fbbf24 !important;"></i>Welcome SuperAdmin,
                            <span class="user-name" style="color: #ffffff !important; font-weight: bold !important; background: none !important;"><?php echo e(Auth::user()->First_Name); ?> <?php echo e(Auth::user()->Middle_Initial); ?>. <?php echo e(Auth::user()->Last_Name); ?>!</span>
                        <?php else: ?>
                            <i class="fas fa-user-shield me-2" style="color: #06b6d4 !important;"></i>Welcome Admin,
                            <span class="user-name" style="color: #ffffff !important; font-weight: bold !important; background: none !important;"><?php echo e(Auth::user()->First_Name); ?> <?php echo e(Auth::user()->Middle_Initial); ?>. <?php echo e(Auth::user()->Last_Name); ?>!</span>
                        <?php endif; ?>
                    </h2>
                    <p class="header-subtitle" style="color: white !important; margin: 0.25rem 0 0 0 !important; font-size: 0.9rem !important;">Manage your inventory and track supplies efficiently</p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-content modern-dashboard">
            <!-- Enhanced Dashboard Overview -->
            <div class="row dashboard-overview enhanced-cards">
                <div class="col-md-4">
                    <div class="card bg-primary text-white modern-card" data-aos="fade-up">
                        <div class="card-content">
                            <div class="card-info">
                                <h5><i class="fas fa-box me-2"></i>Total Products</h5>
                                <p class="card-number"><?php echo e($total_products); ?></p>
                                <small class="card-trend">
                                    <i class="fas fa-arrow-up me-1"></i>Active inventory items
                                </small>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white modern-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="card-content">
                            <div class="card-info">
                                <h5><i class="fas fa-warehouse me-2"></i>Total Stock</h5>
                                <p class="card-number"><?php echo e($total_stock); ?></p>
                                <small class="card-trend">
                                    <i class="fas fa-cubes me-1"></i>Available units
                                </small>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white modern-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="card-content">
                            <div class="card-info">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i>Out of Stock</h5>
                                <p class="card-number"><?php echo e($out_of_stock); ?></p>
                                <small class="card-trend">
                                    <i class="fas fa-alert-triangle me-1"></i>Needs restocking
                                </small>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Strategic Chart Section - Only Stock Status Overview -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card modern-card chart-card" data-aos="fade-right">
                        <div class="card-header modern-card-header">
                            <h5 class="card-title">
                                <i class="fas fa-chart-bar me-2 text-primary"></i>Inventory Status Overview
                            </h5>
                            <p class="card-subtitle">Quick view of your current stock levels</p>
                        </div>
                        <div class="card-body">
                            <canvas id="inventoryChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="quick-stats-container" data-aos="fade-left">
                        <div class="card modern-card quick-stat-card">
                            <div class="card-body text-center">
                                <i class="fas fa-percentage text-success mb-3" style="font-size: 2rem;"></i>
                                <h3 class="text-success"><?php echo e($stock_availability_percentage); ?>%</h3>
                                <p class="text-muted mb-0">Stock Availability</p>
                            </div>
                        </div>
                        <div class="card modern-card quick-stat-card mt-3">
                            <div class="card-body text-center">
                                <i class="fas fa-clock text-warning mb-3" style="font-size: 2rem;"></i>
                                <h3 class="text-warning"><?php echo e($items_need_restocking_percentage); ?>%</h3>
                                <p class="text-muted mb-0">Items Need Restocking</p>
                            </div>
                        </div>
                        <div class="card modern-card quick-stat-card mt-3">
                            <div class="card-body text-center">
                                <i class="fas fa-shipping-fast text-info mb-3" style="font-size: 2rem;"></i>
                                <h3 class="text-info"><?php echo e($recently_released_percentage); ?>%</h3>
                                <p class="text-muted mb-0">Recently Released</p>
                                <button type="button" class="btn btn-sm btn-outline-info mt-2" data-bs-toggle="modal" data-bs-target="#recentlyReleasedModal">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Enhanced Inventory Management Content -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card modern-card supplies-card" data-aos="fade-up">
                        <div class="card-header modern-card-header">
                            <div class="header-title-section">
                                <h5 class="card-title">
                                    <i class="fas fa-boxes me-2 text-primary"></i>Supplies Management
                                </h5>
                                <p class="card-subtitle">Manage and track all your inventory supplies</p>
                            </div>
                        </div>
                        <div class="card-body">
                           <div class="filter-container modern-filter mb-3">
    <form class="d-flex flex-wrap align-items-center gap-2 w-100" method="GET" action="<?php echo e(route('Admin.admin')); ?>">
        
        <div class="d-flex align-items-center gap-2">
            <label class="mb-0 fw-bold text-secondary text-nowrap">Filter by:</label>
            
            <select class="form-select shadow-sm" name="filter" id="stockFilterDropdown" style="width: auto; min-width: 140px;">
                <option value="all" <?php if(request('filter') === 'all'): ?> selected <?php endif; ?>>All Stock</option>
                <option value="available" <?php if(request('filter') === 'available'): ?> selected <?php endif; ?>>Available</option>
                <option value="low" <?php if(request('filter') === 'low'): ?> selected <?php endif; ?>>Low Stock</option>
                <option value="no" <?php if(request('filter') === 'no'): ?> selected <?php endif; ?>>No Stock</option>
            </select>

            <select class="form-select shadow-sm" name="category" id="categoryFilterDropdown" data-selected="<?php echo e(request('category') ?? ''); ?>" style="width: auto; min-width: 160px;">
                <option value="">All Categories</option>
                <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $c = is_string($cat) ? trim($cat) : $cat; ?>
                    <?php if($c !== ''): ?>
                        <option value="<?php echo e($c); ?>" <?php if(request('category') === $c): ?> selected <?php endif; ?>><?php echo e($c); ?></option>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="input-group shadow-sm flex-grow-1 mx-2" style="min-width: 220px;">
            <span class="input-group-text bg-white border-end-0">
                <i class="fas fa-search text-muted"></i>
            </span>
            <input class="form-control border-start-0 ps-0" type="search" name="search" placeholder="Search supplies..." value="<?php echo e(request('search')); ?>">
            <button class="btn btn-outline-success" type="submit">Search</button>
        </div>

        <div class="d-flex gap-2">
            <?php if(Auth::user()->role === 'SuperAdmin' || Auth::user()->can('Add-Supply')): ?>
                <button type="button" class="btn btn-primary shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#addSupplyModal">
                    <i class="fas fa-plus me-1"></i> Add Supply
                </button>
            <?php endif; ?>
            <button type="button" class="btn btn-success shadow-sm text-nowrap" onclick="window.printInventory()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>

    </form>
</div>
                            
                            <div class="table-container">
                                <table class="table modern-table">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-hashtag me-1"></i>Stock Number</th>
                                            <th class="text-center"><i class="fas fa-info-circle me-1"></i>Item Description</th>
                                            <!-- ADDED: Category column -->
                                            <th><i class="fas fa-tags me-1"></i>Category</th>
                                            <th><i class="fas fa-ruler me-1"></i>Unit</th>
                                            <th><i class="fas fa-signal me-1"></i>Status</th>
                                            <th class="text-center"><i class="fas fa-sort-numeric-up me-1"></i>Quantity</th>
                                            <th class="text-center"><i class="fas fa-cogs me-1"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                        <?php $__currentLoopData = $filtered_supplies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            
                                            <?php $qtyGuard = (int) ($supply->Quantity ?? 0); ?>

                                            
                                            <?php
                                                $supplyCategory = trim((string) ($supply->Category ?? $supply->category ?? ''));
                                                $requestedCategory = trim((string) (request('category') ?? ''));
                                            ?>
                                            <?php if($requestedCategory !== '' && $supplyCategory !== $requestedCategory): ?>
                                                <?php continue; ?>
                                            <?php endif; ?>
                                            
                                            <?php if(request('filter') === 'low'): ?>
                                                <?php if($qtyGuard < 1 || $qtyGuard > 5): ?>
                                                    <?php continue; ?>
                                                <?php endif; ?>
                                            <?php elseif(request('filter') === 'available'): ?>
                                                <?php if($qtyGuard <= 5): ?>
                                                    <?php continue; ?>
                                                <?php endif; ?>
                                            <?php elseif(request('filter') === 'no'): ?>
                                                <?php if($qtyGuard !== 0): ?>
                                                    <?php continue; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                        <tr class="table-row-hover">
                                            <td class="font-weight-bold"><?php echo e($supply->StockNumber); ?></td>
                                            <td class="text-center"><?php echo e($supply->ItemDescription); ?></td>
                                            <!-- ADDED: show category (fallback to em dash) -->
                                            <td class="text-center">
                                                <span class="category-badge"><?php echo e($supply->Category ?? $supply->category ?? '—'); ?></span>
                                            </td>
                                            <td><span class="unit-badge"><?php echo e($supply->Unit); ?></span></td>
                                            <td>
                                                
                                                <?php
                                                    $qty = (int) $supply->Quantity;
                                                    $statusClass = $qty === 0 ? 'bg-danger' : ($qty <= 5 ? 'bg-warning text-dark' : 'bg-success');
                                                    $icon = $qty === 0 ? 'fa-times-circle' : ($qty <= 5 ? 'fa-exclamation-triangle' : 'fa-check-circle');
                                                    $label = $qty === 0 ? 'Out of Stock' : ($qty <= 5 ? 'Low Stock' : 'Available');
                                                ?>
                                                <span class="badge <?php echo e($statusClass); ?> modern-badge">
                                                    <i class="fas <?php echo e($icon); ?> me-1"></i><?php echo e($label); ?>

                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="quantity-display <?php echo e($qty <= 5 && $qty > 0 ? 'low-stock' : ''); ?>">
                                                    <?php echo e($supply->Quantity); ?>

                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-buttons d-flex justify-content-center gap-1 flex-wrap">
                                                    
                                                    <?php if(Auth::user()->role === 'SuperAdmin'): ?>
                                                        <button type="button"
                                                                class="btn btn-info btn-sm modern-action-btn history-btn"
                                                                title="View Stock History"
                                                                data-stock="<?php echo e($supply->StockNumber); ?>"
                                                                data-description="<?php echo e($supply->ItemDescription); ?>">
                                                            <i class="fas fa-history"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if(Auth::user()->role === 'SuperAdmin' || Auth::user()->can('Edit-Supply')): ?>
                                                        <button class="btn btn-warning btn-sm modern-action-btn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editSupplyModal<?php echo e($supply->StockNumber); ?>"
                                                                data-category="<?php echo e($supply->Category ?? ''); ?>"
                                                                title="Edit Supply">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    
                                                    <?php if(Auth::user()->role === 'SuperAdmin' || Auth::user()->can('Delete-Supply')): ?>
                                                    <form action="<?php echo e(route('admin.supply.destroy', $supply->StockNumber)); ?>" method="POST" style="display:inline;" class="delete-supply-form">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="button" class="btn btn-danger btn-sm modern-action-btn delete-btn" 
                                                                title="Delete Supply"
                                                                onclick="showDeleteConfirmation('<?php echo e($supply->StockNumber); ?>', '<?php echo e($supply->ItemDescription); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Improved pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small">
                                    Showing <?php echo e($filtered_supplies->firstItem() ?? 0); ?> to <?php echo e($filtered_supplies->lastItem() ?? 0); ?> of <?php echo e($filtered_supplies->total()); ?> supplies
                                </div>
                                <div>
                                    <nav>
                                        <ul class="pagination">
                                            <?php echo e($filtered_supplies->links('pagination::bootstrap-4')); ?>

                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional modals (Orders, Reports) can be added similarly -->
        </div>
    </div>

    <!-- Add Supply Modal - Only include if user has proper permissions -->
    <?php if(Auth::user()->role === 'SuperAdmin' || Auth::user()->can('Add-Supply')): ?>
    <div class="modal fade" id="addSupplyModal" tabindex="-1" aria-labelledby="addSupplyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm"> <!-- COMPACT: smaller dialog for quick add -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplyModalLabel">Add New Supply</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo e(route('admin.supply.store')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="stockNumber" class="form-label">Stock Number</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="stockNumber" 
                                   name="StockNumber" 
                                   required>
                        </div>
                        <div class="mb-3">
                            <label for="itemDescription" class="form-label">Item Description</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="itemDescription"
                                   name="ItemDescription" 
                                   required>
                            <div class="form-text">Enter a description for this item</div>
                            <!-- ADDED: Live checking + existing item notice -->
                            <div id="itemDescChecking" class="form-text text-muted mt-1 d-none" style="display:none;">
                                <i class="fas fa-spinner fa-spin me-1"></i>Checking database...
                            </div>
                            <div id="existingItemNotice" class="alert alert-info d-flex align-items-start mt-2 d-none" style="display:none;">
                                <i class="fas fa-info-circle me-2 mt-1"></i>
                                <div>
                                    This item already exists in the database. Adding quantity will update the existing stock.
                                </div>
                            </div>
                        </div>

                        <!-- ADDED: Category selection with Add Category button -->
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <div class="input-group">
                                <select class="form-control category-select" id="category" name="Category" required>
                                    <option value="">-- Select Category --</option>
                                    <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cat); ?>" <?php echo e(old('Category') == $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" id="addCategoryButton" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                    <i class="fas fa-plus me-1"></i> Add Category
                                </button>
                                <!-- ADDED: inline delete button next to Add Category -->
                                <button type="button" class="btn btn-outline-danger ms-1 delete-category-inline" title="Delete selected category">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <div class="input-group">
                                <select class="form-control" id="unit" name="Unit" required>
                                    <option value="pc">pc</option>
                                    <option value="box">box</option>
                                    <option value="pack">pack</option>
                                    <option value="ream">ream</option>
                                    <!-- Custom units will be added here -->
                                </select>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-primary" id="addUnitButton" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                                        <i class="fas fa-plus me-1"></i> Add Unit
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" id="deleteUnitInlineBtn" title="Delete selected unit">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                             </div>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="quantity" 
                                   name="Quantity" 
                                   min="0" 
                                   required 
                                   onkeydown="return event.keyCode !== 189"
                                   oninput="this.value = Math.abs(parseInt(this.value) || 0)"
                                   onchange="updateStatus(this)">
                        </div>
                        <input type="hidden" name="Status" value="Available">
                        <button type="submit" class="btn btn-primary">Add Supply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Add Unit Modal -->
    <div class="modal fade" id="addUnitModal" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUnitModalLabel">Add New Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newUnitName" class="form-label">Unit Name</label>
                        <input type="text" class="form-control" id="newUnitName" placeholder="e.g., bundle, set, roll">
                        <div class="form-text">Enter a short, descriptive unit name</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveNewUnitBtn">
                        <i class="fas fa-save me-1"></i> Save Unit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ADD: Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newCategoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="newCategoryName" placeholder="e.g., Cleaning, Food, IT">
                        <div class="form-text">Enter a short descriptive category name</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- Deleted: Remove inline delete button from Add Category modal per request -->
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveNewCategoryBtn">
                        <i class="fas fa-save me-1"></i> Save Category
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- NEW: Confirm Delete Category Modal (styled) -->
    <div class="modal fade" id="confirmDeleteCategoryModal" tabindex="-1" aria-labelledby="confirmDeleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmDeleteCategoryModalLabel">
                        <i class="fas fa-trash-alt me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete the category <strong id="confirmDeleteCategoryName"></strong>?</p>
                    <p class="mt-2 small text-muted">This will remove the category from this browser's stored categories and from all category selects on the page.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteCategoryBtn" class="btn btn-danger">
                        <i class="fas fa-check me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Unit Confirmation Modal - Only include if user has proper permissions -->
    <?php if(Auth::user()->role === 'SuperAdmin' || Auth::user()->can('Delete-Supply')): ?>
    <div class="modal fade" id="unitDeleteConfirmModal" tabindex="-1" aria-labelledby="unitDeleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content modern-modal">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="unitDeleteConfirmModalLabel">
                        <i class="fas fa-trash-alt me-2"></i>Delete Unit
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Are you sure you want to delete the unit <strong id="unitToDeleteName"></strong>?</p>
                    <p class="small text-muted mb-0">This action will remove the unit from all unit selects on this page and from your browser's stored custom units. It cannot be undone from the UI.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelDeleteUnitBtn">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteUnitBtn">
                        <i class="fas fa-check me-1"></i>Delete Unit
                        <span id="confirmDeleteUnitSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Edit Supply Modal - Only include if user has proper permissions -->
    <?php if(Auth::user()->role === 'SuperAdmin' || Auth::user()->can('Edit-Supply')): ?>
        <?php $__currentLoopData = $filtered_supplies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="modal fade" id="editSupplyModal<?php echo e($supply->StockNumber); ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editSupplyModalLabel<?php echo e($supply->StockNumber); ?>">Edit Supply</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo e(route('admin.supply.update', $supply->StockNumber)); ?>" method="POST" class="edit-supply-form">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div class="mb-3">
                                <label for="editStockNumber<?php echo e($supply->StockNumber); ?>" class="form-label">Stock Number</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="editStockNumber<?php echo e($supply->StockNumber); ?>" 
                                       name="StockNumber" 
                                       value="<?php echo e($supply->StockNumber); ?>" 
                                       required>
                            </div>
                            <div class="mb-3">
                                <label for="editItemDescription<?php echo e($supply->StockNumber); ?>" class="form-label">Item Description</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="editItemDescription<?php echo e($supply->StockNumber); ?>" 
                                       name="ItemDescription" 
                                       value="<?php echo e($supply->ItemDescription); ?>"
                                       required>
                            </div>

                            <!-- ADDED: Category select for edit modal -->
                            <div class="mb-3">
                                <label for="editSupplyCategory<?php echo e($supply->StockNumber); ?>" class="form-label">Category</label>
                                <div class="input-group">
                                    <select class="form-control category-select" id="editSupplyCategory<?php echo e($supply->StockNumber); ?>" name="Category" required>
                                        <option value="">-- Select Category --</option>
                                        <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($cat); ?>" <?php echo e((isset($supply->Category) && $supply->Category == $cat) ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        
                                        <?php if(!empty($supply->Category) && !in_array($supply->Category, $categories ?? [])): ?>
                                            <option value="<?php echo e($supply->Category); ?>" selected><?php echo e($supply->Category); ?></option>
                                        <?php endif; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-primary add-category-btn" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                        <i class="fas fa-plus me-1"></i> Add
                                    </button>
                                    <!-- ADDED: inline delete for edit modal -->
                                    <button type="button" class="btn btn-outline-danger ms-1 delete-category-inline" title="Delete selected category">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="editSupplyUnit<?php echo e($supply->StockNumber); ?>" class="form-label">Unit</label>
                                <div class="input-group">
                                    <select class="form-control unit-selector" id="editSupplyUnit<?php echo e($supply->StockNumber); ?>" name="Unit" required>
                                        <option value="pc" <?php echo e($supply->Unit == 'pc' ? 'selected' : ''); ?>>pc</option>
                                        <option value="box" <?php echo e($supply->Unit == 'box' ? 'selected' : ''); ?>>box</option>
                                        <option value="pack" <?php echo e($supply->Unit == 'pack' ? 'selected' : ''); ?>>pack</option>
                                        <option value="ream" <?php echo e($supply->Unit == 'ream' ? 'selected' : ''); ?>>ream</option>
                                        <?php if(!in_array($supply->Unit, ['pc','box','pack','ream']) && !empty($supply->Unit)): ?>
                                            <option value="<?php echo e($supply->Unit); ?>" selected><?php echo e($supply->Unit); ?></option>
                                        <?php endif; ?>
                                        <!-- Custom units will be added here -->
                                    </select>
                                    <button type="button" class="btn btn-outline-primary add-unit-btn" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                                        <i class="fas fa-plus me-1"></i> Add
                                    </button>

                                    <!-- ADDED: inline delete for edit modal -->
                                    <button type="button" class="btn btn-outline-danger ms-1 delete-unit-inline" title="Delete selected unit">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="editSupplyQuantity<?php echo e($supply->StockNumber); ?>" class="form-label">Quantity</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="editSupplyQuantity<?php echo e($supply->StockNumber); ?>" 
                                       name="Quantity" 
                                       value="<?php echo e($supply->Quantity); ?>" 
                                       min="0" 
                                       required
                                       onkeydown="return event.keyCode !== 189"
                                       oninput="this.value = Math.abs(parseInt(this.value) || 0)"
                                       onchange="updateStatus(this)">
                            </div>
                            <input type="hidden" name="Status" value="<?php echo e($supply->Quantity > 0 ? 'Available' : 'Out of Stock'); ?>">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    
    <?php if(Auth::user()->role === 'SuperAdmin' || Auth::user()->can('Edit-Supply')): ?>
    <div class="modal fade" id="dynamicEditSupplyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Supply</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <div class="modal-body">
                    <form id="dynamicEditForm" action="" method="POST" class="edit-supply-form">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="mb-3">
                            <label for="dynamicEditStockNumber" class="form-label">Stock Number</label>
                            <input type="text" class="form-control" id="dynamicEditStockNumber" name="StockNumber" required>
                        </div>
                        <div class="mb-3">
                            <label for="dynamicEditItemDescription" class="form-label">Item Description</label>
                            <input type="text" class="form-control" id="dynamicEditItemDescription" name="ItemDescription" required>
                        </div>

                        <!-- ADDED: Category select for dynamic edit -->
                        <div class="mb-3">
                            <label for="dynamicEditCategory" class="form-label">Category</label>
                            <div class="input-group">
                                <select class="form-control category-select" id="dynamicEditCategory" name="Category" required>
                                    <option value="">-- Select Category --</option>
                                    <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cat); ?>"><?php echo e($cat); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary add-category-btn" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                    <i class="fas fa-plus me-1"></i> Add
                                </button>
                                <!-- ADDED: inline delete for dynamic modal -->
                                <button type="button" class="btn btn-outline-danger ms-1 delete-category-inline" title="Delete selected category">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="dynamicEditUnit" class="form-label">Unit</label>
                            <div class="input-group">
                                <select class="form-control unit-selector" id="dynamicEditUnit" name="Unit" required>
                                    <option value="pc">pc</option>
                                    <option value="box">box</option>
                                    <option value="pack">pack</option>
                                    <option value="ream">ream</option>
                                    <!-- Custom units will be added here -->
                                </select>
                                <button type="button" class="btn btn-outline-primary add-unit-btn" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                                    <i class="fas fa-plus me-1"></i> Add
                                </button>

                                <!-- ADDED: inline delete for dynamic edit modal -->
                                <button type="button" class="btn btn-outline-danger ms-1 delete-unit-inline" title="Delete selected unit">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="dynamicEditQuantity" class="form-label">Quantity</label>
                            <input type="number"
                                   class="form-control"
                                   id="dynamicEditQuantity"
                                   name="Quantity"
                                   min="0"
                                   required
                                   onkeydown="return event.keyCode !== 189"
                                   oninput="this.value = Math.abs(parseInt(this.value) || 0)"
                                   onchange="updateStatus(this)">
                        </div>
                        <input type="hidden" id="dynamicEditStatus" name="Status" value="Available">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ADD: Confirmation modal (from SMIS26) for combining quantities on existing stock -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">
                        <i class="fas fa-question-circle text-primary me-2"></i>Confirm Action
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info py-2">
                        <i class="fas fa-info-circle me-2"></i>
                        <span style="color: #0c5460; font-weight: 500;">This stock number already exists. Would you like to combine the quantities?</span>
                    </p>
                    <div class="quantity-summary p-3 border rounded bg-light">
                        <p class="mb-2">
                            <span class="text-secondary fw-bold">Current Quantity:</span>
                            <span class="text-primary fw-bold" id="currentQty" style="font-size: 1.1rem;"></span>
                        </p>
                        <p class="mb-2">
                            <span class="text-secondary fw-bold">Added Quantity:</span>
                            <span class="text-success fw-bold" id="addedQty" style="font-size: 1.1rem;"></span>
                        </p>
                        <div class="border-top pt-2 mt-2">
                            <p class="mb-0">
                                <span class="text-secondary fw-bold">New Total:</span>
                                <span class="text-danger fw-bold" id="newTotal" style="font-size: 1.25rem;"></span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmCombine">
                        <i class="fas fa-check me-1"></i>Confirm
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
                        <i class="fas fa-sign-out-alt text-danger me-2"></i>Confirm Logout
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p style="color: #333; font-weight: 500; font-size: 16px; text-align: center;">Are you sure you want to logout from the system?</p>
                </div>
                <div class="modal-footer">
                    <!-- CHANGED: add id for precise handling -->
                    <button type="button" id="cancelLogoutBtn" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmLogoutBtn" onclick="confirmLogout()">
                        <span id="logoutBtnText">Confirm Logout</span>
                        <span id="logoutBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <script>
        (function () {
            window.confirmLogout = function () {
                try {
                    const btn = document.getElementById('confirmLogoutBtn');
                    const text = document.getElementById('logoutBtnText');
                    const spinner = document.getElementById('logoutBtnSpinner');
                    if (btn) btn.disabled = true;
                    if (spinner) spinner.classList.remove('d-none');
                    // removed buggy reference to `spn` (was undefined)
    
                    const form = document.getElementById('logout-form');
                    if (form) {
                        form.submit();
                        return;
                    }
    
                    // Fallback via fetch if form is missing (shouldn't happen)
                    const token = (document.querySelector('meta[name="csrf-token"]') || {}).content ||
                                  (document.querySelector('input[name="_token"]') || {}).value || '';
                    fetch('<?php echo e(route('logout')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({})
                    }).then(() => {
                        window.location.href = '<?php echo e(url('/')); ?>';
                    }).catch(() => {
                        if (btn) btn.disabled = false;
                        if (spinner) spinner.classList.add('d-none');
                        if (text) text.textContent = 'Confirm Logout';
                    });
                } catch (e) {
                    const form = document.getElementById('logout-form');
                    if (form) form.submit();
                }
            };
        })();
    </script>

    <!-- Delete Supply Confirmation Modal - Only include if user has proper permissions -->
    <?php if(Auth::user()->role === 'SuperAdmin' || Auth::user()->can('Delete-Supply')): ?>
    <div class="modal fade" id="deleteSupplyModal" tabindex="-1" aria-labelledby="deleteSupplyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modern-modal">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteSupplyModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-trash text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="alert alert-danger" style="font-size: 1.1rem; font-weight: 500; text-align: center;">
                        Are you sure you want to delete this supply?
                    </p>
                    <div class="supply-details p-3 border rounded bg-light">
                        <p class="mb-2">
                            <span class="text-secondary fw-bold">Stock Number:</span> 
                            <span class="text-danger fw-bold" id="deleteStockNumber"></span>
                        </p>
                        <p class="mb-0">
                            <span class="text-secondary fw-bold">Item Description:</span> 
                            <span class="text-danger fw-bold" id="deleteItemDescription"></span>
                        </p>
                    </div>
                    <p class="text-muted text-center mt-3" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle me-1"></i>This action cannot be undone
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash me-1"></i><span id="deleteButtonText">Delete Supply</span>
                        <span id="deleteButtonSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Delete Success Modal -->
    <div class="modal fade" id="deleteSuccessModal" tabindex="-1" aria-labelledby="deleteSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modern-modal">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="deleteSuccessModalLabel">
                        <i class="fas fa-check-circle text-white me-2"></i>Success
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                    <p class="alert alert-success" style="font-size: 1.1rem; font-weight: 500; text-align: center;">
                        Supply has been successfully deleted!
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Settings Modal for Password Change -->
    <div class="modal fade" id="userSettingsModal" tabindex="-1" aria-labelledby="userSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modern-modal">
                <div class="modal-header" style="background: #800020 !important; color: white !important;">
                    <h5 class="modal-title" id="userSettingsModalLabel">
                        <i class="fas fa-user-cog me-2"></i>User Settings - <?php echo e(Auth::user()->name); ?>

                    </h5>
                    <!-- Visible close button: explicit FA xmark to ensure visibility on dark header -->
                    <button type="button" class="btn-close btn-close-white visible-close-btn" data-bs-dismiss="modal" aria-label="Close" style="border:0;background:transparent;">
                        <i class="fa-solid fa-xmark" aria-hidden="true" style="color:#fff; font-size:1.05rem;"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm" action="<?php echo e(route('user.change-password')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
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

    <!-- Recently Released Modal -->
    <div class="modal fade" id="recentlyReleasedModal" tabindex="-1" aria-labelledby="recentlyReleasedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-xl-down modal-xl"> <!-- Changed to full width on smaller screens -->
            <div class="modal-content modern-modal">
                <div class="modal-header" style="background: #800020 !important; padding: 0.75rem 1.5rem;">
                    <h5 class="modal-title" id="recentlyReleasedModalLabel">
                        <i class="fas fa-shipping-fast text-info me-2"></i>Recently Released Items (Last 7 Days)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <div class="modal-body p-0"> <!-- Removed padding for more landscape feel -->
                    <?php if($recently_released->count() > 0): ?>
                        <!-- Tab-style Filter Controls -->
                        <div class="filter-tabs px-3 py-2">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <button class="btn btn-primary w-100 filter-tab active" id="filterByStock" type="button">
                                        <i class="fas fa-hashtag me-2"></i>Filter by Stock
                                    </button>
                                    <div class="filter-input mt-1" id="stockFilter">
                                        <input type="text" class="form-control" id="filterStockNumber" placeholder="Enter stock number...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary w-100 filter-tab" id="filterByItem" type="button">
                                        <i class="fas fa-box me-2"></i>Filter by Item
                                    </button>
                                    <div class="filter-input mt-1" id="itemFilter" style="display: none;">
                                        <input type="text" class="form-control" id="filterItemDescription" placeholder="Enter item description...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary w-100 filter-tab" id="filterByRecipient" type="button">
                                        <i class="fas fa-user me-2"></i>Filter by Recipient
                                    </button>
                                    <div class="filter-input mt-1" id="recipientFilter" style="display: none;">
                                        <input type="text" class="form-control" id="filterReceivedBy" placeholder="Enter recipient name...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="releasedItemsTable">
                                <thead style="background: #1e3a8a; color: white;">
                                    <tr>
                                        <th class="py-3">
                                            <span class="d-flex align-items-center">
                                                <i class="fas fa-hashtag me-2"></i>STOCK NUMBER
                                            </span>
                                        </th>
                                        <th class="py-3">
                                            <span class="d-flex align-items-center">
                                                <i class="fas fa-box me-2"></i>ITEM DESCRIPTION
                                            </span>
                                        </th>
                                        <th class="py-3">
                                            <span class="d-flex align-items-center">
                                                <i class="fas fa-ruler me-2"></i>UNIT
                                            </span>
                                        </th>
                                        <th class="text-center py-3">
                                            <span class="d-flex align-items-center justify-content-center">
                                                <i class="fas fa-sort-numeric-up me-2"></i>ISSUED QTY
                                            </span>
                                        </th>
                                        <th class="py-3">
                                            <span class="d-flex align-items-center">
                                                <i class="fas fa-user me-2"></i>RECEIVED BY
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $recently_released; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="released-item-row">
                                        <td class="stock-number"><?php echo e($item->StockNumber); ?></td>
                                        <td class="item-description"><?php echo e($item->supply_name); ?></td>
                                        <td><span class="unit-badge"><?php echo e($item->Unit); ?></span></td>
                                        <td class="text-center">
                                            <span class="quantity-display"><?php echo e($item->issue_quantity); ?></span>
                                        </td>
                                        <td class="received-by"><?php echo e($item->received_by); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- No Results Message -->
                        <div id="noFilterResults" class="alert alert-info m-3" style="display: none;">
                            <i class="fas fa-info-circle me-2"></i> No items match your filter criteria.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info m-3">
                            <i class="fas fa-info-circle me-2"></i> No items have been released in the past 7 days.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="<?php echo e(route('requests.released')); ?>" class="btn btn-primary">
                        <i class="fas fa-list me-1"></i> View All Released Items
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- STOCK HISTORY MODAL (single reusable) -->
    <?php if(Auth::user()->role === 'SuperAdmin'): ?>
    <div class="modal fade" id="stockHistoryModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-fullscreen-lg-down modal-xl stock-history-modal-dialog modal-dialog-scrollable">
             <div class="modal-content">
                 <div class="modal-header" style="background:#1e3a8a; color:#fff;">
                     <h5 class="modal-title">
                         <i class="fas fa-history me-2"></i>Stock History: <span id="historyItemLabel" class="fw-bold"></span>
                     </h5>
                     <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                 </div>
                 <div class="modal-body p-0">
                     <div id="stockHistoryLoading" class="p-4 text-center d-none">
                         <div class="spinner-border text-primary mb-3" role="status"></div>
                         <div class="fw-semibold">Loading history...</div>
                     </div>
                     <div id="stockHistoryError" class="alert alert-danger m-3 d-none"></div>
                     <div id="stockHistoryEmpty" class="alert alert-info m-3 d-none">
                         <i class="fas fa-info-circle me-2"></i>No history records found for this stock item.
                     </div>
                     <div class="table-responsive" id="stockHistoryTableWrapper" style="display:none;">
                         <table class="table table-sm table-hover mb-0" id="stockHistoryTable" style="font-size:0.9rem;">
                             <thead style="background:#f1f5f9;">
                                 <tr>
                                     <th style="white-space:nowrap;">Date / Time (PHT)</th>
                                     <th>Action</th>
                                     <th style="white-space:nowrap;">Change</th>
                                     <th style="white-space:nowrap;">Req / Appr</th>
                                     <th style="white-space:nowrap;">Prev Qty</th>
                                     <th style="white-space:nowrap;">New Qty</th>
                                     <th style="white-space:nowrap;">Performed By</th>
                                     <th>Details</th>
                                 </tr>
                             </thead>
                             <tbody></tbody>
                         </table>
                     </div>
                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                 </div>
             </div>
         </div>
     </div>
     <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
             <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- BOOTSTRAP: Expose server-side data to admin.js (minimal inline bootstrap only) -->
    <script>
        window.__adminData = {
            csrfToken: "<?php echo e(csrf_token()); ?>",

            routes: {
                logout: "<?php echo e(route('logout')); ?>",
                checkStockNumber: "<?php echo e(route('admin.check.stocknumber')); ?>",
                updateSupplyTemplate: "<?php echo e(route('admin.supply.update', '__STOCK__')); ?>",
                // optional: supply history template used by stock history code (if route exists)
                supplyHistoryTemplate: "<?php echo e(isset($supplyHistoryRoute) ? $supplyHistoryRoute : (route('admin.supply.history', ['__STOCK__']) ?? '')); ?>"

            },
            // Chart data: use server-provided arrays when available, otherwise fall back to sensible totals
            chart_labels: <?php echo json_encode($chart_labels ?? ['Total Products','Total Stock','Out of Stock']); ?>,
            chart_data: <?php echo json_encode($chart_data ?? [$total_products ?? 0, $total_stock ?? 0, $out_of_stock ?? 0]); ?>,
            chart_colors: <?php echo json_encode($chart_colors ?? ['rgba(34,197,94,0.7)','rgba(251,191,36,0.7)','rgba(239,68,68,0.7)']); ?>,
            // Totals used elsewhere (admin.js pie chart fallback)
            total_available: <?php echo e($total_available ?? 0); ?>,

            total_low_stock: <?php echo e($total_low_stock ?? 0); ?>,
            total_out_of_stock: <?php echo e($total_out_of_stock ?? 0); ?>,
            // Expose categories for client-side population (trimmed)
            categories: <?php echo json_encode(($categories ?? collect())->map(function($v){ return is_string($v) ? trim($v) : $v; })->values()->all()); ?>,
            selectedCategory: "<?php echo e(request('category') ?? ''); ?>"
        };
    </script>

    <!-- Consolidated page javascript (moved from inline scripts) -->
    <script src="<?php echo e(asset('js/admin.js')); ?>?v=history6"></script>

    <!-- Legacy/feature-specific scripts that are still external -->
    <script src="<?php echo e(asset('js/inventory-print.js')); ?>"></script>
    <script src="<?php echo e(asset('js/enhanced-admin-notifications.js')); ?>"></script>
    <script src="<?php echo e(asset('js/simple-badge-manager.js')); ?>"></script>
    <script src="<?php echo e(asset('js/user-settings.js')); ?>"></script>
    <script src="<?php echo e(asset('js/user-settings-modal.js')); ?>"></script>
    <script src="<?php echo e(asset('js/dark-mode.js')); ?>"></script>

    <!-- ADDED: Category management script -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Populate any saved custom categories from localStorage
        const savedCats = JSON.parse(localStorage.getItem('customCategories') || '[]');
        function ensureCategoryInSelects(name) {
            if (!name) return;
            document.querySelectorAll('select.category-select').forEach(sel => {
                const exists = Array.from(sel.options).some(o => o.value === name);
                if (!exists) {
                    const opt = document.createElement('option');
                    opt.value = name;
                    opt.text = name;
                    sel.appendChild(opt);
                }
            });
        }
        savedCats.forEach(c => ensureCategoryInSelects(c));

        // NEW: update global category filter dropdown + all category-selects and optionally trigger reload
        function refreshCategoryControls(name, { deleted = false } = {}) {
            try {
                const filter = document.getElementById('categoryFilterDropdown');
                // Ensure the option exists (for add) or remove (for delete)
                if (filter) {
                    if (!deleted) {
                        if (!Array.from(filter.options).some(o => o.value === name)) {
                            const opt = document.createElement('option'); opt.value = name; opt.text = name;
                            filter.appendChild(opt);
                        }
                        // select newly added category and trigger change to reload supplies via admin.js
                        filter.value = name;
                    } else {
                        Array.from(filter.options).forEach(o => { if (o.value === name) o.remove(); });
                        // if deleted category was selected, clear selection
                        if (filter.value === name) filter.value = '';
                    }
                    // trigger native change + jQuery fallback so admin.js change handler runs
                    try { filter.dispatchEvent(new Event('change', { bubbles: true })); } catch(e) {}
                    if (window.jQuery) try { $(filter).trigger('change'); } catch(e){}
                }
                
                // Also ensure any other category-select elements reflect the same addition/removal
                if (!deleted) ensureCategoryInSelects(name);
                else {
                    document.querySelectorAll('select.category-select').forEach(sel => {
                        Array.from(sel.options).forEach(opt => { if (opt.value === name) opt.remove(); });
                        if (sel.value === name) sel.value = '';
                    });
                }
            } catch (e) { console.warn('refreshCategoryControls error', e); }
        }

        // Toast helper for category actions
        function showCategoryAddedToast(name, alreadyExisting) {
            try {
                if (!name) return;
                let container = document.getElementById('categoryToastContainer');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'categoryToastContainer';
                    container.style.position = 'fixed';
                    container.style.top = '1rem';
                    container.style.right = '1rem';
                    container.style.zIndex = '1085';
                    document.body.appendChild(container);
                }
                const toastEl = document.createElement('div');
                toastEl.className = 'toast align-items-center ' + (alreadyExisting ? 'text-bg-secondary' : 'text-bg-success') + ' border-0';
                toastEl.setAttribute('role','alert');
                toastEl.setAttribute('aria-live','assertive');
                toastEl.setAttribute('aria-atomic','true');
                const bodyText = alreadyExisting ? `Category "${name}" already exists.` : `Category "${name}" added.`;
                toastEl.innerHTML = '<div class="d-flex"><div class="toast-body">' + bodyText + '</div>' +
                                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                container.appendChild(toastEl);
                const bs = new bootstrap.Toast(toastEl, { delay: 3000 });
                bs.show();
                toastEl.addEventListener('hidden.bs.toast', function () { toastEl.remove(); if (!container.querySelector('.toast')) container.remove(); });
            } catch (e) { console.warn('category toast error', e); }
        }

        // NEW: toast helper for deletion
        function showCategoryDeletedToast(name) {
            try {
                if (!name) return;
                let container = document.getElementById('categoryToastContainer');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'categoryToastContainer';
                    container.style.position = 'fixed';
                    container.style.top = '1rem';
                    container.style.right = '1rem';
                    container.style.zIndex = '1085';
                    document.body.appendChild(container);
                }
                const toastEl = document.createElement('div');
                toastEl.className = 'toast align-items-center text-bg-danger border-0';
                toastEl.setAttribute('role','alert');
                toastEl.setAttribute('aria-live','assertive');
                toastEl.setAttribute('aria-atomic','true');
                const bodyText = `Category "${name}" deleted.`; 
                toastEl.innerHTML = '<div class="d-flex"><div class="toast-body">' + bodyText + '</div>' +
                                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                container.appendChild(toastEl);
                const bs = new bootstrap.Toast(toastEl, { delay: 3000 });
                bs.show();
                toastEl.addEventListener('hidden.bs.toast', function () { toastEl.remove(); if (!container.querySelector('.toast')) container.remove(); });
            } catch (e) { console.warn('category delete toast error', e); }
        }

        // remove category from localStorage and from all selects
        function deleteCategoryByName(name) {
            const trimmed = (name || '').trim();
            if (!trimmed) return false;
            try {
                // remove from persisted list
                const cats = JSON.parse(localStorage.getItem('customCategories') || '[]');
                const remaining = cats.filter(c => c !== trimmed);
                localStorage.setItem('customCategories', JSON.stringify(remaining));
                // remove from DOM selects
                document.querySelectorAll('select.category-select').forEach(sel => {
                    Array.from(sel.options).forEach(opt => {
                        if (opt.value === trimmed) opt.remove();
                    });
                });
                // Also remove any occurrence in add/select inputs if currently selected
                document.querySelectorAll('select.category-select').forEach(sel => {
                    if (sel.value === trimmed) sel.selectedIndex = 0;
                });
                // Also remove from the main filter dropdown if present
                const mainFilter = document.getElementById('categoryFilterDropdown');
                if (mainFilter) {
                    Array.from(mainFilter.options).forEach(o => { if (o.value === trimmed) o.remove(); });
                    if (mainFilter.value === trimmed) mainFilter.value = '';
                    try { mainFilter.dispatchEvent(new Event('change', { bubbles: true })); } catch(e){}
                    if (window.jQuery) try { $(mainFilter).trigger('change'); } catch(e){}
                }
                return true;
            } catch (e) {
                console.warn('deleteCategoryByName error', e);
                return false;
            }
        }

        // NEW: confirm modal integration
        let _pendingCategoryDelete = null;
        function openCategoryDeleteConfirm(name, onConfirm) {
            _pendingCategoryDelete = { name: name, cb: onConfirm };
            const nameEl = document.getElementById('confirmDeleteCategoryName');
            if (nameEl) nameEl.textContent = `"${name}"`;
            const modalEl = document.getElementById('confirmDeleteCategoryModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
        document.getElementById('confirmDeleteCategoryBtn')?.addEventListener('click', function () {
            try {
                const pending = _pendingCategoryDelete;
                if (!pending) return;
                // perform caller-provided callback when available, otherwise run default deletion
                if (typeof pending.cb === 'function') {
                    pending.cb(pending.name);
                    // ensure main filter / selects are updated in case the caller didn't do it
                    refreshCategoryControls(pending.name, { deleted: true });
                } else {
                    const ok = deleteCategoryByName(pending.name);
                    if (ok) {
                        showCategoryDeletedToast(pending.name);
                        refreshCategoryControls(pending.name, { deleted: true });
                    }
                    else alert('Failed to delete category. Check console for details.');
                }
            } finally {
                _pendingCategoryDelete = null;
                const modalEl = document.getElementById('confirmDeleteCategoryModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        });

        // Save new category and add to all selects, select it in active form, persist
        const saveBtn = document.getElementById('saveNewCategoryBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                const nameInput = document.getElementById('newCategoryName');
                const name = (nameInput && nameInput.value || '').trim();
                if (!name) {
                    // small inline feedback
                    nameInput && nameInput.classList.add('is-invalid');
                    return;
                }

                // load current saved categories and determine existence
                const cats = JSON.parse(localStorage.getItem('customCategories') || '[]');
                const alreadyExisting = cats.includes(name);

                // add to selects
                ensureCategoryInSelects(name);

                // persist only if new
                if (!alreadyExisting) {
                    cats.push(name);
                    localStorage.setItem('customCategories', JSON.stringify(cats));
                }

                // mark selected for any open modal where a select is focused
                const activeSelect = document.querySelector('select.category-select:focus') || document.querySelector('select.category-select');
                if (activeSelect) activeSelect.value = name;

                // show popup toast
                showCategoryAddedToast(name, alreadyExisting);

                // NEW: update main filter dropdown and trigger change so supplies reload without page refresh
                refreshCategoryControls(name, { deleted: false });

                // clear and close modal (save path)
                if (nameInput) {
                    nameInput.value = '';
                    nameInput.classList.remove('is-invalid');
                }
                const modalEl = document.getElementById('addCategoryModal');
                const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                bsModal.hide();
            });
        }

        // NEW: handle delete button click in modal (uses styled modal)
        const deleteBtn = document.getElementById('deleteCategoryBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function () {
                const nameInput = document.getElementById('newCategoryName');
                const name = (nameInput && nameInput.value || '').trim();
                if (!name) {
                    // nothing to delete - show small inline feedback
                    if (nameInput) {
                        nameInput.classList.add('is-invalid');
                        setTimeout(() => nameInput.classList.remove('is-invalid'), 1200);
                    }
                    return;
                }
                // open styled confirm modal; on confirm run deletion
                openCategoryDeleteConfirm(name, function (n) {
                    const ok = deleteCategoryByName(n);
                    if (ok) {
                        showCategoryDeletedToast(n);
                        if (nameInput) nameInput.value = '';
                        const modalEl = document.getElementById('addCategoryModal');
                        const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        bsModal.hide();
                        // ensure the filter dropdown updates immediately
                        refreshCategoryControls(n, { deleted: true });
                    } else {
                        alert('Failed to delete category. Check console for details.');
                    }
                });
            });
        }

        // NEW: clear the input when the Add Category modal is closed (cancel, X, or programmatic hide)
        const addCategoryModalEl = document.getElementById('addCategoryModal');
        if (addCategoryModalEl) {
            addCategoryModalEl.addEventListener('hidden.bs.modal', function () {
                try {
                    const nameInput = document.getElementById('newCategoryName');
                    if (nameInput) {
                        nameInput.value = '';
                        nameInput.classList.remove('is-invalid');
                    }
                } catch (e) {
                    console.warn('Failed to clear add category input on modal hide', e);
                }
            });
        }

        // NEW: delegated handler for inline delete buttons (works for Add Supply, Edit, Dynamic modals)
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.delete-category-inline');
            if (!btn) return;
            e.preventDefault();
            // find nearest select.category-select
            const group = btn.closest('.input-group') || btn.closest('.modal') || document;
            const sel = group ? group.querySelector('select.category-select') : document.querySelector('select.category-select');
            const name = sel ? (sel.value || '').trim() : '';
            if (!name) {
                // no selection -- small feedback
                if (sel) {
                    sel.classList.add('is-invalid');
                    setTimeout(() => sel.classList.remove('is-invalid'), 1200);
                } else {
                    alert('No category selected to delete.');
                }
                return;
            }
            // open styled confirm modal; on confirm run deletion
            openCategoryDeleteConfirm(name, function (n) {
                const ok = deleteCategoryByName(n);
                if (ok) {
                    showCategoryDeletedToast(n);
                    // update main filter dropdown immediately after inline deletion
                    refreshCategoryControls(n, { deleted: true });
                } else {
                    alert('Failed to delete category. Check console for details.');
                }
            });
        });
    });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u741082352/domains/smis.ojt-ims-bsit.net/public_html/resources/views/Admin/admin.blade.php ENDPATH**/ ?>