

<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/released.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/notification-badges.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dark-mode.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dark-mode-switch.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/user-settings-dropdown.css')); ?>">
<style>
    .dropdown-container {
        display: none !important;
        transition: all 0.3s ease;
    }
    
    .dropdown-container.show {
        display: block !important;
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .dropdown-btn {
        cursor: pointer !important;
        text-decoration: none !important;
    }
    
    .dropdown-btn:hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
    }
    
    .dropdown-btn.active .fa-caret-down {
        transform: rotate(180deg);
        transition: transform 0.3s ease;
    }
</style>
<body>
    <button class="toggle-sidebar-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar" id="sidebar" style="text-align: left;">
        <img src="<?php echo e(asset('Pup/puplogo.png')); ?>" style="max-width: 40%; height: auto; padding: 10px; margin: 20px auto;" alt="PUP Logo">
        <h3 style="margin-bottom: 20px; text-align: center;">SMIS</h3>
      
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
        <?php if(Auth::user()->role === 'Admin' || Auth::user()->role === 'SuperAdmin'): ?>
        <a href="<?php echo e(route('usersmanage.index')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
            <i class="fas fa-users" style="margin-right: 8px;"></i> <span>User Management</span>
        </a>
        <?php endif; ?>        <a href="<?php echo e(route('notifications.index')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
            <i class="fas fa-bell" style="margin-right: 8px;"></i> 
            <span>Notifications</span>
            <?php if(isset($notificationCounts) && $notificationCounts['total_unread'] > 0): ?>
                <span class="notification-badge"><?php echo e($notificationCounts['total_unread']); ?></span>
            <?php endif; ?>
        </a>
        <a class="dropdown-btn" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px; cursor: pointer; text-decoration: none;">
            <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
            <i class="fa fa-caret-down" style="margin-left: 8px;"></i>
        </a>        <div class="dropdown-container" style="margin: 10px auto; text-align: left; display: none;">
        <?php if(Auth::user()->role !== 'Admin' && Auth::user()->role !== 'SuperAdmin'): ?>
        <a href="<?php echo e(route('requests.create')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                <i class="fas fa-plus" style="margin-right: 8px;"></i> <span>Create New Request</span>
            </a>
        <?php endif; ?>
            <a href="<?php echo e(route('requests.pending')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                <i class="fas fa-clock" style="margin-right: 8px;"></i> <span>Pending</span>
            </a>
            <a href="<?php echo e(route('requests.approved')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> 
                <span>Approved</span>
            </a>
            <a href="<?php echo e(route('requests.released')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                <i class="fas fa-truck" style="margin-right: 8px;"></i> <span>Released</span>
            </a>
            <a href="<?php echo e(route('requests.disapproved')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                <i class="fas fa-times-circle" style="margin-right: 8px;"></i> 
                <span>Disapproved</span>
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

    <div class="main-content p-4">
        <!-- Add Welcome Header Section -->
        <div class="dashboard-header modern-header">
            <div class="header-content">
                <h2 class="welcome-text">
                    <?php if(Auth::user()->role == 'SuperAdmin'): ?>
                        <i class="fas fa-crown text-warning me-2"></i>Welcome SuperAdmin,
                        <span class="user-name"><?php echo e(Auth::user()->First_Name); ?> <?php echo e(Auth::user()->Middle_Initial); ?>. <?php echo e(Auth::user()->Last_Name); ?>!</span>
                    <?php else: ?>
                        <i class="fas fa-user-shield me-2"></i>Welcome <?php echo e(ucfirst(Auth::user()->role)); ?>,
                        <span class="user-name"><?php echo e(Auth::user()->First_Name); ?> <?php echo e(Auth::user()->Middle_Initial); ?>. <?php echo e(Auth::user()->Last_Name); ?>!</span>
                    <?php endif; ?>
                </h2>
                <p class="header-subtitle">Manage and view all released supply requests</p>
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
        
        <div class="header">
            <h2>Released Requests</h2>
            <div id="currentDateTime" class="text-center mb-3"></div>
        </div>
        
        <!-- Improved filter section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search requests...">
                            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-select" id="officeFilter">
                            <!-- Will be populated dynamically based on role -->
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-select" id="sortOrder">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Using container-fluid with proper overflow handling -->
        <div class="container-fluid mt-4">
            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php
                // Role-based filtering - Apply before any processing
                $userRole = strtolower(trim(Auth::user()->role));
                $userOffice = Auth::user()->Office;
                
                // For regular users (not Admin/SuperAdmin), filter by office FIRST
                if (!in_array($userRole, ['superadmin', 'admin'])) {
                    // Filter the paginated collection by office
                    $filteredRequests = collect($releasedRequests->items())->filter(function($request) use ($userOffice) {
                        return $request->user && $request->user->Office === $userOffice;
                    });
                } else {
                    // Admin/SuperAdmin see all requests
                    $filteredRequests = collect($releasedRequests->items());
                }

                // Normalize purposes and create batch groups
                $normalizedRequests = $filteredRequests->map(function($request) {
                    // Create a copy to avoid modifying the original
                    $request->normalizedPurpose = strtolower(trim($request->purpose ?? $request->Purpose ?? 'office use'));
                    
                    // Create a timestamp for grouping - rounded to the nearest 10 minutes
                    // This ensures requests created close together are grouped
                    $timestamp = $request->created_at ?? now();
                    $minutes = floor($timestamp->minute / 10) * 10; // Round to nearest 10 minutes
                    $batchTimestamp = $timestamp->format('Y-m-d_H_') . $minutes;
                    
                    // Create a compound key: purpose + timestamp
                    $request->batchKey = $request->normalizedPurpose . '_' . $batchTimestamp;
                    
                    // Store the original timestamp for display
                    $request->batchTime = $timestamp;
                    
                    return $request;
                });
                
                // Group by the compound key
                $groupedRequests = $normalizedRequests->groupBy('batchKey');
            ?>

            <!-- Added minimum width to ensure scrolling on zoom -->
            <div class="table-responsive" style="min-width: 100%;">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="align-middle sortable">Stock Number <i class="fas fa-sort ms-1"></i></th>
                            <th class="align-middle sortable">Requester <i class="fas fa-sort ms-1"></i></th>
                            <th class="align-middle sortable">Office <i class="fas fa-sort ms-1"></i></th>
                            <th class="align-middle sortable">Supply Name <i class="fas fa-sort ms-1"></i></th>
                            <th class="align-middle sortable">Req Qty <i class="fas fa-sort ms-1"></i></th>
                            <th class="align-middle sortable">Issue Qty <i class="fas fa-sort ms-1"></i></th>
                            <th class="align-middle sortable">Purpose <i class="fas fa-sort ms-1"></i></th>
                            <th class="align-middle">Status</th>
                            <th class="align-middle sortable">Last Updated <i class="fas fa-sort ms-1"></i></th>
                            <th class="align-middle">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="requestsTableBody">
                        <?php $__empty_1 = true; $__currentLoopData = $groupedRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batchKey => $requests): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $firstRequest = $requests->first();
                            $totalItems = $requests->count();
                            
                            // Get original purpose format from the first request
                            $displayPurpose = $firstRequest->purpose ?: $firstRequest->Purpose ?: 'OFFICE USE';
                            
                            // Count all additional items across all requests in this group
                            $additionalItemsCount = $requests->sum(function($req) {
                                return $req->items ? $req->items->count() : 0;
                            });
                            
                            // Generate a safe ID for modal targeting
                            $purposeId = 'purpose_' . md5($batchKey);
                        ?>
                        <tr class="group-header" data-office="<?php echo e($firstRequest->user->Office ?? 'N/A'); ?>">
                            <td><span class="fw-medium"><?php echo e($firstRequest->StockNumber); ?></span></td>
                            <td>
                                <?php if($firstRequest->user): ?>
                                    <?php echo e($firstRequest->user->Last_Name); ?>

                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($firstRequest->user->Office ?? 'N/A'); ?></td>
                            <td>
                                <?php echo e($firstRequest->supply_name); ?>

                                <?php if($totalItems > 1 || $additionalItemsCount > 0): ?>
                                    <span class="badge bg-info">+<?php echo e($totalItems - 1 + $additionalItemsCount); ?> more</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($firstRequest->supply_quantity); ?></td>
                            <td><?php echo e($firstRequest->issue_quantity ?: 'N/A'); ?></td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo e($displayPurpose); ?>">
                                    <?php echo e($displayPurpose); ?>

                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary py-2 px-3">Released</span>
                            </td>
                            <td><?php echo e($firstRequest->updated_at ? $firstRequest->updated_at->setTimezone('Asia/Manila')->format('M d, Y h:i:s A') : 'N/A'); ?></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary view-btn" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo e($purposeId); ?>" title="View Details">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Enhanced View Modal for group -->
                        <div class="modal fade" id="viewModal<?php echo e($purposeId); ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="fas fa-box-open me-2"></i>Released Request Group: <?php echo e($displayPurpose); ?>

                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Status banner -->
                                        <div class="alert alert-info mb-4" role="alert">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <div>
                                                    <strong>Status: Released</strong>
                                                    <div class="small mt-1">This request has been released to the requester.</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Total items summary -->
                                        <div class="card mb-3 border-0 shadow-sm">
                                            <div class="card-header bg-light d-flex align-items-center">
                                                <i class="fas fa-info-circle me-2 text-muted"></i>
                                                <h6 class="mb-0 fw-bold">Request Summary</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p class="mb-2"><strong>Total Requests:</strong> <?php echo e($requests->count()); ?></p>
                                                        <p class="mb-2"><strong>Total Items:</strong> <?php echo e($requests->count() + $additionalItemsCount); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="mb-2"><strong>Released Date:</strong> <?php echo e($firstRequest->updated_at ? $firstRequest->updated_at->setTimezone('Asia/Manila')->format('F d, Y g:i A') . ' PHT' : 'N/A'); ?></p>
                                                        <p class="mb-2"><strong>Latest Update:</strong> <?php echo e($requests->max('updated_at') ? \Carbon\Carbon::parse($requests->max('updated_at'))->setTimezone('Asia/Manila')->format('F d, Y g:i A') . ' PHT' : 'N/A'); ?></p>
                                                        <?php if($firstRequest->ris_file_path): ?>
                                                        <p class="mb-0"><strong>RIS File:</strong> Available for download</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="card mb-3">
                                                <div class="card-header <?php echo e($index === 0 ? 'bg-secondary text-white' : 'bg-light'); ?>">
                                                    <?php echo e($index === 0 ? 'Primary Request' : 'Additional Request #' . $index); ?> 
                                                    <span class="float-end"><?php echo e($request->updated_at ? $request->updated_at->setTimezone('Asia/Manila')->format('M d, Y g:i A') . ' PHT' : 'N/A'); ?></span>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <p><strong>Requester:</strong> <?php echo e(optional($request->user)->First_Name . ' ' . optional($request->user)->Last_Name); ?></p>
                                                            <p><strong>Office:</strong> <?php echo e(optional($request->user)->Office); ?></p>
                                                            <p><strong>Stock Number:</strong> <?php echo e($request->StockNumber); ?></p>
                                                            <p><strong>Supply Name:</strong> <?php echo e($request->supply_name); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Requested Quantity:</strong> <?php echo e($request->supply_quantity); ?></p>
                                                            <p><strong>Issue Quantity:</strong> <?php echo e($request->issue_quantity ?: 'N/A'); ?></p>
                                                            <p><strong>Received By:</strong> <?php echo e($request->received_by ?: 'N/A'); ?></p>
                                                            <p><strong>Position:</strong> <?php echo e($request->received_position ?: 'N/A'); ?></p>
                                                            <p><strong>Status:</strong> 
                                                                <span class="badge bg-secondary">Released</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if($request->items && $request->items->count() > 0): ?>
                                                        <h6 class="mt-4">Additional Items</h6>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Stock Number</th>
                                                                    <th>Item Description</th>
                                                                    <th>Unit</th>
                                                                    <th>Quantity</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $__currentLoopData = $request->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <tr>
                                                                    <td><?php echo e($item->StockNumber); ?></td>
                                                                    <td><?php echo e($item->supply_name); ?></td>
                                                                    <td><?php echo e($item->unit ?? 'N/A'); ?></td>
                                                                    <td><?php echo e($item->supply_quantity); ?></td>
                                                                </tr>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </tbody>
                                                        </table>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <div class="mt-3">
                                            <p><strong>Purpose:</strong> <?php echo e($displayPurpose); ?></p>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-1"></i> Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <img src="<?php echo e(asset('images/no-data.svg') ?? 'https://cdn-icons-png.flaticon.com/512/7486/7486754.png'); ?>" alt="No Data" style="width: 120px; opacity: 0.6;">
                                <h5 class="mt-3 text-muted">No released requests found</h5>
                                <p class="text-muted">There are no released requests at this time.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Improved pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing <?php echo e($releasedRequests->firstItem() ?? 0); ?> to <?php echo e($releasedRequests->lastItem() ?? 0); ?> of <?php echo e($releasedRequests->total()); ?> entries
                </div>
                <div>
                    <nav>
                        <ul class="pagination">
                            <?php if(isset($selectedOffice) && $selectedOffice): ?>
                                <?php echo e($releasedRequests->appends(['office' => $selectedOffice])->links('pagination::bootstrap-4')); ?>

                            <?php else: ?>
                                <?php echo e($releasedRequests->links('pagination::bootstrap-4')); ?>

                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #800020; color: white;">
                    <h5 class="modal-title" id="logoutConfirmModalLabel">
                        <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p style="font-size: 18px; color: #333;">Are you sure you want to logout from the system?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">CANCEL</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmLogoutBtn" onclick="confirmLogout()">
                        <span id="logoutBtnText">CONFIRM LOGOUT</span>
                        <span id="logoutBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateDateTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
                timeZone: 'Asia/Manila'
            };
            document.getElementById('currentDateTime').textContent = now.toLocaleString('en-US', options);
        }

        // Update time every second
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Document ready function
        document.addEventListener('DOMContentLoaded', function () {
            // Set sidebar state
            var sidebarState = localStorage.getItem('sidebarState');
            if (sidebarState === 'collapsed') {
                var sidebar = document.querySelector('.sidebar');
                sidebar.classList.add('collapsed');
                var mainContent = document.querySelector('.main-content');
                mainContent.classList.add('collapsed');
                var title = sidebar.querySelector('h3');
                if (title) title.style.display = 'none';
            }

            // Restore dropdown states
            document.querySelectorAll('.dropdown-btn').forEach(function (btn, index) {
                var dropdownContent = btn.nextElementSibling;
                var dropdownState = localStorage.getItem('dropdownState-' + index);

                if (dropdownState === 'open') {
                    btn.classList.add('active');
                    dropdownContent.classList.add('show');
                } else {
                    dropdownContent.classList.remove('show');
                }
            });
            
            // Populate office filter with role-based restrictions
            populateOfficeFilter();
            
            // Make table headers sortable
            document.querySelectorAll('th.sortable').forEach(headerCell => {
                headerCell.addEventListener('click', () => {
                    const tableElement = headerCell.closest('table');
                    const headerIndex = Array.prototype.indexOf.call(headerCell.parentElement.children, headerCell);
                    const currentIsAscending = headerCell.classList.contains('th-sort-asc');
                    
                    // Update header icons
                    document.querySelectorAll('th.sortable').forEach(th => {
                        th.classList.remove('th-sort-asc', 'th-sort-desc');
                        th.querySelector('i').className = 'fas fa-sort ms-1';
                    });
                    
                    headerCell.classList.toggle('th-sort-asc', !currentIsAscending);
                    headerCell.classList.toggle('th-sort-desc', currentIsAscending);
                    
                    // Update sort icon
                    if (!currentIsAscending) {
                        headerCell.querySelector('i').className = 'fas fa-sort-up ms-1';
                    } else {
                        headerCell.querySelector('i').className = 'fas fa-sort-down ms-1';
                    }
                    
                    // Sort the table
                    sortTableByColumn(tableElement, headerIndex, !currentIsAscending);
                });
            });
            
            // Table sorting function
            function sortTableByColumn(table, column, asc = true) {
                const dirModifier = asc ? 1 : -1;
                const tBody = table.tBodies[0];
                const rows = Array.from(tBody.querySelectorAll('tr'));
                
                // Sort each row
                const sortedRows = rows.sort((a, b) => {
                    const aColText = a.querySelector(`td:nth-child(${column + 1})`).textContent.trim();
                    const bColText = b.querySelector(`td:nth-child(${column + 1})`).textContent.trim();
                    
                    return aColText > bColText ? (1 * dirModifier) : (-1 * dirModifier);
                });
                
                // Remove all existing TRs from the table
                while (tBody.firstChild) {
                    tBody.removeChild(tBody.firstChild);
                }
                
                // Re-add the newly sorted rows
                tBody.append(...sortedRows);
            }
            
            // Search functionality with role-based restrictions
            const searchInput = document.getElementById('searchInput');
            const searchBtn = document.getElementById('searchBtn');
            const officeFilter = document.getElementById('officeFilter');
            const sortOrder = document.getElementById('sortOrder');
            const userRole = '<?php echo e(strtolower(trim(Auth::user()->role))); ?>';
            const userOffice = '<?php echo e(Auth::user()->Office); ?>';
            
            // Handle office filter change - reload page with office parameter for SuperAdmin/Admin
            officeFilter.addEventListener('change', function() {
                if (userRole === 'superadmin' || userRole === 'admin') {
                    const selectedOffice = this.value;
                    const url = new URL(window.location.href);
                    
                    if (selectedOffice) {
                        url.searchParams.set('office', selectedOffice);
                    } else {
                        url.searchParams.delete('office');
                    }
                    
                    window.location.href = url.toString();
                } else {
                    // For regular users, just apply client-side filtering
                    applyFilters();
                }
            });
            
            function applyFilters() {
                const searchTerm = searchInput.value.toLowerCase();
                const officeValue = officeFilter.value;
                const rows = document.querySelectorAll('#requestsTableBody tr.group-header');
                
                rows.forEach(row => {
                    let displayRow = true;
                    const office = row.getAttribute('data-office');
                    const text = row.textContent.toLowerCase();
                    
                    // Role-based office filtering
                    // Non-admin and non-superadmin users can only see their office requests
                    if (!(userRole === 'superadmin' || userRole === 'admin')) {
                        if (office !== userOffice) {
                            displayRow = false;
                        }
                    }
                    
                    // Filter by search term
                    if (displayRow && searchTerm) {
                        if (!text.includes(searchTerm)) {
                            displayRow = false;
                        }
                    }
                    
                    row.style.display = displayRow ? '' : 'none';
                });
            }
            
            searchInput.addEventListener('keyup', applyFilters);
            searchBtn.addEventListener('click', applyFilters);
            
            // Apply initial filtering for non-SuperAdmin users
            if (!(userRole === 'superadmin' || userRole === 'admin')) {
                applyFilters();
            }
            
            // Sort order functionality
            sortOrder.addEventListener('change', function() {
                const tableBody = document.getElementById('requestsTableBody');
                const rows = Array.from(tableBody.querySelectorAll('tr.group-header'));
                
                rows.sort((a, b) => {
                    const aDate = new Date(a.querySelector('td:nth-child(9)').textContent);
                    const bDate = new Date(b.querySelector('td:nth-child(9)').textContent);
                    
                    return this.value === 'newest' ? bDate - aDate : aDate - bDate;
                });
                
                // Clear the table
                while (tableBody.firstChild) {
                    tableBody.removeChild(tableBody.firstChild);
                }
                
                // Add sorted rows back
                rows.forEach(row => {
                    tableBody.appendChild(row);
                    
                    // Find and append the modal that goes with this row
                    const rowId = row.querySelector('button.view-btn').getAttribute('data-bs-target');
                    const modalId = rowId.substring(1); // Remove the #
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        document.body.appendChild(modal);
                    }
                });
            });
        });

        // Populate office filter function with role-based filtering
        function populateOfficeFilter() {
            const officeFilter = document.getElementById('officeFilter');
            const userRole = '<?php echo e(strtolower(trim(Auth::user()->role))); ?>';
            const userOffice = '<?php echo e(Auth::user()->Office); ?>';
            const selectedOffice = '<?php echo e($selectedOffice ?? ''); ?>';
            const allOffices = <?php echo json_encode($allOffices ?? [], 15, 512) ?>;
            
            // Clear existing options
            officeFilter.innerHTML = '';
            
            // Allow both SuperAdmin and Admin to access and choose from all offices
            if (userRole === 'superadmin' || userRole === 'admin') {
                // Always show "All Offices" option first
                const allOfficesOption = document.createElement('option');
                allOfficesOption.value = '';
                allOfficesOption.textContent = 'All Offices';
                // Select "All Offices" if no specific office is selected
                if (!selectedOffice) {
                    allOfficesOption.selected = true;
                }
                officeFilter.appendChild(allOfficesOption);
                
                // Add all available offices from the database (not just visible rows)
                allOffices.forEach(office => {
                    if (office) {
                        const option = document.createElement('option');
                        option.value = office;
                        option.textContent = office;
                        // Pre-select the filtered office if it matches
                        if (office === selectedOffice) {
                            option.selected = true;
                        }
                        officeFilter.appendChild(option);
                    }
                });
            } else {
                // Other users only see their own office (no "All Offices" option)
                if (userOffice && userOffice !== 'N/A') {
                    const option = document.createElement('option');
                    option.value = userOffice;
                    option.textContent = userOffice;
                    officeFilter.appendChild(option);
                    
                    // Automatically select user's office for non-SuperAdmin users
                    officeFilter.value = userOffice;
                }
            }
        }

        function toggleSidebar() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
            
            var mainContent = document.querySelector('.main-content');
            mainContent.classList.toggle('collapsed');
            
            localStorage.setItem('sidebarState', sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded');
        }        // JavaScript to handle dropdown functionality
        document.querySelectorAll('.dropdown-btn').forEach(function (btn, index) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                this.classList.toggle('active');
                var dropdownContent = this.nextElementSibling;

                if (dropdownContent.classList.contains('show')) {
                    dropdownContent.classList.remove('show');
                    localStorage.setItem('dropdownState-' + index, 'closed');
                } else {
                    dropdownContent.classList.add('show');
                    localStorage.setItem('dropdownState-' + index, 'open');
                }
            });
        });

        // Logout confirmation functions
        function showLogoutModal() {
            const logoutModal = new bootstrap.Modal(document.getElementById('logoutConfirmModal'));
            logoutModal.show();
        }

        function confirmLogout() {
            // Show loading spinner
            document.getElementById('logoutBtnText').classList.add('d-none');
            document.getElementById('logoutBtnSpinner').classList.remove('d-none');
            
            // Disable the button to prevent multiple clicks
            document.getElementById('confirmLogoutBtn').disabled = true;
              // Submit the form after a small delay to show the animation
            setTimeout(function() {
                document.getElementById('logout-form').submit();
            }, 800);
        }

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

        // Preserve sidebar state during pagination
        document.querySelectorAll('.pagination a').forEach(function(link) {
            link.addEventListener('click', function(e) {
                // Store current sidebar state before page navigation
                localStorage.setItem('sidebarState', 
                    document.querySelector('.sidebar').classList.contains('collapsed') 
                        ? 'collapsed' 
                        : 'expanded'
                );
            });
        });
    </script>
    <script src="<?php echo e(asset('js/notification-badges.js')); ?>" defer></script>
</body>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u741082352/domains/smis.ojt-ims-bsit.net/public_html/resources/views/requests/released.blade.php ENDPATH**/ ?>