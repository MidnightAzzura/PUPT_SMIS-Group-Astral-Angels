

<?php $__env->startSection('content'); ?>
    <link href="<?php echo e(asset('css/report.css')); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/dark-mode.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/dark-mode-switch.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/user-settings-dropdown.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <button class="toggle-sidebar-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <img src="<?php echo e(asset('Pup/puplogo.png')); ?>" style="max-width: 40%; height: auto; padding: 10px; margin: 20px auto;" alt="PUP Logo">
            <h3 style="margin-bottom: 20px;">SMIS</h3>
            <a href="<?php echo e(route(
                strtolower(trim(Auth::user()->role)) === 'superadmin'
                    ? 'Admin.admin'
                    : (strtolower(trim(Auth::user()->role)) === 'admin'
                        ? 'Admin.admin'
                        : 'dashboard.use')
            )); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                <i class="fas fa-home" style="margin-right: 8px;"></i>
                <span>Home</span>
            </a>
             <?php if(Auth::user()->role === 'Admin' || Auth::user()->role === 'SuperAdmin'): ?>        <a href="<?php echo e(route('usersmanage.index')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                <i class="fas fa-users" style="margin-right: 8px;"></i> <span>User Management</span>
            </a>
            <?php endif; ?>
            <a href="<?php echo e(route('notifications.index')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                <i class="fas fa-bell" style="margin-right: 8px;"></i> 
                <span>Notifications</span>
                <?php if(isset($notificationCounts) && $notificationCounts['total_unread'] > 0): ?>
                    <span class="notification-badge"><?php echo e($notificationCounts['total_unread']); ?></span>
                <?php endif; ?>
            </a>
            <a href="#" class="dropdown-btn" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
                <i class="fa fa-caret-down" style="margin-left: 8px;"></i>
            </a>
            <div class="dropdown-container" style="margin: 10px auto; text-align: left;">
                     <?php if(Auth::user()->role === 'User'): ?>
                    <a href="<?php echo e(route('requests.create')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                        <i class="fas fa-plus" style="margin-right: 8px;"></i> <span>Create New Request</span>
                    </a>
                <?php endif; ?>
                <a href="<?php echo e(route('requests.pending')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                    <i class="fas fa-clock" style="margin-right: 8px;"></i> <span>Pending</span>
                </a>
                <a href="<?php echo e(route('requests.approved')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                    <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> <span>Approved</span>
                </a>
                <a href="<?php echo e(route('requests.released')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                    <i class="fas fa-truck" style="margin-right: 8px;"></i> <span>Released</span>
                </a>
                <a href="<?php echo e(route('requests.disapproved')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                    <i class="fas fa-times-circle" style="margin-right: 8px;"></i> <span>Disapproved</span>
                </a>
            </div>
            <a href="<?php echo e(route('reports')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                <i class="fas fa-chart-line" style="margin-right: 8px;"></i> <span>Reports</span>
            </a>
            <a href="<?php echo e(route('help.support')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                <i class="fas fa-question-circle" style="margin-right: 8px;"></i> <span>Help & Support</span>
            </a>

            <!-- NEW: Rate / Questionnaire Button (replace href with your Google Form link) -->
            <a id="rateSystemBtn" href="https://forms.gle/12SD5ZjPTXbsgqzC7" target="_blank" rel="noopener" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px; background: rgba(255,255,255,0.08); border-radius:5px;">
                <i class="fas fa-star-half-alt" style="margin-right: 8px;"></i> <span>Rate Our System</span>
            </a>
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
            <script src="<?php echo e(asset('js/theme-toggle.js')); ?>"></script>
        </div>
    </div>
    
    <!-- Logout Form (hidden) -->
    <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
        <?php echo csrf_field(); ?>
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
        <div class="header-with-export">
            <h2>Reports</h2>
            <div class="export-btn-container">
                <form action="<?php echo e(route('reports.export')); ?>" method="get" class="d-inline" id="exportForm">
                    <input type="hidden" name="start_date" value="<?php echo e($startDate ?? ''); ?>">
                    <input type="hidden" name="end_date" value="<?php echo e($endDate ?? ''); ?>">
                    <input type="hidden" name="status" value="<?php echo e($action ?? 'all'); ?>">
                    <input type="hidden" name="office" value="<?php echo e($office ?? 'all'); ?>">
                    <!-- NEW: include current log type and audit filters -->
                    <input type="hidden" name="log_type" value="<?php echo e($logType ?? 'requests'); ?>">
                    <input type="hidden" name="action_type" value="<?php echo e($actionType ?? 'all'); ?>">
                    <input type="hidden" name="admin_id" value="<?php echo e($adminId ?? 'all'); ?>">
                    <button type="submit" class="btn btn-export" id="exportBtn">
                        <i class="fas fa-file-excel"></i>
                        <span class="export-text">Export to Excel</span>
                        <div class="export-loading">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>
                </form>
            </div>
        </div>
        <div class="content-container">
            <h3>Supply Actions Log</h3>

            <!-- Clarify expected behavior after restore -->
            <div class="alert alert-info" role="alert" style="margin-bottom: 1rem;">
                <i class="fas fa-info-circle"></i>
                Backups exclude Audit and Request logs. Restores preserve existing Audit/Request logs (including SuperAdmin logs).
            </div>

            
            <?php if(session('success')): ?>
                <div class="alert alert-custom alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-custom alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('warning')): ?>
                <div class="alert alert-custom alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo e(session('warning')); ?>

                </div>
            <?php endif; ?>

            <!-- Modern Smart Filter Interface -->
            <div style="background: white; border-radius: 12px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                
                <!-- Header with Log Type Toggle -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                    <h3 style="color: #2c3e50; margin: 0; font-size: 1.5rem;">
                        <i class="fas fa-filter" style="color: #800020;"></i> Filter Reports
                    </h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="pill-btn <?php echo e(($logType ?? 'requests') == 'requests' ? 'active' : ''); ?>" 
                                onclick="switchLogType('requests')"
                                style="background: <?php echo e(($logType ?? 'requests') == 'requests' ? '#800020' : '#e9ecef'); ?>; color: <?php echo e(($logType ?? 'requests') == 'requests' ? 'white' : '#495057'); ?>; border: none; padding: 0.6rem 1.5rem; border-radius: 20px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                            <i class="fas fa-clipboard-list"></i> Request Logs
                        </button>
                        <?php if(Auth::user()->role === 'SuperAdmin' || Auth::user()->role === 'Admin'): ?>
                            <button type="button" class="pill-btn <?php echo e(($logType ?? '') == 'audit' ? 'active' : ''); ?>" 
                                    onclick="switchLogType('audit')"
                                    style="background: <?php echo e(($logType ?? '') == 'audit' ? '#800020' : '#e9ecef'); ?>; color: <?php echo e(($logType ?? '') == 'audit' ? 'white' : '#495057'); ?>; border: none; padding: 0.6rem 1.5rem; border-radius: 20px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                                <i class="fas fa-shield-alt"></i> Admin Audit
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <form action="<?php echo e(route('reports')); ?>" method="get" id="filterForm">
                    <input type="hidden" name="log_type" id="log_type" value="<?php echo e($logType ?? 'requests'); ?>">
                    
                    <!-- Time Period Selection -->
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                        <h5 style="color: #495057; margin-bottom: 1rem; font-size: 1rem;">
                            <i class="fas fa-calendar-alt"></i> Select Time Period
                        </h5>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.75rem; margin-bottom: 1rem;">
                            <button type="button" onclick="setTimePreset('today')" style="background: white; border: 2px solid #dee2e6; padding: 0.75rem; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 500; color: #495057;" onmouseover="this.style.borderColor='#800020'; this.style.color='#800020'" onmouseout="this.style.borderColor='#dee2e6'; this.style.color='#495057'">
                                <i class="fas fa-calendar-day"></i> Today
                            </button>
                            <button type="button" onclick="setTimePreset('week')" style="background: white; border: 2px solid #dee2e6; padding: 0.75rem; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 500; color: #495057;" onmouseover="this.style.borderColor='#800020'; this.style.color='#800020'" onmouseout="this.style.borderColor='#dee2e6'; this.style.color='#495057'">
                                <i class="fas fa-calendar-week"></i> This Week
                            </button>
                            <button type="button" onclick="setTimePreset('month')" style="background: white; border: 2px solid #dee2e6; padding: 0.75rem; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 500; color: #495057;" onmouseover="this.style.borderColor='#800020'; this.style.color='#800020'" onmouseout="this.style.borderColor='#dee2e6'; this.style.color='#495057'">
                                <i class="fas fa-calendar-alt"></i> This Month
                            </button>
                            <button type="button" onclick="setTimePreset('custom')" style="background: white; border: 2px solid #dee2e6; padding: 0.75rem; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 500; color: #495057;" onmouseover="this.style.borderColor='#800020'; this.style.color='#800020'" onmouseout="this.style.borderColor='#dee2e6'; this.style.color='#495057'">
                                <i class="fas fa-calendar-plus"></i> Custom Range
                            </button>
                        </div>
                        
                        <!-- Custom Date Range -->
                        <div id="customDateRange" style="display: none; margin-top: 1rem;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; color: #495057; font-weight: 500;">
                                        <i class="fas fa-calendar-check"></i> Start Date
                                    </label>
                                    <input type="date" name="start_date" id="start_date" value="<?php echo e($startDate ?? ''); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px; font-size: 0.95rem;">
                                </div>
                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; color: #495057; font-weight: 500;">
                                        <i class="fas fa-calendar-times"></i> End Date
                                    </label>
                                    <input type="date" name="end_date" id="end_date" value="<?php echo e($endDate ?? ''); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px; font-size: 0.95rem;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Request Log Filters -->
                    <div id="request-filters" style="<?php echo e(($logType ?? 'requests') == 'requests' ? '' : 'display:none;'); ?>">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; color: #495057; font-weight: 600;">
                                    <i class="fas fa-list-check"></i> Status
                                </label>
                                <select name="status" id="status" style="width: 100%; padding: 0.75rem; border: 2px solid #dee2e6; border-radius: 6px; font-size: 0.95rem; background: white;">
                                    <option value="all" <?php echo e(($action ?? '') == 'all' ? 'selected' : ''); ?>>All Statuses</option>
                                    <?php $__currentLoopData = $actions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($act); ?>" <?php echo e(($action ?? '') == $act ? 'selected' : ''); ?>><?php echo e(ucfirst($act)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <?php
                                $userRole = strtolower(trim(Auth::user()->role));
                                $userOffice = Auth::user()->Office ?? 'N/A';
                                if (!in_array($userRole, ['superadmin', 'admin'])) {
                                    if (isset($logs) && $logs instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                                        $filtered = $logs->getCollection()->filter(function($log) use ($userOffice) {
                                            return optional($log->user)->Office === $userOffice || ($log->office ?? null) === $userOffice;
                                        });
                                        $logs->setCollection($filtered->values());
                                    } elseif (isset($logs)) {
                                        $logs = collect($logs)->filter(function($log) use ($userOffice) {
                                            return optional($log->user)->Office === $userOffice || ($log->office ?? null) === $userOffice;
                                        })->values();
                                    }
                                    if (isset($offices) && is_iterable($offices)) {
                                        $offices = [$userOffice];
                                    }
                                }
                            ?>

                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; color: #495057; font-weight: 600;">
                                    <i class="fas fa-building"></i> Office
                                </label>
                                <?php if(in_array($userRole, ['superadmin', 'admin'])): ?>
                                    <select name="office" id="office" style="width: 100%; padding: 0.75rem; border: 2px solid #dee2e6; border-radius: 6px; font-size: 0.95rem; background: white;">
                                        <option value="all" <?php echo e(($office ?? '') == 'all' ? 'selected' : ''); ?>>All Offices</option>
                                        <?php $__currentLoopData = $offices ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $off): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($off); ?>" <?php echo e(($office ?? '') == $off ? 'selected' : ''); ?>><?php echo e($off); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                <?php else: ?>
                                    <select name="office" id="office" readonly style="width: 100%; padding: 0.75rem; border: 2px solid #dee2e6; border-radius: 6px; font-size: 0.95rem; background: #e9ecef;">
                                        <option value="<?php echo e($userOffice); ?>" selected><?php echo e($userOffice); ?></option>
                                    </select>
                                    <input type="hidden" name="office" value="<?php echo e($userOffice); ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Audit Log Filters -->
                    <?php if(Auth::user()->role === 'SuperAdmin' || Auth::user()->role === 'Admin'): ?>
                    <div id="audit-filters" style="<?php echo e(($logType ?? '') == 'audit' ? '' : 'display:none;'); ?>">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; color: #495057; font-weight: 600;">
                                    <i class="fas fa-cog"></i> Action Type
                                </label>
                                <select name="action_type" id="action_type" style="width: 100%; padding: 0.75rem; border: 2px solid #dee2e6; border-radius: 6px; font-size: 0.95rem; background: white;">
                                    <option value="all" <?php echo e(($actionType ?? '') == 'all' ? 'selected' : ''); ?>>All Actions</option>
                                    <?php $__currentLoopData = $actionTypes ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($type); ?>" <?php echo e(($actionType ?? '') == $type ? 'selected' : ''); ?>><?php echo e(ucfirst(strtolower($type))); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; color: #495057; font-weight: 600;">
                                    <i class="fas fa-user-shield"></i> Admin
                                </label>
                                <select name="admin_id" id="admin_id" style="width: 100%; padding: 0.75rem; border: 2px solid #dee2e6; border-radius: 6px; font-size: 0.95rem; background: white;">
                                    <option value="all" <?php echo e(($adminId ?? '') == 'all' ? 'selected' : ''); ?>>All Admins</option>
                                    <?php $__currentLoopData = $admins ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($admin->id); ?>" <?php echo e(($adminId ?? '') == $admin->id ? 'selected' : ''); ?>>
                                            <?php echo e($admin->First_Name); ?> <?php echo e($admin->Last_Name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 1rem; justify-content: center; padding-top: 1rem; border-top: 2px solid #e9ecef;">
                        <button type="submit" style="background: #800020; color: white; border: none; padding: 0.875rem 3rem; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 6px rgba(128, 0, 32, 0.2);" onmouseover="this.style.background='#600018'; this.style.boxShadow='0 6px 12px rgba(128, 0, 32, 0.3)'" onmouseout="this.style.background='#800020'; this.style.boxShadow='0 4px 6px rgba(128, 0, 32, 0.2)'">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="<?php echo e(route('reports')); ?>" style="background: white; color: #6c757d; border: 2px solid #dee2e6; padding: 0.875rem 3rem; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block;" onmouseover="this.style.borderColor='#adb5bd'; this.style.color='#495057'" onmouseout="this.style.borderColor='#dee2e6'; this.style.color='#6c757d'">
                            <i class="fas fa-redo"></i> Reset All
                        </a>
                    </div>
                </form>
            </div>

            <!-- Modern Archive Management -->
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete-Selected')): ?>
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; border: 1px solid #dee2e6;">
                <h4 style="margin-bottom: 1.5rem; color: #333; font-size: 1.1rem;">
                    <i class="fas fa-toolbox"></i> Archive Management
                </h4>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    
                    <!-- Card 1: View Archived -->
                    <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 2px solid #6c63ff; text-align: center;">
                        <div style="background: #6c63ff; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.8rem;">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h5 style="color: #333; font-size: 1.1rem; margin-bottom: 0.5rem;">View Archived Records</h5>
                        <p style="color: #666; font-size: 0.9rem; margin-bottom: 1.5rem;">See all archived items and restore them if needed</p>
                        <button type="button" onclick="showArchivedModal()" style="background: #6c63ff; color: white; border: none; padding: 0.75rem 2rem; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; font-size: 1rem; transition: all 0.2s;" onmouseover="this.style.background='#5548e0'" onmouseout="this.style.background='#6c63ff'">
                            <i class="fas fa-eye"></i> Open Archive
                        </button>
                    </div>
                    
                    <!-- Card 2: Archive Current Results -->
                    <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 2px solid #ff6b6b; text-align: center;">
                        <div style="background: #ff6b6b; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.8rem;">
                            <i class="fas fa-box-archive"></i>
                        </div>
                        <h5 style="color: #333; font-size: 1.1rem; margin-bottom: 0.5rem;">Archive Current Results</h5>
                        <p style="color: #666; font-size: 0.9rem; margin-bottom: 1.5rem;">Move filtered records to archive for safe storage</p>
                        <button type="button" onclick="smartArchive()" style="background: #ff6b6b; color: white; border: none; padding: 0.75rem 2rem; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; font-size: 1rem; transition: all 0.2s;" onmouseover="this.style.background='#ee5a52'" onmouseout="this.style.background='#ff6b6b'">
                            <i class="fas fa-archive"></i> Archive Now
                        </button>
                    </div>
                    
                </div>
                
                <!-- Simple Info Box -->
                <div style="background: #e3f2fd; padding: 1rem; border-radius: 6px; margin-top: 1.5rem; border-left: 4px solid #2196f3;">
                    <i class="fas fa-info-circle" style="color: #2196f3;"></i>
                    <strong style="color: #1976d2;">Quick Tip:</strong> 
                    <span style="color: #555;">Use filters above to select specific records, then archive them. You can restore archived items anytime.</span>
                </div>
            </div>
            <?php endif; ?>

              <div class="table-responsive">
                <?php if(($logType ?? 'requests') == 'requests'): ?>
                    <!-- Request Logs Table -->
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Stock #</th>
                                <th>Office</th>
                                <th>Supply Name</th>
                                <th>Requested Qty</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <!-- NEW -->
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($logs) > 0): ?>
                                <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($log->StockNumber); ?></td>
                                        <td><?php echo e($log->user->Office ?? ($log->office ?? 'N/A')); ?></td>
                                        <td><?php echo e($log->supply_name); ?></td>
                                        <td><?php echo e($log->supply_quantity); ?></td>
                                        <td>
                                            
                                            <?php $s = strtolower($log->status ?? ''); ?>
                                            <?php if($s == 'approved'): ?>
                                                <span class="badge badge-success"><?php echo e(ucfirst($s)); ?></span>
                                            <?php elseif($s == 'released'): ?>
                                                <span class="badge badge-info"><?php echo e(ucfirst($s)); ?></span>
                                            <?php elseif($s == 'pending'): ?>
                                                <span class="badge badge-warning"><?php echo e(ucfirst($s)); ?></span>
                                            <?php elseif($s == 'disapproved' || $s == 'denied'): ?>
                                                <span class="badge badge-danger"><?php echo e(ucfirst($s)); ?></span>
                                            <?php elseif($s == 'expired'): ?>
                                                <span class="badge badge-warning"><?php echo e(ucfirst($s)); ?></span>
                                            <?php elseif($s == 'cancelled'): ?>
                                                <span class="badge badge-secondary"><?php echo e(ucfirst($s)); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?php echo e(ucfirst($s ?: 'unknown')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(\Carbon\Carbon::parse($log->created_at)->setTimezone('Asia/Manila')->format('M d, Y g:i A')); ?> PHT</td>
                                        <!-- NEW -->
                                        <td><?php echo e(\Carbon\Carbon::parse($log->updated_at ?? $log->created_at)->setTimezone('Asia/Manila')->format('M d, Y g:i A')); ?> PHT</td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No request logs found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php elseif(($logType ?? '') == 'audit'): ?>
                    <!-- Audit Logs Table -->
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Type</th>
                                <th>Date/Time</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($logs) > 0): ?>
                                <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <span class="record-type <?php echo e(($logType ?? 'requests') == 'requests' ? 'requests' : 'audit'); ?>">
                                                <?php echo e(($logType ?? 'requests') == 'requests' ? 'Request Log' : 'Audit Log'); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e(\Carbon\Carbon::parse($log->performed_at)->setTimezone('Asia/Manila')->format('M d, Y g:i A')); ?> PHT</td>
                                        <td>
                                            <?php if($log->admin): ?>
                                                <?php echo e($log->admin->First_Name); ?> <?php echo e($log->admin->Last_Name); ?>

                                            <?php else: ?>
                                                <span class="text-muted"><?php echo e($log->admin_name ?? 'Unknown Admin'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($log->action_type): ?>
                                                <span class="badge 
                                                    <?php if(strtolower($log->action_type) == 'create' || strtolower($log->action_type) == 'created'): ?>
                                                        badge-success
                                                    <?php elseif(strtolower($log->action_type) == 'update' || strtolower($log->action_type) == 'updated'): ?>
                                                        badge-warning
                                                    <?php elseif(strtolower($log->action_type) == 'delete' || strtolower($log->action_type) == 'deleted'): ?>
                                                        badge-danger
                                                    <?php elseif(strtolower($log->action_type) == 'approve' || strtolower($log->action_type) == 'approved'): ?>
                                                        badge-success
                                                    <?php elseif(strtolower($log->action_type) == 'deny' || strtolower($log->action_type) == 'denied'): ?>
                                                        badge-danger
                                                    <?php else: ?>
                                                        badge-info
                                                    <?php endif; ?>
                                                ">
                                                    <?php echo e(ucfirst(strtolower(str_replace('_', ' ', $log->action_type)))); ?>

                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Unknown</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($log->description): ?>
                                                <div class="audit-details">
                                                    <?php echo e($log->description); ?>

                                                </div>
                                            <?php elseif($log->target_name || $log->old_values || $log->new_values): ?>
                                                <div class="audit-details">
                                                    <?php
                                                        $actionType = strtolower($log->action_type ?? '');
                                                        $targetType = $log->target_type ?? 'item';
                                                        $targetName = $log->target_name ?? 'Unknown';
                                                    ?>
                                                    
                                                    <?php if($actionType == 'create' || $actionType == 'created'): ?>
                                                        <strong>Added new <?php echo e($targetType); ?>:</strong> <?php echo e($targetName); ?>

                                                    <?php elseif($actionType == 'update' || $actionType == 'updated'): ?>
                                                        <strong>Edited <?php echo e($targetType); ?>:</strong> <?php echo e($targetName); ?>

                                                        <?php if($log->old_values && $log->new_values): ?>
                                                            <br><small class="text-muted">
                                                                <?php
                                                                    $oldVals = is_string($log->old_values) ? json_decode($log->old_values, true) : $log->old_values;
                                                                    $newVals = is_string($log->new_values) ? json_decode($log->new_values, true) : $log->new_values;
                                                                ?>
                                                                <?php if(is_array($oldVals) && is_array($newVals)): ?>
                                                                    <?php $__currentLoopData = $newVals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $newVal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <?php if(isset($oldVals[$key]) && $oldVals[$key] != $newVal): ?>
                                                                            Changed <?php echo e($key); ?> from "<?php echo e($oldVals[$key]); ?>" to "<?php echo e($newVal); ?>"<br>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                <?php endif; ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    <?php elseif($actionType == 'delete' || $actionType == 'deleted'): ?>
                                                        <strong>Deleted <?php echo e($targetType); ?>:</strong> <?php echo e($targetName); ?>

                                                    <?php elseif($actionType == 'approve' || $actionType == 'approved'): ?>
                                                        <strong>Approved <?php echo e($targetType); ?>:</strong> <?php echo e($targetName); ?>

                                                    <?php elseif($actionType == 'deny' || $actionType == 'denied'): ?>
                                                        <strong>Denied <?php echo e($targetType); ?>:</strong> <?php echo e($targetName); ?>

                                                    <?php else: ?>
                                                        <strong><?php echo e(ucfirst($actionType)); ?> <?php echo e($targetType); ?>:</strong> <?php echo e($targetName); ?>

                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No details available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        No audit logs found.
                                        <a href="<?php echo e(route('reports', ['log_type' => 'audit', 'action_type' => 'all', 'admin_id' => 'all'])); ?>">Reset filters</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php elseif(($logType ?? '') == 'user'): ?>
                    <!-- User Logs Table -->
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Action</th>
                                <th>Device</th>
                                <th>IP</th>
                                <th>Country</th>
                                <th>Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($logs) > 0): ?>
                                <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(optional($log->user)->First_Name ? (optional($log->user)->First_Name . ' ' . optional($log->user)->Last_Name) : ($log->user_name ?? 'Unknown')); ?></td>
                                        <td><?php echo e(optional($log->user)->email ?? ($log->email ?? 'N/A')); ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php if(strtolower($log->action) == 'login' || strtolower($log->action) == 'success'): ?> badge-success
                                                <?php elseif(strtolower($log->action) == 'failed' || strtolower($log->action) == 'error'): ?> badge-danger
                                                <?php else: ?> badge-info <?php endif; ?>
                                            ">
                                                <?php echo e(ucfirst(strtolower($log->action ?? $log->user_action ?? 'Unknown'))); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($log->device ?? ($log->user_device ?? 'Unknown')); ?></td>
                                        <td><?php echo e($log->ip_address ?? 'N/A'); ?></td>
                                        <td><?php echo e($log->country ?? 'N/A'); ?></td>
                                        <td><?php echo e(\Carbon\Carbon::parse($log->created_at ?? $log->performed_at ?? $log->time)->setTimezone('Asia/Manila')->format('M d, Y g:i A')); ?> PHT</td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No user logs found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <!-- fallback to requests if unknown -->
                    <!-- Request Logs Table -->
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Stock #</th>
                                <th>Office</th>
                                <th>Supply Name</th>
                                <th>Requested Qty</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <!-- NEW -->
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($logs) > 0): ?>
                                <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($log->StockNumber); ?></td>
                                        <td><?php echo e($log->user->Office ?? ($log->office ?? 'N/A')); ?></td>
                                        <td><?php echo e($log->supply_name); ?></td>
                                        <td><?php echo e($log->supply_quantity); ?></td>
                                        <td>
                                            <?php $s = strtolower($log->status ?? ''); ?>
                                            <?php if($s == 'approved'): ?>
                                                <span class="badge badge-success"><?php echo e(ucfirst($s)); ?></span>
                                            <?php elseif($s == 'released'): ?>
                                                <span class="badge badge-info"><?php echo e(ucfirst($s)); ?></span>
                                            <?php elseif($s == 'pending'): ?>
                                                <span class="badge badge-warning"><?php echo e(ucfirst($s)); ?></span>
                                            <?php elseif($s == 'disapproved' || $s == 'denied'): ?>
                                                <span class="badge badge-danger"><?php echo e(ucfirst($s)); ?></span>
                                            <?php elseif($s == 'expired'): ?>
                                                <span class="badge badge-warning"><?php echo e(ucfirst($s)); ?></span>
                                            <?php elseif($s == 'cancelled'): ?>
                                                <span class="badge badge-secondary"><?php echo e(ucfirst($s)); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?php echo e(ucfirst($s ?: 'unknown')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($log->created_at); ?></td>
                                        <!-- NEW -->
                                        <td><?php echo e(\Carbon\Carbon::parse($log->updated_at ?? $log->created_at)->setTimezone('Asia/Manila')->format('M d, Y g:i A')); ?> PHT</td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No request logs found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
              <div class="pagination-container mt-4">
                <div class="pagination-info">
                    <?php if(isset($logs) && $logs->total() > 0): ?>
                        Showing <?php echo e($logs->firstItem()); ?> to <?php echo e($logs->lastItem()); ?> of <?php echo e($logs->total()); ?> results
                    <?php endif; ?>
                </div>
                <nav>
                    <ul class="pagination pagination-sm">
                        <?php if(isset($logs) && $logs->total() > 20): ?>
                            <?php echo e($logs->appends([
                                'log_type' => $logType ?? 'requests',
                                'start_date' => $startDate ?? '',
                                'end_date' => $endDate ?? '',
                                'status' => $action ?? 'all',
                                'office' => $office ?? 'all',
                                'action_type' => $actionType ?? 'all',
                                'admin_id' => $adminId ?? 'all'
                            ])->links('pagination::bootstrap-4')); ?>

                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>    <script>
        // Provide appData (routes, csrfToken, flash messages) to external JS
        window.appData = {
            routes: {
                getArchivedRecords: "<?php echo e(route('reports.getArchivedRecords')); ?>",
                archiveFiltered: "<?php echo e(route('reports.archiveFiltered')); ?>",
                restoreRecord: "<?php echo e(route('reports.restoreRecord')); ?>",
                restoreMultipleRecords: "<?php echo e(route('reports.restoreMultipleRecords')); ?>"
            },
            csrfToken: "<?php echo e(csrf_token()); ?>",
            flash: {
                <?php if(session('success')): ?> success: "<?php echo e(session('success')); ?>", <?php endif; ?>
                <?php if(session('error')): ?> error: "<?php echo e(session('error')); ?>", <?php endif; ?>
                <?php if(session('warning')): ?> warning: "<?php echo e(session('warning')); ?>", <?php endif; ?>
            }
        };
    </script>
    <script src="<?php echo e(asset('js/report.js')); ?>"></script>

    <!-- Fallback: mirror admin logout logic if report.js fails to define it -->
    <script>
        if (typeof window.confirmLogout !== 'function') {
            (function(){
                var pending = null;
                function performLogoutSubmit() {
                    var form = document.getElementById('logout-form');
                    if (form) { try { form.submit(); return; } catch (e) {} }
                    var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || (window.appData && window.appData.csrfToken) || '';
                    return fetch('/logout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({})
                    }).then(function(){ window.location.href = '/'; });
                }
                function confirmLogout() {
                    try {
                        var btnText = document.getElementById('logoutBtnText');
                        var btnSpinner = document.getElementById('logoutBtnSpinner');
                        var confirmBtn = document.getElementById('confirmLogoutBtn');
                        if (btnText) btnText.classList.add('d-none');
                        if (btnSpinner) btnSpinner.classList.remove('d-none');
                        if (confirmBtn) confirmBtn.disabled = true;

                        pending = setTimeout(function(){
                            pending = null;
                            var p = performLogoutSubmit();
                            if (p && p.catch) {
                                p.catch(function(){
                                    if (confirmBtn) confirmBtn.disabled = false;
                                    if (btnSpinner) btnSpinner.classList.add('d-none');
                                    if (btnText) btnText.classList.remove('d-none');
                                });
                            }
                        }, 800);
                    } catch (e) {
                        var form = document.getElementById('logout-form');
                        if (form) try { form.submit(); } catch (err) { window.location.reload(); }
                    }
                }
                function cancelLogout() {
                    if (pending) { clearTimeout(pending); pending = null; }
                    var btnText = document.getElementById('logoutBtnText');
                    var btnSpinner = document.getElementById('logoutBtnSpinner');
                    var confirmBtn = document.getElementById('confirmLogoutBtn');
                    if (confirmBtn) confirmBtn.disabled = false;
                    if (btnSpinner) btnSpinner.classList.add('d-none');
                    if (btnText) btnText.classList.remove('d-none');
                }
                window.performLogoutSubmit = performLogoutSubmit;
                window.confirmLogout = confirmLogout;
                window.cancelLogout = cancelLogout;

                document.addEventListener('DOMContentLoaded', function () {
                    var logoutModalEl = document.getElementById('logoutConfirmModal');
                    if (logoutModalEl) {
                        logoutModalEl.addEventListener('hidden.bs.modal', function(){ cancelLogout(); });
                    }
                });
            })();
        }
    </script>

    <!-- Simple Logout Confirmation Modal -->
    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #800020; color: white;">
                    <h5 class="modal-title" id="logoutConfirmModalLabel">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout Confirmation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p style="font-size: 16px; color: #333;">Are you sure you want to logout?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" onclick="cancelLogout()">Stay Logged In</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmLogoutBtn" onclick="confirmLogout()">
                        <span id="logoutBtnText">Yes, Logout</span>
                        <span id="logoutBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Archive History Modal (keeps current design; adds pagination) -->
    <div class="modal fade" id="archivedHistoryModal" tabindex="-1" aria-labelledby="archivedHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(90deg,#6f42c1,#6f42c1); color: #fff;">
                    <h5 class="modal-title" id="archivedHistoryModalLabel"><i class="fas fa-archive me-2"></i>Archived Records History</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3" style="gap:12px;">
                        <div style="display:flex;gap:8px;align-items:center;">
                            <!-- header checkbox (matches report.js toggleSelectAll API) -->
                            <label style="margin:0;display:flex;align-items:center;gap:8px;">
                                <input id="selectAllCheckbox" type="checkbox" onchange="toggleSelectAll(this.checked)">
                                <span style="font-weight:600;margin-left:4px;">Select All</span>
                            </label>

                            <!-- NEW: archive scope selector -->
                            <label style="margin:0;display:flex;align-items:center;gap:6px;">
                                <select id="archiveScopeSelect" onchange="archiveScopeChanged()" style="padding:6px;border-radius:6px;">
                                     <option value="current">Current Page</option>
                                     <option value="upto">Up To Page</option>
                                     <option value="all">All Pages</option>
                                </select>
                            </label>
                            <input id="archiveUpToPage" type="number" min="1" value="1" placeholder="Page #" style="width:84px;padding:6px;border-radius:6px;display:none;" title="Specify up to which page to include">
                            
                            <button type="button" class="btn btn-sm btn-outline-primary btn-restore-selected" onclick="restoreSelectedArchived()" disabled title="Restore selected archived records">
                                <i class="fas fa-undo"></i> Restore Selected (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div class="text-muted" style="display:flex;gap:8px;align-items:center;">
                                <i class="fas fa-box-archive"></i> Total Archived: <span id="archivedTotalCount">0</span>
                            </div>
                            <button id="exportArchivedPageBtn" type="button" class="btn btn-sm btn-danger" onclick="exportArchivedToPDF()">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </div>
                    </div>

                     <div class="table-responsive" style="max-height: 420px; overflow:auto;">
                         <table class="table table-hover table-striped">
                             <thead>
                                 <tr>
-                                    <th>Type</th>
+                                    <th style="width:48px;"></th>
+                                    <th>Type</th>
                                     <th>Reference</th>
                                     <th>Description</th>
                                     <th>Archived Date</th>
                                     <th>Archived By</th>
                                     <th width="120">Actions</th>
                                 </tr>
                             </thead>
                             <tbody id="archivedTbody">
                                 <!-- injected rows -->
                             </tbody>
                         </table>
                     </div>

                     <!-- Pagination (20 per page) -->
                     <nav aria-label="Archived pagination" class="mt-3">
                         <ul class="pagination justify-content-center" id="archivedPagination">
                             <!-- injected pagination items -->
                         </ul>
                     </nav>
                 </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Archived pagination & UI
        (function () {
            const apiRoute = window.appData && window.appData.routes && window.appData.routes.getArchivedRecords ? window.appData.routes.getArchivedRecords : "<?php echo e(route('reports.getArchivedRecords')); ?>";
            const restoreRoute = window.appData && window.appData.routes && window.appData.routes.restoreRecord ? window.appData.routes.restoreRecord : "<?php echo e(route('reports.restoreRecord')); ?>";
            const restoreMultipleRoute = window.appData && window.appData.routes && window.appData.routes.restoreMultipleRecords ? window.appData.routes.restoreMultipleRecords : "<?php echo e(route('reports.restoreMultipleRecords')); ?>";
            let currentPage = 1;
            let lastPage = 1;
            const perPage = 20;

            // AbortController for cancelling in-flight page fetches
            let currentFetchController = null;

            // Global selection state for archived items (supports select-all across pages)
            window.archivedSelection = window.archivedSelection || {
                allPages: false,
                deselected: new Set(),
                total: 0
            };

            window.showArchivedModal = function(page = 1) {
                currentPage = page;
                const modalEl = document.getElementById('archivedHistoryModal');
                if (modalEl && window.bootstrap && bootstrap.Modal) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
                // Use the paginated fetcher
                window.fetchArchivedPage(page);
            };

            function fetchArchivedPage(page = 1) {
                const tbody = document.getElementById('archivedTbody');
                const pagination = document.getElementById('archivedPagination');
                const totalCountEl = document.getElementById('archivedTotalCount');
                const restoreCountEl = document.getElementById('restoreSelectedCount');

                if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
                if (pagination) pagination.innerHTML = '';

                // Reset/reflect page-level UI while preserving global selection state
                if (restoreCountEl) {
                    restoreCountEl.textContent = window.archivedSelection.allPages ? (window.archivedSelection.total - window.archivedSelection.deselected.size) : '0';
                    const restoreBtn = document.getElementById('restoreSelectedBtn');
                    if (restoreBtn) restoreBtn.disabled = !(window.archivedSelection.allPages ? (window.archivedSelection.total - window.archivedSelection.deselected.size) > 0 : false);
                }

                // Abort any previous page fetch
                try { if (currentFetchController) currentFetchController.abort(); } catch (e) {}
                currentFetchController = new AbortController();
                const signal = currentFetchController.signal;

                const url = apiRoute + '?page=' + page + '&per_page=' + perPage;

                fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': window.appData ? window.appData.csrfToken : (document.querySelector('meta[name="csrf-token"]') || {}).content
                    },
                    signal: signal
                })
                .then(res => res.json())
                .then(data => {
                    if (!data || !data.success) {
                        if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Failed to load archived records.</td></tr>';
                        return;
                    }

                    const records = data.records || [];
                    // expose current page records for exportArchivedToPDF()
                    try { window.latestArchivedRecords = records; } catch (e) { window.latestArchivedRecords = null; }
                    const paginationMeta = data.pagination || {};
                    lastPage = paginationMeta.last_page || 1;
                    const total = paginationMeta.total || records.length;
                    window.archivedSelection.total = total;
                    if (totalCountEl) totalCountEl.textContent = total;

                    if (records.length === 0) {
                        if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No archived records found.</td></tr>';
                    } else {
                        if (tbody) tbody.innerHTML = '';
                        records.forEach(r => {
                            const tr = document.createElement('tr');

                            // checkbox cell (works with report.js helpers)
                            const checkboxTd = document.createElement('td');
                            checkboxTd.style.verticalAlign = 'middle';
                            const cb = document.createElement('input');
                            cb.type = 'checkbox';
                            cb.className = 'restore-checkbox';
                            cb.value = r.id;
                            cb.dataset.type = r.type || '';
                            cb.addEventListener('change', function () { try { updateSelectedCount(); } catch (e) { /* ignore */ } });
                            checkboxTd.appendChild(cb);

                            const typeTd = document.createElement('td'); typeTd.innerHTML = `<span class="badge bg-primary text-white">${escapeHtml(r.type)}</span>`;
                            const refTd = document.createElement('td'); refTd.innerHTML = escapeHtml(r.reference ?? '');
                            const descTd = document.createElement('td'); descTd.innerHTML = escapeHtml(r.description ?? '');
                            const dateTd = document.createElement('td'); dateTd.innerHTML = escapeHtml(new Date(r.archived_at).toLocaleString());
                            const byTd = document.createElement('td'); byTd.innerHTML = escapeHtml(r.archived_by ?? 'Unknown');
                            const actionsTd = document.createElement('td');
                            actionsTd.innerHTML = `<button class="btn btn-sm btn-info" onclick="restoreSingleArchived(${r.id}, '${r.type}')"><i class="fas fa-undo"></i> Restore</button>`;

                            tr.appendChild(checkboxTd);
                            tr.appendChild(typeTd);
                            tr.appendChild(refTd);
                            tr.appendChild(descTd);
                            tr.appendChild(dateTd);
                            tr.appendChild(byTd);
                            tr.appendChild(actionsTd);

                            if (tbody) tbody.appendChild(tr);
                        });

                        // If global select-all/up-to mode is active, mark visible checkboxes based on cached records
                        try {
                            if (window._archiveSelectAllMode && Array.isArray(window.latestArchivedRecordsAll)) {
                                // create a quick lookup set of ids (numbers or strings)
                                const idSet = new Set(window.latestArchivedRecordsAll.map(function(rr){ return String(rr.id); }));
                                document.querySelectorAll('.restore-checkbox').forEach(function(cb){
                                    try { cb.checked = idSet.has(String(cb.value)); } catch(e){}
                                });
                                // show global selected count and enable restore button
                                var sc = document.getElementById('selectedCount');
                                if (sc) sc.textContent = window.latestArchivedRecordsAll.length;
                                var restoreBtn = document.querySelector('.btn-restore-selected');
                                if (restoreBtn) restoreBtn.disabled = (window.latestArchivedRecordsAll.length === 0);
                                var selectAllCheckbox = document.getElementById('selectAllCheckbox');
                                if (selectAllCheckbox) { selectAllCheckbox.checked = true; selectAllCheckbox.indeterminate = false; }
                            }
                        } catch (ex) { /* ignore if not available */ }
                        // sync header checkbox / counts after rendering page
                        try { updateSelectedCount(); } catch (e) {}
                     }

                     // expose last page for UI hints
                     try { window.latestArchivedLastPage = lastPage; } catch (e) {}
                     buildArchivedPagination(paginationMeta.current_page || page, lastPage);
                 })
                .catch(err => {
                    if (err && err.name === 'AbortError') {
                        // aborted by user navigation; ignore
                        return;
                    }
                    if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">An error occurred while loading archived records.</td></tr>';
                });
            }

            // expose fetchArchivedPage so other scripts (legacy) can call it safely
            window.fetchArchivedPage = fetchArchivedPage;

            function buildArchivedPagination(current, last) {
                const ul = document.getElementById('archivedPagination');
                ul.innerHTML = '';

                const createItem = (label, page, disabled = false, active = false) => {
                    const li = document.createElement('li');
                    li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
                    const a = document.createElement('a');
                    a.className = 'page-link';
                    a.href = 'javascript:void(0);';
                    a.textContent = label;
                    a.onclick = function () { if (!disabled && !active) { fetchArchivedPage(page); } };
                    li.appendChild(a);
                    return li;
                };

                ul.appendChild(createItem('Prev', Math.max(1, current - 1), current === 1));

                const windowSize = 5;
                let start = Math.max(1, current - Math.floor(windowSize / 2));
                let end = Math.min(last, start + windowSize - 1);
                if (end - start < windowSize - 1) start = Math.max(1, end - windowSize + 1);

                if (start > 1) {
                    ul.appendChild(createItem('1', 1));
                    if (start > 2) {
                        const dots = document.createElement('li'); dots.className = 'page-item disabled'; dots.innerHTML = '<span class="page-link">…</span>';
                        ul.appendChild(dots);
                    }
                }

                for (let p = start; p <= end; p++) {
                    ul.appendChild(createItem(p, p, false, p === current));
                }

                if (end < last) {
                    if (end < last - 1) {
                        const dots = document.createElement('li'); dots.className = 'page-item disabled'; dots.innerHTML = '<span class="page-link">…</span>';
                        ul.appendChild(dots);
                    }
                    ul.appendChild(createItem(last, last));
                }

                ul.appendChild(createItem('Next', Math.min(last, current + 1), current === last));
            }

            // small UI glue: show/hide up-to input when scope changes
            try {
                document.addEventListener('DOMContentLoaded', function () {
                    var scope = document.getElementById('archiveScopeSelect');
                    var upTo = document.getElementById('archiveUpToPage');
                    if (scope && upTo) {
                        scope.addEventListener('change', function () {
                            if (this.value === 'upto') upTo.style.display = 'inline-block';
                            else upTo.style.display = 'none';
                        });
                    }
                });
            } catch (e) { /* ignore */ }

            // Restore single archived record (unchanged logic)
            window.restoreSingleArchived = function(id, type) {
                if (!confirm('Restore this archived record?')) return;
                fetch(restoreRoute, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.appData ? window.appData.csrfToken : document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ record_id: id, record_type: type })
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        fetchArchivedPage(currentPage);
                    } else {
                        alert(data.message || 'Failed to restore record.');
                    }
                })
                .catch(() => alert('Failed to restore record.'));
            };

            // Restore selected (multiple) archived records — scope-aware: current / upto / all
            window.restoreSelectedArchived = function() {
                var scopeEl = document.getElementById('archiveScopeSelect');
                var scope = scopeEl ? scopeEl.value : 'current';

                if (scope === 'current') {
                    // Collect checked IDs from current page only (existing behavior)
                    var checked = Array.from(document.querySelectorAll('.restore-checkbox:checked'));
                    if (!checked.length) { showToast('Please select records to restore', 'warning'); return; }
                    if (!confirm('Restore selected archived records?')) return;
                    var records = checked.map(function(cb){ return { id: parseInt(cb.value), type: cb.dataset.type }; });
                    fetch(restoreMultipleRoute, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.appData ? window.appData.csrfToken : document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ records: records })
                    })
                    .then(res => res.json())
                    .then(function(data){
                        if (data && data.success) {
                            showToast('Selected records restored', 'success');
                            fetchArchivedPage(currentPage);
                        } else {
                            alert(data && data.message ? data.message : 'Failed to restore selected records.');
                        }
                    })
                    .catch(function(){ alert('Failed to restore selected records.'); });
                    return;
                }

                if (scope === 'upto') {
                    var uptoInput = document.getElementById('archiveUpToPage');
                    var upto = uptoInput ? parseInt(uptoInput.value, 10) : NaN;
                    if (!upto || upto < 1) { alert('Please enter a valid page number'); return; }
                    if (!confirm('Restore archived records from page 1 up to page ' + upto + '?')) return;

                    // fetch pages 1..upto via function in report.js (fetchArchivedPages)
                    try {
                        showToast('Fetching pages 1..' + upto + '...', 'info');
                        if (typeof window.fetchArchivedPages !== 'function') {
                            alert('Cannot fetch pages: helper not available.');
                            return;
                        }
                        window.fetchArchivedPages(upto).then(function(records){
                            if (!records || !records.length) { alert('No records found for selected pages'); return; }
                            var payload = records.map(function(r){ return { id: r.id, type: r.type || '' }; });
                            fetch(restoreMultipleRoute, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': window.appData ? window.appData.csrfToken : document.querySelector('meta[name="csrf-token"]').content,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({ records: payload })
                            })
                            .then(function(res){ return res.json(); })
                            .then(function(data){
                                if (data && data.success) {
                                    showToast('Restored ' + (payload.length) + ' record(s)', 'success');
                                    fetchArchivedPage(currentPage);
                                } else {
                                    alert(data && data.message ? data.message : 'Failed to restore records.');
                                }
                            })
                            .catch(function(){ alert('Failed to restore records.'); });
                        }).catch(function(err){
                            console.error(err);
                            alert('Failed to fetch archived pages for restore.');
                        });
                    } catch (e) {
                        console.error(e); alert('Error restoring records'); 
                    }
                    return;
                }

                if (scope === 'all') {
                    if (!confirm('Restore all archived records? This will attempt to restore every archived record.')) return;
                    // Send select_all request (backend previously supported this payload)
                    fetch(restoreMultipleRoute, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.appData ? window.appData.csrfToken : document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ select_all: true })
                    })
                    .then(function(res){ return res.json(); })
                    .then(function(data){
                        if (data && data.success) {
                            showToast('All archived records restored', 'success');
                            fetchArchivedPage(currentPage);
                        } else {
                            alert(data && data.message ? data.message : 'Failed to restore all archived records.');
                        }
                    })
                    .catch(function(){ alert('Failed to restore archived records.');});
                    return;
                }
            };
            // end restoreSelectedArchived

            // NEW: archive scope change handler
            window.archiveScopeChanged = function() {
                var scopeEl = document.getElementById('archiveScopeSelect');
                var scope = scopeEl ? scopeEl.value : 'current';

                if (scope === 'upto' || scope === 'all') {
                    // enable global selection for upto/all
                    toggleSelectAll(true);
                } else {
                    // clear global selection for current
                    toggleSelectAll(false);
                }

                if (scope === 'upto') {
                    var uptoInput = document.getElementById('archiveUpToPage');
                    var upto = uptoInput ? parseInt(uptoInput.value, 10) : NaN;
                    if (!upto || upto < 1) { 
                        showToast('Please enter a valid page number', 'warning'); 
                        uptoInput.focus();
                        return; 
                    }
                }
            };
        })();
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Web Dev 3-1\Activity 1_Dela Cruz\SMIS40\resources\views/reports.blade.php ENDPATH**/ ?>