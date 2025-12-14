

<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/Disapproved.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/notification-badges.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/collapsed-sidebar-badges.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dark-mode.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dark-mode-switch.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/user-settings-dropdown.css')); ?>">
<script src="<?php echo e(asset('js/enhanced-notifications.js')); ?>" defer></script>

<style>
/* Dropdown styles */
.dropdown-btn {
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
}

.dropdown-btn.active {
    background-color: rgba(255, 255, 255, 0.1);
}

.dropdown-btn .fa-caret-down {
    transition: transform 0.3s ease;
}

.dropdown-btn.active .fa-caret-down {
    transform: rotate(180deg)
}

.dropdown-container {
    display: none;
    padding-left: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
}
</style>

<body>
    <button class="toggle-sidebar-btn">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar" id="sidebar" style="text-align: left;">
        <div class="sidebar-content">
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
            <a href="#" class="dropdown-btn" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
                <i class="fa fa-caret-down" style="margin-left: 8px;"></i>
            </a>
            <div class="dropdown-container" style="margin: 10px auto; text-align: left;">
                <?php if(Auth::user()->role === 'User'): ?>
                    <a href="<?php echo e(route('requests.create')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                        <i class="fas fa-plus" style="margin-right: 8px;"></i> <span>Create New Request</span>
                    </a>
                <?php endif; ?>          <a href="<?php echo e(route('requests.pending')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                    <i class="fas fa-clock" style="margin-right: 8px;"></i> <span>Pending</span>
                </a>            <a href="<?php echo e(route('requests.approved')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                    <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> 
                    <span>Approved</span>
                </a>
                <a href="<?php echo e(route('requests.released')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
                    <i class="fas fa-truck" style="margin-right: 8px;"></i> <span>Released</span>
                </a>            <a href="<?php echo e(route('requests.disapproved')); ?>" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 10px;">
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
    </div>

    <div class="main-content p-4">
        <!-- Replace welcome banner with the format from pending.blade.php -->
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
                <p class="header-subtitle">Manage and view all Disapproved/Expired and Cancelled requests.</p>
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
            <h2>Disapproved/Expired/Cancelled Requests</h2>
            <div id="currentDateTime" class="text-center mb-3"></div>
        </div>
        
        <!-- Improved filter section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="Disapproved">Disapproved</option>
                            <option value="Expired">Expired</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <select class="form-select" id="officeFilter">
                            <!-- Will be populated dynamically based on role -->
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <select class="form-select" id="sortOrder">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Improved table section -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Date Denied/Updated <i class="fas fa-sort ms-1"></i></th>
                        <th>Purpose <i class="fas fa-sort ms-1"></i></th>
                        <th>Status</th>
                        <th>Requestor/Office <i class="fas fa-sort ms-1"></i></th>
                        <th>Date Requested <i class="fas fa-sort ms-1"></i></th>
                        <th>Updated By</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Role-based filtering
                        $userRole = strtolower(trim(Auth::user()->role));
                        $userOffice = Auth::user()->Office;
                        
                        // Filter requests based on user role (SuperAdmin and Admin see all)
                        if (in_array($userRole, ['superadmin', 'admin'])) {
                            $filteredRequests = $disapprovedRequests;
                        } else {
                            $filteredRequests = $disapprovedRequests->filter(function($request) use ($userOffice) {
                                return $request->user && $request->user->Office === $userOffice;
                            });
                        }

                        // Use the same batch grouping logic as pending page
                        $normalizedRequests = $filteredRequests->map(function($request) {
                            // Create a copy to avoid modifying the original
                            $request->normalizedPurpose = strtolower(trim($request->Purpose));
                            
                            // Create a timestamp for grouping - rounded to the nearest 10 minutes
                            // Use created_at for batching (when originally requested, not when denied)
                            $timestamp = $request->created_at ?? now();
                            $minutes = floor($timestamp->minute / 10) * 10; // Round to nearest 10 minutes
                            $batchTimestamp = $timestamp->format('Y-m-d_H_') . $minutes;
                            
                            // Create a compound key: purpose + timestamp
                            $request->batchKey = $request->normalizedPurpose . '_' . $batchTimestamp;
                            
                            // Store the original timestamp for display
                            $request->batchTime = $timestamp;
                            
                            return $request;
                        });
                        
                        // Group by the compound key (same as pending page)
                        $groupedRequests = $normalizedRequests->groupBy('batchKey');
                    ?>
                    
                    <?php $__currentLoopData = $groupedRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batchKey => $requests): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $firstRequest = $requests->first();
                            $totalItems = $requests->count();
                            
                            // Get original purpose format from the first request
                            $displayPurpose = $firstRequest->Purpose;
                            
                            // Count all additional items across all requests in this group
                            $additionalItemsCount = $requests->sum(function($req) {
                                return $req->items ? $req->items->count() : 0;
                            });
                            
                            // Generate a safe ID for modal targeting
                            $purposeId = 'purpose_' . md5($batchKey);
                            
                            // Use updated_at for when it was denied
                            $deniedDate = $firstRequest->updated_at ?? $firstRequest->created_at;
                            $displayDate = $deniedDate->setTimezone(new DateTimeZone('Asia/Manila'))->format('M d, Y h:i:s A');
                        ?>
                    <tr class="request-row group-header" data-office="<?php echo e($firstRequest->user->Office ?? 'N/A'); ?>">
                        <td>
                            <span class="fw-medium">
                                <?php echo e($displayDate); ?>

                            </span>
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 250px;" title="<?php echo e($displayPurpose); ?>">
                                <?php echo e($displayPurpose); ?>

                            </span>
                            <?php if($totalItems > 1 || $additionalItemsCount > 0): ?>
                                <br><span class="badge bg-info">+<?php echo e($totalItems - 1 + $additionalItemsCount); ?> more items</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-danger py-2 px-3">
                                <?php echo e($firstRequest->status); ?>

                            </span>
                        </td>
                        <td>
                            <?php
                                $requestor = $firstRequest->user->First_Name ?? 'Unknown';
                                $office = $firstRequest->user->Office ?? 'N/A';
                            ?>
                            <div>
                                <span class="d-block"><i class="fas fa-user me-1"></i> <?php echo e($requestor); ?></span>
                                <small class="text-muted"><i class="fas fa-building me-1"></i> <?php echo e($office); ?></small>
                            </div>
                        </td>
                        <td><?php echo e($firstRequest->created_at ? $firstRequest->created_at->setTimezone('Asia/Manila')->format('M d, Y h:i:s A') : 'N/A'); ?></td>
                        <td><?php echo e($firstRequest->updated_by ?? 'None'); ?></td>
                        <td class="text-center">
                            <button class="btn btn-primary view-group-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#viewGroupRequestModal"
                                data-batchkey="<?php echo e($batchKey); ?>"
                                data-created="<?php echo e($firstRequest->created_at ? $firstRequest->created_at->setTimezone('Asia/Manila')->format('c') : ''); ?>"
                                data-purpose="<?php echo e($displayPurpose); ?>"
                                data-requests="<?php echo e(json_encode($requests->load('user')->toArray())); ?>"
                                data-count="<?php echo e($totalItems); ?>"
                                data-requestor="<?php echo e($requestor); ?>"
                                data-office="<?php echo e($office); ?>">
                                <i class="fas fa-eye me-1"></i> View Request
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

            <!-- No requests message -->
            <?php if(count($disapprovedRequests) == 0): ?>
            <div class="text-center py-5">
                <img src="<?php echo e(asset('images/no-data.svg')); ?>" alt="No Data" style="width: 120px; opacity: 0.6;">
                <h5 class="mt-3 text-muted">No disapproved requests found</h5>
                <p class="text-muted">There are no disapproved, expired, or cancelled requests at this time.</p>
            </div>
            <?php endif; ?>

            <!-- Improved pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing <?php echo e($groupedRequests->count()); ?> purpose groups
                </div>
                <div>
                    <nav>
                        <ul class="pagination">
                            <?php if(isset($selectedOffice) && $selectedOffice): ?>
                                <?php echo e($disapprovedRequests->appends(['office' => $selectedOffice])->links('pagination::bootstrap-4')); ?>

                            <?php else: ?>
                                <?php echo e($disapprovedRequests->links('pagination::bootstrap-4')); ?>

                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Group View Request Modal -->
    <div class="modal fade" id="viewGroupRequestModal" tabindex="-1" aria-labelledby="viewGroupRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="viewGroupRequestModalLabel">
                        <i class="fas fa-file-alt me-2"></i> Request Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Request info section -->
                    <div class="alert alert-info mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i>
                            <div>
                                <strong>Date Denied/Updated:</strong>
                                <div id="modalGroupDateTime" class="mt-1"></div>
                                <strong class="mt-2">Date Requested:</strong>
                                <div id="modalGroupCreatedTime" class="mt-1"></div>
                                <strong class="mt-2">Purpose:</strong>
                                <div id="modalGroupPurpose" class="mt-1"></div>
                                <div class="mt-2">
                                    <strong>Requestor/Office:</strong>
                                    <div id="modalGroupRequestor" class="mt-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items table -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-list me-2"></i> Request Items (<span id="itemCount">0</span>)
                                </h6>
                                <input type="text" id="groupItemSearch" class="form-control form-control-sm" style="width: 200px;" placeholder="Search items...">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Stock #</th>
                                            <th>Supply Name</th>
                                            <th>Quantity</th>
                                            <th>Status</th>
                                            <th>Date Updated</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody id="groupItemsTableBody">
                                        <!-- Items will be populated here via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

    <!-- Enhanced View Request Modal -->
    <div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" id="modalHeaderStatus">
                    <h5 class="modal-title" id="viewRequestModalLabel">
                        <i class="fas fa-file-alt me-2"></i> Request Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Status banner -->
                    <div class="alert mb-4" id="statusBanner" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i>
                            <div>
                                <strong>Status: <span id="modalStatusBanner"></span></strong>
                                <div id="statusMessage" class="small mt-1"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light d-flex align-items-center">
                            <i class="fas fa-info-circle me-2 text-muted"></i>
                            <h6 class="mb-0 fw-bold">Request Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted small text-uppercase">Stock Number</h6>
                                    <p id="modalStockNumber" class="mb-0 fs-5"></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted small text-uppercase">Supply Name</h6>
                                    <p id="modalSupplyName" class="mb-0 fs-5"></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted small text-uppercase">Quantity</h6>
                                    <p id="modalQuantity" class="mb-0 fs-5"></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted small text-uppercase">Status</h6>
                                    <p id="modalStatus" class="badge bg-danger mb-0"></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <h6 class="fw-bold text-muted small text-uppercase">Purpose</h6>
                                    <p id="modalPurpose" class="mb-0"></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="fw-bold text-muted small text-uppercase">Remarks/Reason for Denial</h6>
                                    <p id="modalRemarks" class="mb-0 p-2 bg-light rounded"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header bg-light d-flex align-items-center">
                            <i class="fas fa-truck-loading me-2 text-muted"></i>
                            <h6 class="mb-0 fw-bold">Tracking Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted small text-uppercase">Requested By</h6>
                                    <p id="modalRequestor" class="mb-0"></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted small text-uppercase">Date Requested</h6>
                                    <p id="modalCreated" class="mb-0"></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted small text-uppercase">Updated By</h6>
                                    <p id="modalUpdatedBy" class="mb-0"></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted small text-uppercase">Date Updated</h6>
                                    <p id="modalUpdatedAt" class="mb-0"></p>
                                </div>
                            </div>
                        </div>
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
            
            // View button functionality
            document.querySelectorAll('.view-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const stockNumber = this.getAttribute('data-stock');
                    const supplyName = this.getAttribute('data-name');
                    const quantity = this.getAttribute('data-quantity');
                    const status = this.getAttribute('data-status');
                    const purpose = this.getAttribute('data-purpose');
                    const remarks = this.getAttribute('data-remarks');
                    const requestor = this.getAttribute('data-requestor');
                    const created = this.getAttribute('data-created');
                    const updatedBy = this.getAttribute('data-updatedby');
                    const updatedAt = this.getAttribute('data-date');
                    
                    // Populate modal with data
                    document.getElementById('modalStockNumber').textContent = stockNumber;
                    document.getElementById('modalSupplyName').textContent = supplyName;
                    document.getElementById('modalQuantity').textContent = quantity;
                    document.getElementById('modalStatus').textContent = status;
                    document.getElementById('modalStatusBanner').textContent = status;
                    document.getElementById('modalPurpose').textContent = purpose;
                    document.getElementById('modalRemarks').textContent = remarks || 'No remarks provided';
                    document.getElementById('modalRequestor').textContent = requestor;
                    document.getElementById('modalCreated').textContent = created;
                    document.getElementById('modalUpdatedBy').textContent = updatedBy;
                    document.getElementById('modalUpdatedAt').textContent = updatedAt;
                    
                    // Style header and banner based on status
                    const modalHeader = document.getElementById('modalHeaderStatus');
                    const statusBanner = document.getElementById('statusBanner');
                    const statusMessage = document.getElementById('statusMessage');
                    
                    // Reset classes
                    modalHeader.className = 'modal-header';
                    statusBanner.className = 'alert mb-4';
                    
                    // Apply status-specific styling
                    if (status.toLowerCase() === 'disapproved') {
                        modalHeader.classList.add('bg-danger', 'text-white');
                        statusBanner.classList.add('alert-danger');
                        statusMessage.textContent = 'This request was disapproved. See remarks for details.';
                    } else if (status.toLowerCase() === 'expired') {
                        modalHeader.classList.add('bg-warning', 'text-dark');
                        statusBanner.classList.add('alert-warning');
                        statusMessage.textContent = 'This request has expired and is no longer valid.';
                    } else if (status.toLowerCase() === 'cancelled') {
                        modalHeader.classList.add('bg-secondary', 'text-white');
                        statusBanner.classList.add('alert-secondary');
                        statusMessage.textContent = 'This request was cancelled by the requestor or administrator.';
                    }
                });
            });
            
            // Add group view button functionality
            document.querySelectorAll('.view-group-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const purpose = this.getAttribute('data-purpose');
                    const requests = JSON.parse(this.getAttribute('data-requests'));
                    const count = this.getAttribute('data-count');
                    const requestor = this.getAttribute('data-requestor');
                    const office = this.getAttribute('data-office');
                    const dateTime = this.getAttribute('data-datetime');
                    const createdTime = this.getAttribute('data-created');
                    
                    // Format the dates for display with exact timestamp
                    const dateOptions = {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true,
                        timeZone: 'Asia/Manila'
                    };
                    
                    // Parse ISO 8601 dates to ensure timezone is correctly interpreted
                    document.getElementById('modalGroupDateTime').textContent = 
                        new Date(dateTime).toLocaleString('en-PH', dateOptions);
                    document.getElementById('modalGroupCreatedTime').textContent = 
                        createdTime ? new Date(createdTime).toLocaleString('en-PH', dateOptions) : 'N/A';
                    document.getElementById('modalGroupPurpose').textContent = purpose;
                    document.getElementById('itemCount').textContent = count;
                    
                    // Set requestor and office information directly from data attributes
                    document.getElementById('modalGroupRequestor').innerHTML = `
                        <span><i class="fas fa-user me-1"></i> ${requestor}</span>
                        <small class="text-muted d-block"><i class="fas fa-building me-1"></i> ${office}</small>
                    `;
                    
                    // Populate items table
                    const tableBody = document.getElementById('groupItemsTableBody');
                    tableBody.innerHTML = ''; // Clear existing content
                    
                    requests.forEach(request => {
                        const row = document.createElement('tr');
                        
                        // Format date
                        let updatedDate = 'N/A';
                        if (request.updated_at) {
                            const date = new Date(request.updated_at);
                            updatedDate = date.toLocaleString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: true
                            });
                        }
                        
                        // Determine badge class based on status
                        let badgeClass = 'bg-secondary';
                        if (request.status === 'Disapproved') badgeClass = 'bg-danger';
                        if (request.status === 'Expired') badgeClass = 'bg-warning text-dark';
                        if (request.status === 'Cancelled') badgeClass = 'bg-secondary';
                        
                        row.innerHTML = `
                            <td>${request.StockNumber}</td>
                            <td>${request.supply_name}</td>
                            <td>${request.supply_quantity}</td>
                            <td><span class="badge ${badgeClass}">${request.status}</span></td>
                            <td>${updatedDate}</td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 150px;" title="${request.remarks || 'No remarks'}">
                                    ${request.remarks || 'No remarks'}
                                </span>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                    
                    // Add search functionality for the group items table
                    document.getElementById('groupItemSearch').addEventListener('keyup', function() {
                        const searchText = this.value.toLowerCase();
                        document.querySelectorAll('#groupItemsTableBody tr').forEach(row => {
                            const text = row.textContent.toLowerCase();
                            row.style.display = text.includes(searchText) ? '' : 'none';
                        });
                    });
                });
            });
            
            // Add table sorting functionality
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
            
            // Bulk view functionality
            const viewBulkModal = document.getElementById('viewBulkRequestModal');
            if (viewBulkModal) {
                viewBulkModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    // Extract data from button attributes
                    const batchId = button.getAttribute('data-batch');
                    const itemCount = button.getAttribute('data-count');
                    const purpose = button.getAttribute('data-purpose');
                    const status = button.getAttribute('data-status');
                    const updatedAt = button.getAttribute('data-date');
                    const updatedBy = button.getAttribute('data-updatedby');
                    const remarks = button.getAttribute('data-remarks');
                    const requestor = button.getAttribute('data-requestor');
                    
                    // Populate modal with data
                    document.getElementById('modalBatchId').textContent = batchId;
                    document.getElementById('modalBulkStatus').textContent = status;
                    document.getElementById('modalItemCount').textContent = itemCount;
                    document.getElementById('modalBulkPurpose').textContent = purpose;
                    document.getElementById('modalBulkRemarks').textContent = remarks;
                    document.getElementById('modalBulkRequestor').textContent = requestor;
                    document.getElementById('modalBulkUpdatedAt').textContent = updatedAt;
                    document.getElementById('modalBulkUpdatedBy').textContent = updatedBy;
                    
                    // Fetch and populate the items table
                    const tableBody = document.getElementById('bulkItemsTableBody');
                    tableBody.innerHTML = ''; // Clear existing content
                    
                    // Use AJAX to fetch items belonging to this batch
                    fetch(`/requests/bulk-items/${batchId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(item => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${item.StockNumber}</td>
                                    <td>${item.supply_name}</td>
                                    <td>${item.supply_quantity}</td>
                                `;
                                tableBody.appendChild(row);
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching bulk items:', error);
                            // Fallback to display a message if AJAX fails
                            tableBody.innerHTML = `<tr><td colspan="3" class="text-center">Error loading items. Please try again.</td></tr>`;
                        });
                });
            }
            
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
            
            // Enhanced sidebar toggle functionality
            // Expose the toggle function globally to avoid onclick scope issues and ensure other scripts can call it
            window.toggleSidebar = function () {
                var sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('collapsed');
                
                // Animate main content
                var mainContent = document.querySelector('.main-content');
                if (sidebar.classList.contains('collapsed')) {
                    mainContent.classList.add('collapsed');
                    localStorage.setItem('sidebarState', 'collapsed');
                } else {
                    mainContent.classList.remove('collapsed');
                    localStorage.setItem('sidebarState', 'expanded');
                }
            };

            // Toggle button event listener (explicitly reference the global function)
            document.querySelector('.toggle-sidebar-btn').addEventListener('click', window.toggleSidebar);

            // Fixed dropdown functionality
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
            
            // Prevent closing dropdown when clicking inside it
            document.querySelectorAll('.dropdown-container a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    // Don't close dropdown when clicking submenu items
                    e.stopPropagation();
                });
            });
            
            // Mark current page as active and open its dropdown
            document.querySelectorAll('.sidebar a').forEach(function(link) {
                if (window.location.href.includes(link.getAttribute('href'))) {
                    link.classList.add('active');
                    
                    // If this is a submenu item, open its parent dropdown
                    const container = link.closest('.dropdown-container');
                    if (container) {
                        container.classList.add('show');
                        container.style.display = 'block';
                        const dropdownBtn = container.previousElementSibling;
                        if (dropdownBtn && dropdownBtn.classList.contains('dropdown-btn')) {
                            dropdownBtn.classList.add('active');
                        }
                    }
                }
            });
            
            // Initialize dropdown state for Orders menu since we're on a submenu page
            (function initializeOrdersDropdown() {
                const currentPath = window.location.pathname;
                if (currentPath.includes('/requests/')) {
                    const ordersDropdown = document.querySelector('.dropdown-btn');
                    const dropdownContent = ordersDropdown.nextElementSibling;
                    
                    // Open the dropdown
                    ordersDropdown.classList.add('active');
                    dropdownContent.classList.add('show');
                    dropdownContent.style.display = 'block';
                    
                    // Mark current submenu as active
                    dropdownContent.querySelectorAll('a').forEach(function(link) {
                        if (currentPath.includes(link.getAttribute('href'))) {
                            link.classList.add('active');
                        }
                    });
                }
            })();
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

        document.addEventListener('DOMContentLoaded', function () {
            // Populate office filter with role-based restrictions
            populateOfficeFilter();
            
            // Initialize table filtering and sorting with role-based restrictions
            const statusFilter = document.getElementById('statusFilter');
            const officeFilter = document.getElementById('officeFilter');
            const sortOrder = document.getElementById('sortOrder');
            const tableBody = document.querySelector('.table tbody');
            const rows = Array.from(tableBody.querySelectorAll('tr.request-row'));
            const userRole = '<?php echo e(strtolower(trim(Auth::user()->role))); ?>';
            const userOffice = '<?php echo e(Auth::user()->Office); ?>';
            
            // Store original table data for reset
            const originalRows = [...rows];
            
            // Function to apply all filters and sorting
            function applyFilters() {
                // Get filter values
                const statusValue = statusFilter.value.toLowerCase();
                const officeValue = officeFilter.value;
                const sortValue = sortOrder.value;
                
                // Handle office filter change - reload page for SuperAdmin/Admin
                if ((userRole === 'superadmin' || userRole === 'admin') && officeFilter === document.activeElement) {
                    const url = new URL(window.location.href);
                    
                    if (officeValue) {
                        url.searchParams.set('office', officeValue);
                    } else {
                        url.searchParams.delete('office');
                    }
                    
                    window.location.href = url.toString();
                    return; // Exit early as we're reloading
                }
                
                // Clear the table
                while (tableBody.firstChild) {
                    tableBody.removeChild(tableBody.firstChild);
                }
                    
                // Filter and sort rows
                let filteredRows = originalRows.filter(row => {
                    let displayRow = true;
                    const rowOffice = row.getAttribute('data-office');
                    
                    // Role-based office filtering
                    if (!(userRole === 'superadmin' || userRole === 'admin')) {
                        if (rowOffice !== userOffice) {
                            displayRow = false;
                        }
                    }
                    
                    // Apply status filter
                    if (displayRow && statusValue) {
                        const rowStatus = row.querySelector('td:nth-child(3) .badge').textContent.toLowerCase();
                        if (!rowStatus.includes(statusValue)) {
                            displayRow = false;
                        }
                    }
                    
                    return displayRow;
                });
                
                // Sort rows by date
                filteredRows.sort((a, b) => {
                    const dateA = new Date(a.querySelector('td:first-child').textContent.trim());
                    const dateB = new Date(b.querySelector('td:first-child').textContent.trim());
                    
                    return sortValue === 'newest' ? dateB - dateA : dateA - dateB;
                });
                
                // Add filtered and sorted rows back to table
                filteredRows.forEach(row => {
                    tableBody.appendChild(row);
                });
                
                // Show message if no results
                if (filteredRows.length === 0) {
                    const noResultsRow = document.createElement('tr');
                    noResultsRow.innerHTML = `<td colspan="7" class="text-center py-4">No matching requests found</td>`;
                    tableBody.appendChild(noResultsRow);
                }
                
                // Update count in UI
                const countElement = document.querySelector('.text-muted.small');
                if (countElement) {
                    countElement.textContent = `Showing ${filteredRows.length} request${filteredRows.length !== 1 ? 's' : ''}`;
                }
            }
            
            // Add event listeners to filters
            statusFilter.addEventListener('change', applyFilters);
            officeFilter.addEventListener('change', applyFilters);
            sortOrder.addEventListener('change', applyFilters);
            
            // Apply initial filtering for non-SuperAdmin users
            if (userRole !== 'superadmin' && userRole !== 'admin') {
                applyFilters();
            } else {
                // Initial sort on page load - default to newest first
                applyFilters();
            }
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
                if (!selectedOffice) {
                    allOfficesOption.selected = true;
                }
                officeFilter.appendChild(allOfficesOption);
                
                // Add all available offices from the database
                allOffices.forEach(office => {
                    if (office) {
                        const option = document.createElement('option');
                        option.value = office;
                        option.textContent = office;
                        if (office === selectedOffice) {
                            option.selected = true;
                        }
                        officeFilter.appendChild(option);
                    }
                });
            } else {
                // Other users only see their own office
                if (userOffice && userOffice !== 'N/A') {
                    const option = document.createElement('option');
                    option.value = userOffice;
                    option.textContent = userOffice;
                    officeFilter.appendChild(option);
                    officeFilter.value = userOffice;
                }
            }
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
    </script>
    <script src="<?php echo e(asset('js/notification-badges.js')); ?>" defer></script>
    </body>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u741082352/domains/smis.ojt-ims-bsit.net/public_html/resources/views/requests/disapproved.blade.php ENDPATH**/ ?>