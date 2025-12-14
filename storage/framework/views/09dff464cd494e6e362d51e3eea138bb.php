

<?php $__env->startSection('content'); ?>

<link rel="stylesheet" href="<?php echo e(asset('css/approved.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dark-mode.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dark-mode-switch.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/user-settings-dropdown.css')); ?>">
<style>
    .modal-lg {
        max-width: 900px;
    }
</style>
<body>
    <button class="toggle-sidebar-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <img src="<?php echo e(asset('Pup/puplogo.png')); ?>" style="width: 60px; height: 60px; padding: 0; margin: 20px auto;" alt="PUP Logo">
            <h3 style="margin-bottom: 20px;">SMIS</h3>
            <a href="<?php echo e(route(
                strtolower(trim(Auth::user()->role)) === 'superadmin'
                    ? 'Admin.admin'
                    : (strtolower(trim(Auth::user()->role)) === 'admin'
                        ? 'Admin.admin'
                        : 'dashboard.use')
            )); ?>" style="margin-bottom: 10px;">
                <i class="fas fa-home" style="margin-right: 8px;"></i>
                <span>Home</span>
            </a>
              <?php if(Auth::user()->role === 'Admin' || Auth::user()->role === 'SuperAdmin'): ?>
            <a href="<?php echo e(route('usersmanage.index')); ?>" style="margin-bottom: 10px;">
                <i class="fas fa-users" style="margin-right: 8px;"></i> <span>User Management</span>
            </a>
            <?php endif; ?>
            <a href="<?php echo e(route('notifications.index')); ?>" style="margin-bottom: 10px;">
                <i class="fas fa-bell" style="margin-right: 8px;"></i> 
                <span>Notifications</span>
                <?php if(isset($notificationCounts) && $notificationCounts['total_unread'] > 0): ?>
                    <span class="notification-badge"><?php echo e($notificationCounts['total_unread']); ?></span>
                <?php endif; ?>
            </a>
            <a href="#" class="dropdown-btn" style="margin-bottom: 10px;">
                <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
                <i class="fa fa-caret-down" style="margin-left: auto;"></i>
            </a>
            <div class="dropdown-container" style="margin: 10px auto;">
                   <?php if(Auth::user()->role === 'User'): ?>
                    <a href="<?php echo e(route('requests.create')); ?>" style="margin-bottom: 10px;">
                        <i class="fas fa-plus" style="margin-right: 8px;"></i> <span>Create New Request</span>
                    </a>
                <?php endif; ?>
                <a href="<?php echo e(route('requests.pending')); ?>" style="margin-bottom: 10px;">
                    <i class="fas fa-clock" style="margin-right: 8px;"></i> <span>Pending</span>
                </a>
                <a href="<?php echo e(route('requests.approved')); ?>" style="margin-bottom: 10px;">
                    <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> <span>Approved</span>
                </a>
                <a href="<?php echo e(route('requests.released')); ?>" style="margin-bottom: 10px;">
                    <i class="fas fa-truck" style="margin-right: 8px;"></i> <span>Released</span>
                </a>
                <a href="<?php echo e(route('requests.disapproved')); ?>" style="margin-bottom: 10px;">
                    <i class="fas fa-times-circle" style="margin-right: 8px;"></i> <span>Disapproved</span>
                </a>
            </div>
            <a href="<?php echo e(route('reports')); ?>" style="margin-bottom: 10px;">
                <i class="fas fa-chart-line" style="margin-right: 8px;"></i> <span>Reports</span>
            </a>
            <a href="<?php echo e(route('help.support')); ?>" style="margin-bottom: 10px;">
                <i class="fas fa-question-circle" style="margin-right: 8px;"></i> <span>Help & Support</span>
            </a>

            <!-- NEW: Rate / Questionnaire Button (replace href with your Google Form link) -->
            <a id="rateSystemBtn" href="https://forms.gle/12SD5ZjPTXbsgqzC7" target="_blank" rel="noopener" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px; background: rgba(255,255,255,0.08); border-radius:5px;">
                <i class="fas fa-star-half-alt" style="margin-right: 8px;"></i> <span>Rate Our System</span>
            </a>
        </div>
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
                <p class="header-subtitle">Manage and view all approved requests.</p>
            </div>
        </div>
        
        <!-- User Settings Dropdown - Fixed Position with Manual Implementation -->
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
                
                <!-- Theme Toggle Script -->
                <script src="<?php echo e(asset('js/theme-toggle.js')); ?>"></script>
            </div>
        </div>
        
        <!-- Logout Form (hidden) -->
        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
            <?php echo csrf_field(); ?>
        </form>
        
        <!-- Manual dropdown implementation -->
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
            <h2>Approved Requests</h2>
            <div id="currentDateTime" class="text-center mb-3"></div>
        </div>
        <div class="container-fluid p-0">
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show mx-4">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Search and Filter Section -->
            <div class="search-filter mb-4">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search requests...">
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <select id="officeFilter" class="form-select">
                            <option value="">All Offices</option>
                            <!-- Will be populated dynamically -->
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button id="refreshBtn" class="btn btn-primary w-100">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <?php
                // Role-based filtering
                $userRole = strtolower(trim(Auth::user()->role));
                $userOffice = Auth::user()->Office;
                
                // Filter requests based on user role
                if (in_array($userRole, ['superadmin', 'admin'])) {
                    // SuperAdmin sees all requests
                    $filteredRequests = $approvedRequests;
                } else {
                    // Other users only see requests from their office
                    $filteredRequests = $approvedRequests->filter(function($request) use ($userOffice) {
                        return $request->user && $request->user->Office === $userOffice;
                    });
                }

                // Normalize purposes and create batch groups
                $normalizedRequests = $filteredRequests->map(function($request) {
                    // Create a copy to avoid modifying the original
                    $request->normalizedPurpose = strtolower(trim($request->Purpose ?? $request->purpose ?? 'office use'));
                    
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

            <div class="table-responsive">
                <table class="table table-hover" id="requestsTable">
                    <thead>
                        <tr>
                            <th width="10%">Stock #</th>
                            <th width="10%">Requester</th>
                            <th width="10%">Office</th>
                            <th width="20%">Supply Name</th>
                            <th width="5%">Qty</th>
                            <th width="5%">Issue Qty</th>
                            <th width="15%">Purpose</th>
                            <th width="10%">Status</th>
                            <th width="10%">Date/Time</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $groupedRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batchKey => $requests): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $firstRequest = $requests->first();
                                $totalItems = $requests->count();
                                
                                // Get original purpose format from the first request
                                $displayPurpose = $firstRequest->purpose ?? $firstRequest->Purpose ?? 'OFFICE USE';
                                
                                // Count all additional items across all requests in this group
                                $additionalItemsCount = $requests->sum(function($req) {
                                    return $req->items ? $req->items->count() : 0;
                                });
                                
                                // Generate a safe ID for modal targeting
                                $purposeId = 'purpose_' . md5($batchKey);
                            ?>
                            <tr class="group-header" data-office="<?php echo e($firstRequest->user->Office ?? 'N/A'); ?>">
                                <td><?php echo e($firstRequest->StockNumber); ?></td>
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
                                <td><?php echo e($firstRequest->issue_quantity ?? 'N/A'); ?></td>
                                <td><?php echo e(Str::limit($displayPurpose, 30)); ?></td>
                                <td>
                                    <?php
                                        // Calculate days since approval
                                        $daysSinceApproval = $firstRequest->updated_at ? 
                                            $firstRequest->updated_at->diffInDays(now()) : 0;
                                        $isExpiringSoon = $daysSinceApproval >= 4;
                                    ?>
                                    
                                    <?php if($firstRequest->is_edited): ?>
                                        <span class="badge bg-info me-1">Edited</span>
                                    <?php endif; ?>
                                    <span class="status-badge approved"><?php echo e($firstRequest->status); ?></span>
                                    
                                    <?php if($isExpiringSoon): ?>
                                        <span class="badge bg-warning text-dark mt-1 d-block" title="This request will expire in <?php echo e(5 - $daysSinceApproval); ?> day(s)">
                                            <i class="fas fa-clock"></i> Expires Soon
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($firstRequest->created_at ? $firstRequest->created_at->setTimezone('Asia/Manila')->format('M d, Y g:i A') : 'N/A'); ?></td>
                <td>                                    <div class="action-buttons">                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo e($purposeId); ?>" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>                                        
                                        <?php if(Auth::user()->role === 'SuperAdmin'): ?>
                                            <a href="<?php echo e(route('requests.downloadRIS', ['stockNumber' => $firstRequest->StockNumber])); ?>" 
                                               class="btn btn-success btn-sm download-ris-btn" 
                                               data-stock-number="<?php echo e($firstRequest->StockNumber); ?>"
                                               data-purpose="<?php echo e($displayPurpose); ?>"
                                               data-batch-key="<?php echo e($batchKey); ?>"
                                               data-request-ids="<?php echo e($requests->pluck('id')->join(',')); ?>"
                                               title="Check Request">
                                               <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- View Modal for group -->
                            <div class="modal fade" id="viewModal<?php echo e($purposeId); ?>" tabindex="-1" aria-labelledby="viewModalLabel<?php echo e($purposeId); ?>" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="viewModalLabel<?php echo e($purposeId); ?>">Request Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="request-summary">
                                                <h4><?php echo e($displayPurpose); ?></h4>
                                                <div class="alert alert-info mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="mb-2"><strong>Total Requests:</strong> <?php echo e($requests->count()); ?></p>
                                                            <p class="mb-0"><strong>Total Items:</strong> <?php echo e($requests->count() + $additionalItemsCount); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="mb-2"><strong>First Requested:</strong> <?php echo e($firstRequest->created_at ? $firstRequest->created_at->setTimezone('Asia/Manila')->format('F d, Y g:i A') : 'N/A'); ?></p>
                                                            <p class="mb-0"><strong>Approved On:</strong> <?php echo e($firstRequest->updated_at ? $firstRequest->updated_at->setTimezone('Asia/Manila')->format('F d, Y g:i A') : 'N/A'); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <ul class="nav nav-tabs mb-3" id="requestTabs<?php echo e($purposeId); ?>" role="tablist">
                                                <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link <?php echo e($index === 0 ? 'active' : ''); ?>" 
                                                                id="request<?php echo e($index); ?>-tab<?php echo e($purposeId); ?>" 
                                                                data-bs-toggle="tab" 
                                                                data-bs-target="#request<?php echo e($index); ?><?php echo e($purposeId); ?>" 
                                                                type="button" 
                                                                role="tab" 
                                                                aria-controls="request<?php echo e($index); ?><?php echo e($purposeId); ?>" 
                                                                aria-selected="<?php echo e($index === 0 ? 'true' : 'false'); ?>">
                                                            <?php echo e($index === 0 ? 'Primary' : 'Request #' . ($index + 1)); ?>

                                                        </button>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                            
                                            <div class="tab-content" id="requestTabContent<?php echo e($purposeId); ?>">
                                                <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="tab-pane fade <?php echo e($index === 0 ? 'show active' : ''); ?>" 
                                                         id="request<?php echo e($index); ?><?php echo e($purposeId); ?>" 
                                                         role="tabpanel" 
                                                         aria-labelledby="request<?php echo e($index); ?>-tab<?php echo e($purposeId); ?>">
                                                        
                                                        <div class="card mb-3">
                                                            <div class="card-header <?php echo e($index === 0 ? 'bg-success text-white' : 'bg-light'); ?>">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span><?php echo e($index === 0 ? 'Primary Request' : 'Additional Request #' . ($index + 1)); ?></span>
                                                                    <span class="small"><?php echo e($request->created_at ? $request->created_at->setTimezone('Asia/Manila')->format('M d, Y g:i A') : 'N/A'); ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row mb-3">
                                                                    <div class="col-md-6">
                                                                        <p><strong>Requester:</strong> <?php echo e(optional($request->user)->First_Name . ' ' . optional($request->user)->Last_Name); ?></p>
                                                                        <p><strong>Office:</strong> <?php echo e(optional($request->user)->Office); ?></p>
                                                                        <p><strong>Stock Number:</strong> <?php echo e($request->StockNumber); ?></p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <p><strong>Supply Name:</strong> <?php echo e($request->supply_name); ?></p>
                                                                        <p><strong>Requested Quantity:</strong> <?php echo e($request->supply_quantity); ?></p>
                                                                        <p><strong>Issue Quantity:</strong> <?php echo e($request->issue_quantity ?? '0'); ?></p>
                                                                        <p><strong>Status:</strong> 
                                                                            <?php if($request->is_edited): ?>
                                                                                <span class="badge bg-info me-1">Edited</span>
                                                                            <?php endif; ?>
                                                                            <span class="status-badge approved"><?php echo e($request->status); ?></span>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Remarks Section - Always displayed for all approved requests -->
                                                                <div class="row mb-3">
                                                                    <div class="col-12">
                                                                        <div class="remarks-container p-3 border rounded bg-light">
                                                                            <h6 class="mb-2"><i class="fas fa-comment-alt me-2"></i>Remarks:</h6>
                                                                            <p class="mb-0">
                                                                                <?php if(isset($request->remarks) && !empty($request->remarks)): ?>
                                                                                    <?php echo e($request->remarks); ?>

                                                                                <?php else: ?>
                                                                                    <span class="text-muted fst-italic">No remarks provided</span>
                                                                                <?php endif; ?>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <?php if($request->items && $request->items->count() > 0): ?>
                                                                    <h6 class="mt-4">Additional Items</h6>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered">
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
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                            
                                            <div class="request-purpose mt-3 p-3 bg-light rounded">
                                                <p class="mb-0"><strong>Purpose:</strong> <?php echo e($displayPurpose); ?></p>
                                            </div>
                                        </div>                                        <div class="modal-footer">                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>                                            
                                            <?php if(Auth::user()->role === 'SuperAdmin'): ?>
                                                <a href="<?php echo e(route('requests.downloadRIS', ['stockNumber' => $firstRequest->StockNumber])); ?>" 
                                                   class="btn btn-success download-ris-btn"
                                                   data-purpose-id="<?php echo e($purposeId); ?>"
                                                   data-stock-number="<?php echo e($firstRequest->StockNumber); ?>"
                                                   data-purpose="<?php echo e($displayPurpose); ?>"
                                                   data-batch-key="<?php echo e($batchKey); ?>"
                                                   data-request-ids="<?php echo e($requests->pluck('id')->join(',')); ?>">
                                                   <i class="fas fa-check me-1"></i> Check Request
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <!-- Improved pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing <?php echo e($approvedRequests->firstItem() ?? 0); ?> to <?php echo e($approvedRequests->lastItem() ?? 0); ?> of <?php echo e($approvedRequests->total()); ?> approved requests
                </div>
                <div>
                    <nav>
                        <ul class="pagination">
                            <?php if(isset($selectedOffice) && $selectedOffice): ?>
                                <?php echo e($approvedRequests->appends(['office' => $selectedOffice])->links('pagination::bootstrap-4')); ?>

                            <?php else: ?>
                                <?php echo e($approvedRequests->links('pagination::bootstrap-4')); ?>

                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Logout Confirmation Modal -->
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>    <script>
        // Update time function
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
        
        // Helper to compute SHA-256 hex digest (used to store a secure hash of the message)
        async function sha256Hex(str) {
            const enc = new TextEncoder();
            const digest = await crypto.subtle.digest('SHA-256', enc.encode(str));
            return Array.from(new Uint8Array(digest)).map(b => b.toString(16).padStart(2, '0')).join('');
        }
+
        // Sidebar toggle
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
            
            // Auto-dismiss success alert after 3 seconds
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                setTimeout(function() {
                    // Use Bootstrap's alert dismiss functionality
                    const bsAlert = new bootstrap.Alert(successAlert);
                    bsAlert.close();
                }, 3000);
            }
            
            // Initialize search functionality
            initializeSearch();
            
            // Populate office filter
            populateOfficeFilter();
            
            // Refresh button
            document.getElementById('refreshBtn').addEventListener('click', function() {
                window.location.reload();
            });
              // Download RIS button event handlers
            document.querySelectorAll('.download-ris-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Store reference to the button for proper scoping
                    const button = this;
                    
                    // Get the request data
                    var stockNumber = button.getAttribute('data-stock-number');
                    var requestIds = button.getAttribute('data-request-ids');
                    var batchKey = button.getAttribute('data-batch-key');
                    var purpose = button.getAttribute('data-purpose');
                    
                    if (!requestIds && !stockNumber) {
                        alert('Error: Could not determine request information');
                        return;
                    }
                    
                    // Show loading indication
                    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
                    button.disabled = true;
                    
                    // Determine which row to remove after successful processing
                    var row = button.closest('tr');
                    var modalElement = button.closest('.modal');
                    
                    // Send AJAX request to update status - pass all request IDs in the batch
                    fetch("<?php echo e(route('requests.updateStatusAndDownload')); ?>", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',               // ask Laravel for JSON
                            'X-Requested-With': 'XMLHttpRequest',       // hint it's an AJAX call
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({
                            requestIds: requestIds ? requestIds.split(',') : [],
                            stockNumber: stockNumber,
                            batchKey: batchKey,
                            purpose: purpose,
                            newStatus: 'released'
                        })
                    })
                    .then(async (response) => {
                        // Be defensive: not all responses are JSON (could be HTML error/redirect)
                        const contentType = response.headers.get('content-type') || '';
                        let data = null;

                        try {
                            if (contentType.includes('application/json')) {
                                data = await response.json();
                            } else {
                                const text = await response.text();
                                data = JSON.parse(text); // try parse in case backend still sent JSON without header
                            }
                        } catch (_) {
                            // Fallback when body isn't JSON
                            data = { success: response.ok, message: '' };
                        }

                        if (!response.ok || (data && data.success === false)) {
                            const message = (data && data.message) || `Request failed (HTTP ${response.status})`;
                            throw new Error(message);
                        }

                        // Ensure we always have a minimal success payload
                        return data || { success: true };
                    })
                    .then(data => {
                        if (data.success) {
                            <?php if(Auth::user()->role === 'SuperAdmin'): ?>
                                // For SuperAdmin, skip download but still redirect
                                console.log('Successfully updated ' + (data.updated_count || 1) + ' request(s) to released status');
                                
                                // Close modal if open
                                if (modalElement) {
                                    const modal = bootstrap.Modal.getInstance(modalElement);
                                    if (modal) modal.hide();
                                }
                                
                                // Remove the row from the table immediately for better UX
                                if (row) {
                                    row.style.opacity = '0.5';
                                    row.style.transition = 'opacity 0.3s ease';
                                    setTimeout(() => {
                                        row.remove();
                                    }, 300);
                                }
                                
                                // Prepare success message, store encoded message + SHA-256 hash, then redirect without query string
                                const successMessage = data.updated_count > 1
                                    ? `${data.updated_count} requests have been processed and moved to Released status.`
                                    : 'Request has been processed and moved to Released status.';

                                (async () => {
                                    try {
                                        const encoded = btoa(unescape(encodeURIComponent(successMessage)));
                                        sessionStorage.setItem('releasedSuccess', encoded);
                                        const hash = await sha256Hex(encoded);
                                        sessionStorage.setItem('releasedSuccessHash', hash);
                                    } catch (e) {
                                        // fallback if encoding/subtle fails: store plaintext hashless
                                        try { sessionStorage.setItem('releasedSuccess', successMessage); } catch (_) {}
                                    } finally {
                                        window.location.href = "<?php echo e(route('requests.released')); ?>";
                                    }
                                })();
                            <?php else: ?>
                                 // For other users, trigger download then redirect
                                 var downloadUrl = "<?php echo e(route('requests.downloadRIS', ['stockNumber' => ':stockNumber'])); ?>";
                                 downloadUrl = downloadUrl.replace(':stockNumber', stockNumber);
                                 
                                 // Create a temporary link to trigger download
                                 const link = document.createElement('a');
                                 link.href = downloadUrl;
                                 link.download = '';
                                 document.body.appendChild(link);
                                 link.click();
                                 document.body.removeChild(link);
                                 
                                 // Close modal if open
                                 if (modalElement) {
                                     const modal = bootstrap.Modal.getInstance(modalElement);
                                     if (modal) modal.hide();
                                 }
                                 
                                 // Remove the row from the table
                                 if (row) {
                                     row.style.opacity = '0.5';
                                     row.style.transition = 'opacity 0.3s ease';
                                     setTimeout(() => {
                                         row.remove();
                                     }, 300);
                                 }
                                 
                                // Show success message and redirect to released page immediately.
                                // Store encoded message + SHA-256 hash then redirect without query string.
                                const successMessage = data.updated_count > 1
                                    ? `${data.updated_count} requests have been moved to Released status and RIS downloaded successfully.`
                                    : 'Request has been moved to Released status and RIS downloaded successfully.';

                                (async () => {
                                    try {
                                        const encoded = btoa(unescape(encodeURIComponent(successMessage)));
                                        sessionStorage.setItem('releasedSuccess', encoded);
                                        const hash = await sha256Hex(encoded);
                                        sessionStorage.setItem('releasedSuccessHash', hash);
                                    } catch (e) {
                                        try { sessionStorage.setItem('releasedSuccess', successMessage); } catch (_) {}
                                    } finally {
                                        window.location.href = "<?php echo e(route('requests.released')); ?>";
                                    }
                                })();
                             <?php endif; ?>
                         } else {
                             alert('Error: ' + (data.message || 'Could not update request status'));
                             <?php if(Auth::user()->role === 'SuperAdmin'): ?>
                                 button.innerHTML = '<i class="fas fa-check me-1"></i> Check Request';
                             <?php else: ?>
                                 button.innerHTML = '<i class="fas fa-download me-1"></i> Download RIS';
                             <?php endif; ?>
                             button.disabled = false;
                         }
                     })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(error.message || 'An error occurred. Please try again.');
                        <?php if(Auth::user()->role === 'SuperAdmin'): ?>
                            button.innerHTML = '<i class="fas fa-check me-1"></i> Check Request';
                        <?php else: ?>
                            button.innerHTML = '<i class="fas fa-download me-1"></i> Download RIS';
                        <?php endif; ?>
                        button.disabled = false;
                    });
                });
            });});

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
        
        // Search functionality with role-based restrictions
        function initializeSearch() {
            const searchInput = document.getElementById('searchInput');
            const officeFilter = document.getElementById('officeFilter');
            const table = document.getElementById('requestsTable');
            const rows = table.getElementsByTagName('tr');
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
                    filterTable();
                }
            });
            
            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedOffice = officeFilter.value;
                
                for (let i = 1; i < rows.length; i++) {
                    const row = rows[i];
                    if (row.classList.contains('group-header')) {
                        let displayRow = true;
                        const rowOffice = row.getAttribute('data-office');
                        
                        // Role-based office filtering
                        if (!(userRole === 'superadmin' || userRole === 'admin')) {
                            if (rowOffice !== userOffice) {
                                displayRow = false;
                            }
                        }
                        
                        // Filter by search term
                        if (displayRow && searchTerm) {
                            let matchFound = false;
                            const cells = row.getElementsByTagName('td');
                            
                            for (let j = 0; j < cells.length; j++) {
                                if (cells[j].textContent.toLowerCase().includes(searchTerm)) {
                                    matchFound = true;
                                    break;
                                }
                            }
                            
                            if (!matchFound) {
                                displayRow = false;
                            }
                        }
                        
                        row.style.display = displayRow ? '' : 'none';
                    }
                }
            }
            
            // Add event listeners
            searchInput.addEventListener('keyup', filterTable);
            
            // Apply initial filtering for non-SuperAdmin users
            if (!(userRole === 'superadmin' || userRole === 'admin')) {
                filterTable();
            }
        }

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
</body>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u741082352/domains/smis.ojt-ims-bsit.net/public_html/resources/views/Admin/requests/approved.blade.php ENDPATH**/ ?>