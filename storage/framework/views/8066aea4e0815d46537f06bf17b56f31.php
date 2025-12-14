

<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/use.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/modern-use.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/notification-badges.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/inventory-print.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/user-settings-dropdown.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dark-mode.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dark-mode-switch.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/header-fix.css')); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="<?php echo e(asset('js/inventory-print.js')); ?>"></script>

<body style="background-color: #000000;">
    <button class="toggle-sidebar-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
   <div class="sidebar" id="sidebar" style="text-align: center;">
        <div class="sidebar-content">
            <img src="<?php echo e(asset('Pup/puplogo.png')); ?>" style="max-width: 40%; height: auto; padding: 10px; margin: 20px auto;" alt="PUP Logo">
            <h3 style="margin-bottom: 20px;">SMIS</h3>        <a href="<?php echo e(route('dashboard.use')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-home" style="margin-right: 8px;"></i> <span>Home</span>
            </a>        <a href="<?php echo e(route('notifications.index')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-bell" style="margin-right: 8px;"></i> 
                <span>Notifications</span>
                <?php if(isset($notificationCounts)): ?>
                    <span class="notification-badge total-badge" data-type="total"><?php echo e($notificationCounts['total_unread'] ?? 0); ?></span>
                <?php endif; ?>
            </a>
            <a href="#" class="dropdown-btn" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
                <i class="fa fa-caret-down" style="margin-left: 8px;"></i>
            </a>
            <div class="dropdown-container" style="margin: 10px auto; text-align: center;">
                <a href="<?php echo e(route('requests.create')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i> <span>Create New Request</span>
                </a>
                  <a href="<?php echo e(route('requests.pending')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-clock" style="margin-right: 8px;"></i> <span>Pending</span>
                </a>            <a href="<?php echo e(route('requests.approved')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> 
                    <span>Approved</span>
                </a>
                <a href="<?php echo e(route('requests.released')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-truck" style="margin-right: 8px;"></i> <span>Released</span>
                </a>            <a href="<?php echo e(route('requests.disapproved')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
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
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">
                        <i class="fas fa-check-circle text-success me-2"></i>Success
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo e(session('success')); ?>

                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successModalEl = document.getElementById('successModal');
            if (!successModalEl) return;
            
            const successModal = new bootstrap.Modal(successModalEl, {
                backdrop: 'static',
                keyboard: false
            });
            
            // Function to completely remove modal artifacts
            function completeModalCleanup() {
                // Remove modal-open class from body
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                document.body.style.removeProperty('overflow');
                
                // Remove ALL backdrop elements
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => {
                    backdrop.classList.remove('show');
                    backdrop.style.opacity = '0';
                    setTimeout(() => backdrop.remove(), 150);
                });
                
                // Ensure modal is fully hidden
                successModalEl.classList.remove('show');
                successModalEl.style.display = 'none';
                successModalEl.setAttribute('aria-hidden', 'true');
                successModalEl.removeAttribute('aria-modal');
                
                // Force page scroll restore
                document.documentElement.style.overflow = '';
                document.body.style.overflow = '';
            }
            
            // Show the modal
            successModal.show();
            
            // Auto-close after 3 seconds
            const autoCloseTimer = setTimeout(function () {
                successModal.hide();
            }, 3000);
            
            // Handle hidden event - complete cleanup
            successModalEl.addEventListener('hidden.bs.modal', function (event) {
                completeModalCleanup();
                clearTimeout(autoCloseTimer);
            }, { once: true });
            
            // Handle manual close button click
            const closeButton = successModalEl.querySelector('.btn-close');
            if (closeButton) {
                closeButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    clearTimeout(autoCloseTimer);
                    successModal.hide();
                });
            }
            
            // Emergency cleanup on any error
            window.addEventListener('error', function(e) {
                if (e.message && e.message.includes('modal')) {
                    completeModalCleanup();
                }
            });
        });
    </script>
    <?php endif; ?>

    <!-- User Settings Dropdown - Fixed Position with Manual Implementation -->
    <div class="user-settings-dropdown">
        <button class="btn btn-sm btn-dark rounded-circle" id="userSettingsButton" style="width: 38px; height: 38px;" aria-expanded="false" aria-label="User settings and profile" title="Click for user settings">
            <i class="fas fa-user"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end manual-dropdown" id="userSettingsDropdownMenu">
            <h6 class="dropdown-header">Settings</h6>
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#userSettingsModal"><i class="fas fa-user-cog me-2"></i> User Settings</a>
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
            <div class="header-content" style="color: white !important; padding: 0.75rem 1.5rem !important;">
                <div>
                    <h2 class="welcome-text" style="color: #ffffff !important; font-size: 1.5rem !important; margin: 0 !important;">
                        <i class="fas fa-user me-2" style="color: #06b6d4 !important;"></i>Welcome User,
                        <span class="user-name" style="color: #ffffff !important; font-weight: bold !important; background: none !important;"><?php echo e(Auth::user()->First_Name); ?> <?php echo e(Auth::user()->Middle_Initial); ?>. <?php echo e(Auth::user()->Last_Name); ?>!</span>
                    </h2>
                    <p class="header-subtitle" style="color: white !important; margin: 0.25rem 0 0 0 !important; font-size: 0.9rem !important;">View inventory and request supplies easily</p>
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
                                    <i class="fas fa-exclamation-circle me-1"></i>Needs restocking
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
                            <p class="card-subtitle">Quick view of available stock levels</p>
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
                                <h3 class="text-warning"><?php echo e($need_restocking_percentage); ?>%</h3>
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

            <!-- Enhanced Supplies Overview -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card modern-card supplies-card" data-aos="fade-up">
                        <div class="card-header modern-card-header">
                            <div class="header-title-section">
                                <h5 class="card-title">
                                    <i class="fas fa-boxes me-2 text-primary"></i>Supplies Overview
                                </h5>
                                <p class="card-subtitle">View all available inventory supplies</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="filter-container modern-filter">
                                <form class="d-flex flex-wrap align-items-center gap-3" method="GET" action="<?php echo e(route('dashboard.use')); ?>">
                                    <div class="filter-group">
                                        <label class="filter-label">Filter by:</label>
                                        <select class="form-control modern-select" name="filter" id="stockFilterDropdown">
                                            <option value="all" <?php if($filter === 'all'): ?> selected <?php endif; ?>>All Stock</option>
                                            <option value="available" <?php if($filter === 'available'): ?> selected <?php endif; ?>>Available Stock</option>
                                            <option value="low" <?php if($filter === 'low'): ?> selected <?php endif; ?>>Low Stock</option>
                                            <option value="no" <?php if($filter === 'no'): ?> selected <?php endif; ?>>No Stock</option>
                                        </select>

                                        <!-- ADDED: Category filter (mirrors admin view) -->
                                        <select class="form-control modern-select ms-2" name="category" id="categoryFilterDropdown" data-selected="<?php echo e($selectedCategory ?? ''); ?>" style="min-width:160px;">
                                            <option value="">All Categories</option>
                                            <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php $c = is_string($cat) ? trim($cat) : $cat; ?>
                                                <?php if($c !== ''): ?>
                                                    <option value="<?php echo e($c); ?>" <?php if(($selectedCategory ?? '') === $c): ?> selected <?php endif; ?>><?php echo e($c); ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="search-group flex-grow-1">
                                        <div class="search-input-container">
                                            <i class="fas fa-search search-icon"></i>
                                            <input class="form-control modern-search" type="search" name="search" placeholder="Search supplies..." value="<?php echo e($search); ?>">
                                        </div>
                                    </div>
                                    <button class="btn btn-outline-success modern-btn" type="submit">
                                        <i class="fas fa-search me-1"></i>Search
                                    </button>
                                </form>
                            </div>
                            
                            <div class="table-container">
                                <table class="table modern-table">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-hashtag me-1"></i>Stock Number</th>
                                            <th class="text-center"><i class="fas fa-info-circle me-1"></i>Item Description</th>
                                            <!-- Added Category column -->
                                            <th><i class="fas fa-tags me-1"></i>Category</th>
                                            <th><i class="fas fa-ruler me-1"></i>Unit</th>
                                            <th><i class="fas fa-signal me-1"></i>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                        <?php $__currentLoopData = $filtered_supplies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            
                                            <?php if(($filter ?? 'all') === 'low'): ?>
                                                <?php $qtyGuard = (int) ($supply->Quantity ?? 0); ?>
                                                <?php if($qtyGuard < 1 || $qtyGuard > 5): ?>
                                                    <?php continue; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <tr class="table-row-hover">
                                            <td class="font-weight-bold"><?php echo e($supply->StockNumber); ?></td>
                                            <td class="text-center"><?php echo e($supply->ItemDescription); ?></td>
                                            <!-- Render Category (safe fallback) -->
                                            <td class="text-center"><span class="category-badge"><?php echo e($supply->Category ?? $supply->category ?? '—'); ?></span></td>
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
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Enhanced Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                <nav>
                                    <ul class="pagination modern-pagination">
                                        <?php echo e($filtered_supplies->links('pagination::bootstrap-4')); ?>

                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p style="color: #333; font-weight: 500; font-size: 16px; text-align: center;">Are you sure you want to logout from the system?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelLogoutBtn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmLogoutBtn" onclick="confirmLogout()">
                        <span id="logoutBtnText">Confirm Logout</span>
                        <span id="logoutBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                        <!-- Added Category column for released items -->
                                        <th class="py-3">
                                            <span class="d-flex align-items-center">
                                                <i class="fas fa-tags me-2"></i>CATEGORY
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
                                        <!-- Render Category for released items -->
                                        <td class="category-cell"><span class="category-badge"><?php echo e($item->Category ?? $item->category ?? '—'); ?></span></td>
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

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- BOOTSTRAP: minimal server-side data for admin.js + category helpers -->
    <script>
        window.__adminData = window.__adminData || {};
        Object.assign(window.__adminData, {
            csrfToken: "<?php echo e(csrf_token()); ?>",
            routes: {
                // supplyHistoryTemplate not required here but keep shape compatible with admin.js
                supplyHistoryTemplate: "<?php echo e(isset($supplyHistoryRoute) ? $supplyHistoryRoute : (route('admin.supply.history', ['__STOCK__']) ?? '')); ?>"
            },
            categories: <?php echo json_encode(($categories ?? collect())->map(function($v){ return is_string($v) ? trim($v) : $v; })->values()->all()); ?>,
            selectedCategory: "<?php echo e($selectedCategory ?? ''); ?>",
            total_available: <?php echo e($total_available ?? 0); ?>,
            total_low_stock: <?php echo e($total_low_stock ?? 0); ?>,
            total_out_of_stock: <?php echo e($total_out_of_stock ?? 0); ?>

        });
    </script>

    <!-- Include shared admin helpers (category population, unit manager, ajax handlers) -->
    <script src="<?php echo e(asset('js/admin.js')); ?>?v=history6"></script>

    <!-- AJAX Pagination Script - User Dashboard Implementation -->
    <script>
        // Optimized AJAX Pagination Handler for User Dashboard
        $(document).ready(function() {
            // Cache DOM elements for performance
            const tableContainer = $('.table-container');
            const paginationContainer = $('.modern-pagination');
            const filterForm = $('.filter-container form');
            const filterDropdown = $('#stockFilterDropdown');
            
            // Initial state tracking
            let currentPage = <?php echo e($filtered_supplies->currentPage()); ?>;
            let currentUrl = window.location.href;
            let currentFilter = '<?php echo e($filter); ?>';
            let currentSearch = '<?php echo e($search); ?>';
            
            // Handle filter dropdown change - AJAX implementation
            filterDropdown.on('change', function() {
                // Get the selected filter value
                const filterValue = $(this).val();
                
                // Show loading immediately for better UX
                showTableLoading();
                
                // Create URL with the filter parameter
                let url = new URL(window.location.href);
                url.searchParams.set('filter', filterValue);
                
                // Preserve any existing search parameter
                if (currentSearch) {
                    url.searchParams.set('search', currentSearch);
                }
                
                // Reset to page 1 when filter changes
                url.searchParams.delete('page');
                
                // Load the filtered content
                loadTableContent(url.toString());
                
                // Update current filter
                currentFilter = filterValue;
            });
            
            // Event delegation for pagination links - works with dynamically loaded content
            $(document).on('click', '.pagination .page-link', function(e) {
                e.preventDefault();
                const targetUrl = $(this).attr('href');
                
                // Don't reload if clicking the current page
                if (targetUrl.includes(`page=${currentPage}`)) {
                    return;
                }
                
                // Show loading state
                showTableLoading();
                
                // Load new page content
                loadTableContent(targetUrl);
            });
            
            // Handle browser back/forward navigation
            window.onpopstate = function(event) {
                if (event.state && event.state.url) {
                    // Parse the URL to extract filter value
                    const url = new URL(event.state.url, window.location.origin);
                    const filterValue = url.searchParams.get('filter') || 'all';
                    const searchValue = url.searchParams.get('search') || '';
                    
                    // Update the filter dropdown to match URL state
                    filterDropdown.val(filterValue);
                    
                    // Update current values
                    currentFilter = filterValue;
                    currentSearch = searchValue;
                    
                    // Load content without pushing state
                    loadTableContent(event.state.url, false);
                }
            };
            
            // Filter form submission - use AJAX instead of page reload
            filterForm.on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                const targetUrl = `${window.location.pathname}?${formData}`;
                
                // Parse form data to update current states
                const formParams = new URLSearchParams(formData);
                currentFilter = formParams.get('filter') || 'all';
                currentSearch = formParams.get('search') || '';
                
                // Show loading state
                showTableLoading();
                
                // Load filtered content
                loadTableContent(targetUrl);
            });
            
            /**
             * Load table content via AJAX
             * @param {string} url - The URL to fetch content from
             * @param {boolean} pushState - Whether to update browser history
             */
            function loadTableContent(url, pushState = true) {
                // Extract page number from URL for state tracking
                const urlObj = new URL(url, window.location.origin);
                const pageParam = urlObj.searchParams.get('page') || 1;
                
                // Add loading class to the container for visual feedback
                $('.supplies-card').addClass('loading-container');
                
                $.ajax({
                    url: url,
                    dataType: 'html',
                    cache: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Parse the HTML response
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(response, 'text/html');
                        
                        // Extract only the table HTML
                        const newTable = doc.querySelector('.table-container');
                        const newPagination = doc.querySelector('.modern-pagination');
                        
                        // Update the table content
                        if (newTable) {
                            tableContainer.html(newTable.innerHTML);
                        }
                        
                        // Update pagination links
                        if (newPagination) {
                            paginationContainer.html(newPagination.innerHTML);
                        }
                        
                        // Update current page and URL for state management
                        currentPage = parseInt(pageParam);
                        currentUrl = url;
                        
                        // Update browser history to maintain proper back/forward navigation
                        if (pushState) {
                            window.history.pushState({ 
                                url: url,
                                filter: currentFilter,
                                search: currentSearch,
                                page: currentPage
                            }, '', url);
                        }
                        
                        // Remove loading class
                        $('.supplies-card').removeClass('loading-container');
                        
                        // Hide loading indicators
                        hideTableLoading();
                        
                        // Scroll to top of table container with smooth animation
                        $('html, body').animate({
                            scrollTop: $('.supplies-card').offset().top - 100
                        }, 200);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Loading Error:', error);
                        
                        // Graceful fallback - redirect to the page on error
                        window.location.href = url;
                        
                        // Hide loading indicators
                        hideTableLoading();
                    }
                });
            }
            
            /**
             * Show table loading state
             */
            function showTableLoading() {
                // Create and add loading overlay if it doesn't exist
                if (!$('#table-loading-overlay').length) {
                    const loadingHTML = `
                        <div id="table-loading-overlay" class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center" 
                             style="top: 0; left: 0; background: rgba(255,255,255,0.8); z-index: 100;">
                            <div class="d-flex flex-column align-items-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="mt-2 fw-bold">Loading data...</span>
                            </div>
                        </div>
                    `;
                    tableContainer.css('position', 'relative').append(loadingHTML);
                } else {
                    $('#table-loading-overlay').fadeIn(200);
                }
                
                // Disable pagination links while loading
                $('.pagination .page-link').addClass('disabled');
            }
            
            /**
             * Hide table loading state
             */
            function hideTableLoading() {
                $('#table-loading-overlay').fadeOut(200);
                
                // Re-enable pagination links
                $('.pagination .page-link').removeClass('disabled');
            }
            
            // Initialize browser history state on page load
            window.history.replaceState({ 
                url: currentUrl,
                filter: currentFilter,
                search: currentSearch,
                page: currentPage
            }, '', currentUrl);
            
            // Add a visual indicator style for loading state
            $('<style>.loading-container { opacity: 0.7; transition: opacity 0.3s ease; }</style>').appendTo('head');
        });
    </script>

    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Logout confirmation functions
        function confirmLogout() {
            const txt = document.getElementById('logoutBtnText');
            const spn = document.getElementById('logoutBtnSpinner');
            const btn = document.getElementById('confirmLogoutBtn');
            if (txt) txt.classList.add('d-none');
            if (spn) spn.classList.remove('d-none');
            if (btn) btn.disabled = true;
            setTimeout(() => document.getElementById('logout-form').submit(), 800);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('logoutConfirmModal');
            const cancelBtn = document.getElementById('cancelLogoutBtn');
            const logoutTrigger = document.querySelector('[data-bs-target="#logoutConfirmModal"]');

            function cleanModalArtifacts() {
                const anyOpen = document.querySelectorAll('.modal.show').length > 0;
                if (!anyOpen) {
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                }
            }

            if (cancelBtn) {
                cancelBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    instance.hide();
                });
            }

            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function () {
                    const confirmBtn = document.getElementById('confirmLogoutBtn');
                    const txt = document.getElementById('logoutBtnText');
                    const spn = document.getElementById('logoutBtnSpinner');
                    if (confirmBtn) confirmBtn.disabled = false;
                    if (txt) txt.classList.remove('d-none');
                    if (spn) spn.classList.add('d-none');
                    if (logoutTrigger) logoutTrigger.blur();
                    cleanModalArtifacts();
                });

                modalEl.addEventListener('hidePrevented.bs.modal', function () {
                    const instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    instance.hide();
                    cleanModalArtifacts();
                });
            }
        });

        /* Replace the simple Chart init with robust destroy/recreate logic */
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('inventoryChart');
            if (!canvas) {
                console.warn('inventoryChart canvas not found');
                return;
            }

            // Ensure Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js is not loaded');
                return;
            }

            // If a previous instance is stored, destroy it
            const stored = window.inventoryChartInstance;
            const retrieved = (typeof Chart.getChart === 'function') ? Chart.getChart(canvas) : null;
            const existing = stored || retrieved;

            if (existing && typeof existing.destroy === 'function') {
                try {
                    existing.destroy();
                } catch (err) {
                    console.warn('Error destroying previous inventoryChart instance:', err);
                }
                window.inventoryChartInstance = null;
            }

            // Create new chart and store instance on window for future destruction
            const ctx = canvas.getContext('2d');
            window.inventoryChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Good Stock', 'Low Stock', 'Out of Stock'],
                    datasets: [{
                        label: 'Items Count',
                        data: [<?php echo e($good_stock); ?>, <?php echo e($low_stock); ?>, <?php echo e($out_of_stock); ?>],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(251, 191, 36, 0.8)', 
                            'rgba(239, 68, 68, 0.8)'
                        ],
                        borderColor: [
                            'rgba(34, 197, 94, 1)',
                            'rgba(251, 191, 36, 1)',
                            'rgba(239, 68, 68, 1)'
                        ],
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            // keep previous look; Chart.js v3+ uses 'borderRadius' for tooltip corner rounding
                            borderRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#64748b'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#64748b'
                            }
                        }
                    },
                    elements: {
                        bar: {
                            borderRadius: 8
                        }
                    }
                }
            });
        });

        function toggleSidebar() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarState', sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded');
        }

        // Set sidebar state on page load
        document.addEventListener('DOMContentLoaded', function () {
            var sidebarState = localStorage.getItem('sidebarState');
            if (sidebarState === 'collapsed') {
                var sidebar = document.querySelector('.sidebar');
                sidebar.classList.add('collapsed');
                var title = sidebar.querySelector('h3');
                title.style.display = 'none';
            }

            // Restore dropdown states
            document.querySelectorAll('.dropdown-btn').forEach(function (btn, index) {
                var dropdownContent = btn.nextElementSibling;
                var dropdownState = localStorage.getItem('dropdownState-' + index);

                if (dropdownState === 'open') {
                    btn.classList.add('active');
                    dropdownContent.style.display = 'block';
                } else {
                    dropdownContent.style.display = 'none';
                }
            });
        });

        // JavaScript to handle dropdown functionality
        document.querySelectorAll('.dropdown-btn').forEach(function (btn, index) {
            btn.addEventListener('click', function () {
                this.classList.toggle('active');
                var dropdownContent = this.nextElementSibling;

                if (dropdownContent.style.display === 'block') {
                    dropdownContent.style.display = 'none';
                    localStorage.setItem('dropdownState-' + index, 'closed');
                } else {
                    dropdownContent.style.display = 'block';
                    localStorage.setItem('dropdownState-' + index, 'open');
                }
            });
        });

        // Initialize filtering functionality for Recently Released modal
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            document.getElementById('filterByStock')?.addEventListener('click', function() {
                this.classList.add('active');
                document.getElementById('filterByItem')?.classList.remove('active');
                document.getElementById('filterByRecipient')?.classList.remove('active');
                document.getElementById('stockFilter').style.display = 'block';
                document.getElementById('itemFilter').style.display = 'none';
                document.getElementById('recipientFilter').style.display = 'none';
                document.getElementById('filterStockNumber').focus();
            });

            document.getElementById('filterByItem')?.addEventListener('click', function() {
                this.classList.add('active');
                document.getElementById('filterByStock')?.classList.remove('active');
                document.getElementById('filterByRecipient')?.classList.remove('active');
                document.getElementById('stockFilter').style.display = 'none';
                document.getElementById('itemFilter').style.display = 'block';
                document.getElementById('recipientFilter').style.display = 'none';
                document.getElementById('filterItemDescription').focus();
            });

            document.getElementById('filterByRecipient')?.addEventListener('click', function() {
                this.classList.add('active');
                document.getElementById('filterByStock')?.classList.remove('active');
                document.getElementById('filterByItem')?.classList.remove('active');
                document.getElementById('stockFilter').style.display = 'none';
                document.getElementById('itemFilter').style.display = 'none';
                document.getElementById('recipientFilter').style.display = 'block';
                document.getElementById('filterReceivedBy').focus();
            });

            // Filter functionality
            document.getElementById('filterStockNumber')?.addEventListener('input', filterReleased);
            document.getElementById('filterItemDescription')?.addEventListener('input', filterReleased);
            document.getElementById('filterReceivedBy')?.addEventListener('input', filterReleased);

            function filterReleased() {
                const stockFilter = document.getElementById('filterStockNumber').value.toLowerCase();
                const itemFilter = document.getElementById('filterItemDescription').value.toLowerCase();
                const recipientFilter = document.getElementById('filterReceivedBy').value.toLowerCase();
                
                const rows = document.querySelectorAll('.released-item-row');
                let visibleRows = 0;
                
                rows.forEach(row => {
                    const stockNumber = row.querySelector('.stock-number').textContent.toLowerCase();
                    const itemDescription = row.querySelector('.item-description').textContent.toLowerCase();
                    const receivedBy = row.querySelector('.received-by').textContent.toLowerCase();
                    
                    const matchesStock = stockNumber.includes(stockFilter);
                    const matchesItem = itemDescription.includes(itemFilter);
                    const matchesRecipient = receivedBy.includes(recipientFilter);
                    
                    if (matchesStock && matchesItem && matchesRecipient) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                  // Show/hide the "no results" message
                const noResultsMsg = document.getElementById('noFilterResults');
                if (noResultsMsg) {
                    noResultsMsg.style.display = visibleRows === 0 ? 'block' : 'none';
                }
            }
        });

        // Enhanced notification badge management
        function updateNotificationBadges(counts) {
            // Update each badge with smooth animations
            updateBadgeElement('notifications', counts.total_unread);
            updateBadgeElement('approved', counts.approved);
            updateBadgeElement('denied', counts.denied);
            updateBadgeElement('pending', counts.pending);
            updateBadgeElement('released', counts.released);
        }

        function updateBadgeElement(type, count) {
            const badges = document.querySelectorAll(`.notification-badge[data-type="${type}"], .notification-badge.${type}-badge`);
            badges.forEach(badge => {
                // SENIOR DEVELOPER FIX: Preserve badges with previous content
                const currentCount = parseInt(badge.textContent) || 0;
                const newCount = parseInt(count) || 0;
                
                if (newCount > 0) {
                    badge.textContent = newCount;
                    badge.style.display = 'inline-block';
                    badge.classList.add('badge-update');
                    badge.setAttribute('data-has-content', 'true');
                    
                    // Remove animation class after animation completes
                    setTimeout(() => {
                        badge.classList.remove('badge-update');
                    }, 300);
                } else if (currentCount === 0 && newCount === 0) {
                    // Only hide if truly empty (never had content)
                    if (!badge.hasAttribute('data-has-content')) {
                        badge.style.display = 'none';
                    }
                }
                // If currentCount > 0 but newCount === 0, preserve the badge (don't hide)
            });
        }

        // Auto-refresh notification badges every 30 seconds
        setInterval(() => {
            fetch('<?php echo e(route("notifications.counts")); ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotificationBadges(data.counts);
                    }
                })
                .catch(error => console.log('Badge update failed:', error));
        }, 30000);
    </script>
    <script src="<?php echo e(asset('js/notification-badges.js')); ?>" defer></script>
    <!-- User Settings Scripts -->
    <script src="<?php echo e(asset('js/user-settings.js')); ?>"></script>
    <script src="<?php echo e(asset('js/user-settings-modal.js')); ?>"></script>
    <script src="<?php echo e(asset('js/dark-mode.js')); ?>"></script>
</body>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u741082352/domains/smis.ojt-ims-bsit.net/public_html/resources/views/dashboard/use.blade.php ENDPATH**/ ?>