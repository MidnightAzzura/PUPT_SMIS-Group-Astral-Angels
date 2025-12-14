@extends('layout.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/pending.css') }}">
<link rel="stylesheet" href="{{ asset('css/notification-badges.css') }}">
<link rel="stylesheet" href="{{ asset('css/collapsed-sidebar-badges.css') }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode-switch.css') }}">
<link rel="stylesheet" href="{{ asset('css/user-settings-dropdown.css') }}">
<script src="{{ asset('js/enhanced-notifications.js') }}" defer></script>
<body>
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
            @endif        <a href="{{ route('notifications.index') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
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
                </a>            <a href="{{ route('requests.approved') }}" style="display: flex; align-items: center; margin-bottom: 10px;" onclick="clearPageNotifications('approved')">
                    <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> 
                    <span>Approved</span>
                </a>
                <a href="{{ route('requests.released') }}" style="display: flex; align-items: center; margin-bottom: 10px;">
                    <i class="fas fa-truck" style="margin-right: 8px;"></i> <span>Released</span>
                </a>            <a href="{{ route('requests.disapproved') }}" style="display: flex; align-items: center; margin-bottom: 10px;" onclick="clearPageNotifications('disapproved')">
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
                    @if(Auth::user()->role == 'SuperAdmin')
                        <i class="fas fa-crown text-warning me-2"></i>Welcome SuperAdmin,
                        <span class="user-name">{{ Auth::user()->First_Name }} {{ Auth::user()->Middle_Initial }}. {{ Auth::user()->Last_Name }}!</span>
                    @else
                        <i class="fas fa-user-shield me-2"></i>Welcome {{ ucfirst(Auth::user()->role) }},
                        <span class="user-name">{{ Auth::user()->First_Name }} {{ Auth::user()->Middle_Initial }}. {{ Auth::user()->Last_Name }}!</span>
                    @endif
                </h2>
                <p class="header-subtitle">Manage and view all pending supply requests</p>
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
        
        <div class="header">
            <h2>Pending Requests</h2>
            <div id="currentDateTime" class="text-center mb-3"></div>
        </div>
        <div class="container mt-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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

            @php
                // Role-based filtering
                $userRole = strtolower(trim(Auth::user()->role));
                $userOffice = Auth::user()->Office;
                
                // Filter requests based on user role
                // NOTE: Admins should be able to view all offices like SuperAdmin
                if (in_array($userRole, ['superadmin', 'admin'])) {
                    // SuperAdmin (and Admin) sees all requests
                    $filteredRequests = $pendingRequests;
                } else {
                    // Other users only see requests from their office
                    $filteredRequests = $pendingRequests->filter(function($request) use ($userOffice) {
                        return $request->user && $request->user->Office === $userOffice;
                    });
                }

                // Normalize purposes and create batch groups
                $normalizedRequests = $filteredRequests->map(function($request) {
                    // Create a copy to avoid modifying the original
                    $request->normalizedPurpose = strtolower(trim($request->Purpose));
                    
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
            @endphp

            <div class="table-responsive">
                <table class="table table-hover" id="requestsTable">
                    <thead>
                        <tr>
                            <th width="10%">Stock #</th>
                            <th width="10%">Requester</th>
                            <th width="10%">Office</th>
                            <th width="20%">Supply Name</th>
                            <th width="5%">Qty</th>
                            <th width="15%">Purpose</th>
                            <th width="10%">Status</th>
                            <th width="10%">Date/Time</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groupedRequests as $batchKey => $requests)
                            @php
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
                            @endphp
                            <tr class="group-header" data-office="{{ $firstRequest->user->Office ?? 'N/A' }}">
                                <td>{{ $firstRequest->StockNumber }}</td>
                                <td>
                                    @if($firstRequest->user)
                                        {{ $firstRequest->user->Last_Name }} 
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $firstRequest->user->Office ?? 'N/A' }}</td>
                                <td>
                                    {{ $firstRequest->supply_name }}
                                    @if($totalItems > 1 || $additionalItemsCount > 0)
                                        <span class="badge bg-info">+{{ $totalItems - 1 + $additionalItemsCount }} more</span>
                                    @endif
                                </td>
                                <td>{{ $firstRequest->supply_quantity }}</td>
                                <td>{{ Str::limit($displayPurpose, 30) }}</td>
                                <td><span class="status-badge pending">{{ $firstRequest->status }}</span></td>
                                <td>{{ $firstRequest->created_at ? $firstRequest->created_at->setTimezone('Asia/Manila')->format('M d, Y g:i A') : 'N/A' }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal{{ $purposeId }}" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(Auth::user()->role === 'SuperAdmin' || Auth::user()->role === 'Admin')
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="showDenyModal('{{ $batchKey }}', '{{ htmlspecialchars($firstRequest->supply_name) }}', {{ $totalItems }}, {{ $additionalItemsCount }})" 
                                                    title="Deny Request Group">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        @if(Auth::user()->role === 'SuperAdmin' || Auth::user()->role === 'Admin')
                                            <a href="{{ route('requests.editRIS', ['stockNumber' => $firstRequest->StockNumber, 'batchKey' => $batchKey]) }}" 
                                               class="btn btn-primary btn-sm" title="Edit RIS">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- View Modal for group -->
                            <div class="modal fade" id="viewModal{{ $purposeId }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $purposeId }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="viewModalLabel{{ $purposeId }}">Request Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="request-summary">
                                                <h4>{{ $displayPurpose }}</h4>
                                                <div class="alert alert-info mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="mb-2"><strong>Total Requests:</strong> {{ $requests->count() }}</p>
                                                            <p class="mb-0"><strong>Total Items:</strong> {{ $requests->count() + $additionalItemsCount }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="mb-2"><strong>First Requested:</strong> {{ $firstRequest->created_at ? $firstRequest->created_at->setTimezone('Asia/Manila')->format('F d, Y g:i A') : 'N/A' }}</p>
                                                            <p class="mb-0"><strong>Latest Request:</strong> {{ $requests->max('created_at') ? \Carbon\Carbon::parse($requests->max('created_at'))->setTimezone('Asia/Manila')->format('F d, Y g:i A') : 'N/A' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <ul class="nav nav-tabs mb-3" id="requestTabs{{ $purposeId }}" role="tablist">
                                                @foreach($requests as $index => $request)
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                                                id="request{{ $index }}-tab{{ $purposeId }}" 
                                                                data-bs-toggle="tab" 
                                                                data-bs-target="#request{{ $index }}{{ $purposeId }}" 
                                                                type="button" 
                                                                role="tab" 
                                                                aria-controls="request{{ $index }}{{ $purposeId }}" 
                                                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                                            {{ $index === 0 ? 'Primary' : 'Request #' . ($index + 1) }}
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            
                                            <div class="tab-content" id="requestTabContent{{ $purposeId }}">
                                                @foreach($requests as $index => $request)
                                                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                                                         id="request{{ $index }}{{ $purposeId }}" 
                                                         role="tabpanel" 
                                                         aria-labelledby="request{{ $index }}-tab{{ $purposeId }}">
                                                        
                                                        <div class="card mb-3">
                                                            <div class="card-header {{ $index === 0 ? 'bg-primary text-white' : 'bg-light' }}">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>{{ $index === 0 ? 'Primary Request' : 'Additional Request #' . ($index + 1) }}</span>
                                                                    <span class="small">{{ $request->created_at ? $request->created_at->setTimezone('Asia/Manila')->format('M d, Y g:i A') : 'N/A' }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row mb-3">
                                                                    <div class="col-md-6">
                                                                        <p><strong>Requester:</strong> {{ optional($request->user)->First_Name . ' ' . optional($request->user)->Last_Name }}</p>
                                                                        <p><strong>Office:</strong> {{ optional($request->user)->Office }}</p>
                                                                        <p><strong>Stock Number:</strong> {{ $request->StockNumber }}</p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <p><strong>Supply Name:</strong> {{ $request->supply_name }}</p>
                                                                        <p><strong>Quantity:</strong> {{ $request->supply_quantity }}</p>
                                                                        <p><strong>Status:</strong> <span class="status-badge pending">{{ $request->status }}</span></p>
                                                                    </div>
                                                                </div>
                                                                
                                                                @if($request->items && $request->items->count() > 0)
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
                                                                                @foreach($request->items as $item)
                                                                                <tr>
                                                                                    <td>{{ $item->StockNumber }}</td>
                                                                                    <td>{{ $item->supply_name }}</td>
                                                                                    <td>{{ $item->unit ?? 'N/A' }}</td>
                                                                                    <td>{{ $item->supply_quantity }}</td>
                                                                                </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            
                                            <div class="request-purpose mt-3 p-3 bg-light rounded">
                                                <p class="mb-0"><strong>Purpose:</strong> {{ $displayPurpose }}</p>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Improved pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing {{ $pendingRequests->firstItem() ?? 0 }} to {{ $pendingRequests->lastItem() ?? 0 }} of {{ $pendingRequests->total() }} pending requests
                </div>
                <div>
                    <nav aria-label="Pending requests pagination">
                        <ul class="pagination">
                            @if(isset($selectedOffice) && $selectedOffice)
                                {{ $pendingRequests->appends(['office' => $selectedOffice])->links('pagination::bootstrap-4') }}
                            @else
                                {{ $pendingRequests->links('pagination::bootstrap-4') }}
                            @endif
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

    <!-- Deny Confirmation Modal -->
    <div class="modal fade" id="denyConfirmModal" tabindex="-1" aria-labelledby="denyConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #dc3545; color: white;">
                    <h5 class="modal-title" id="denyConfirmModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deny Request Group
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p style="font-size: 18px; color: #333;">Are you sure you want to deny this entire request group?</p>
                    <div class="alert alert-warning">
                        <p class="mb-1"><strong>Primary Item:</strong> <span id="denyItemName"></span></p>
                        <p class="mb-1"><strong>Group ID:</strong> <span id="denyBatchKey"></span></p>
                        <p class="mb-0"><strong>Total Items:</strong> <span id="denyTotalItems"></span></p>
                    </div>
                    <p class="text-danger mt-3">This action cannot be undone and will deny ALL requests in this group.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">CANCEL</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDenyBtn">
                        <span id="denyBtnText">CONFIRM DENY GROUP</span>
                        <span id="denyBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                    <form id="deny-form" action="" method="POST" class="d-none">
                        @csrf
                        <input type="hidden" id="denyBatchKeyInput" name="batchKey" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
        
        // Set sidebar state on page load
        document.addEventListener('DOMContentLoaded', function () {
            console.log('🚀 Pending page initialized');
            
            // Initialize CSRF token refresh for this page
            initializeCSRFForPendingPage();
            
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
            
            // Populate office filter with role-based restrictions
            populateOfficeFilter();
            
            // Refresh button
            const refreshBtn = document.getElementById('refreshBtn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    console.log('🔄 Manual page refresh triggered');
                    window.location.reload();
                });
            }
            
            // Initialize deny button error recovery
            initializeDenyErrorRecovery();
            
            console.log('✅ Pending page fully initialized with simplified deny system');
        });

        // CSRF initialization for pending page
        function initializeCSRFForPendingPage() {
            console.log('� CSRF system initialized for pending page');
        }

        // Error recovery system for deny operations
        function initializeDenyErrorRecovery() {
            // Listen for modal hide events to reset state
            const denyModal = document.getElementById('denyConfirmModal');
            if (denyModal) {
                denyModal.addEventListener('hidden.bs.modal', function() {
                    console.log('🔄 Deny modal hidden, resetting state');
                    resetDenyModal();
                });
            }
            
            // Add escape key handler
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('denyConfirmModal'));
                    if (modal) {
                        resetDenyModal();
                    }
                }
            });
        }

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
        
        // Sidebar toggle function
        function toggleSidebar() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarState', sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded');
        }
        
        // Populate office filter function with role-based filtering
        function populateOfficeFilter() {
            const officeFilter = document.getElementById('officeFilter');
            const userRole = '{{ strtolower(trim(Auth::user()->role)) }}';
            const userOffice = '{{ Auth::user()->Office }}';
            const selectedOffice = '{{ $selectedOffice ?? '' }}';
            const allOffices = @json($allOffices ?? []);
            
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
            const userRole = '{{ strtolower(trim(Auth::user()->role)) }}';
            const userOffice = '{{ Auth::user()->Office }}';
            
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

// Deny confirmation functions - SENIOR DEVELOPER ENHANCED VERSION
function showDenyModal(batchKey, primaryItemName, totalRequests, additionalItems) {
    console.log('🎯 Opening deny modal for batch:', batchKey);
    
    // Reset modal state first
    resetDenyModal();
    
    // Set values in the modal
    document.getElementById('denyBatchKey').textContent = batchKey;
    document.getElementById('denyItemName').textContent = primaryItemName;
    
    // Calculate and display total items (requests + additional items)
    const totalItems = totalRequests + additionalItems;
    document.getElementById('denyTotalItems').textContent = totalItems;
    
    // Set the form action and batch key input
    const denyForm = document.getElementById('deny-form');
    denyForm.action = "{{ url('/requests/deny-group') }}";
    document.getElementById('denyBatchKeyInput').value = batchKey;
    
    // Show the modal
    const denyModal = new bootstrap.Modal(document.getElementById('denyConfirmModal'));
    denyModal.show();
    
    // CRITICAL FIX: Remove any existing event listeners first
    const confirmBtn = document.getElementById('confirmDenyBtn');
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // Add fresh click event listener
    document.getElementById('confirmDenyBtn').addEventListener('click', function() {
        confirmDeny(batchKey, primaryItemName);
    });
}

// Enhanced reset function
function resetDenyModal() {
    // Reset loading state
    const denyBtnText = document.getElementById('denyBtnText');
    const denyBtnSpinner = document.getElementById('denyBtnSpinner');
    const confirmBtn = document.getElementById('confirmDenyBtn');
    
    if (denyBtnText) denyBtnText.classList.remove('d-none');
    if (denyBtnSpinner) denyBtnSpinner.classList.add('d-none');
    if (confirmBtn) {
        confirmBtn.disabled = false;
    }
    
    console.log('🔄 Modal state reset');
}

// Simplified and robust confirm deny function - Following approval pattern
function confirmDeny(batchKey, primaryItemName) {
    console.log('🚀 Starting deny process for:', batchKey);
    
    // Prevent double-clicking
    const confirmBtn = document.getElementById('confirmDenyBtn');
    if (confirmBtn.disabled) {
        console.log('⚠️ Button already disabled, preventing double submission');
        return;
    }
    
    // Show loading state immediately
    document.getElementById('denyBtnText').classList.add('d-none');
    document.getElementById('denyBtnSpinner').classList.remove('d-none');
    confirmBtn.disabled = true;
    
    // Hide modal first to prevent user interaction
    const modal = bootstrap.Modal.getInstance(document.getElementById('denyConfirmModal'));
    if (modal) {
        modal.hide();
    }
    
    // Show processing message
    showSuccessMessage(`Processing denial for "${primaryItemName}"...`);
    
    // CRITICAL FIX: Use traditional form submission like approval system
    // This ensures proper server-side processing and redirect
    try {
        const denyForm = document.getElementById('deny-form');
        if (denyForm) {
            // Ensure batch key is properly set
            document.getElementById('denyBatchKeyInput').value = batchKey;
            
            // Add timestamp for tracking
            const timestampInput = document.createElement('input');
            timestampInput.type = 'hidden';
            timestampInput.name = 'denial_timestamp';
            timestampInput.value = new Date().toISOString();
            denyForm.appendChild(timestampInput);
            
            console.log('📤 Submitting denial form traditionally...');
            console.log('Form action:', denyForm.action);
            console.log('Batch key:', batchKey);
            
            // Submit form directly - this will follow the same pattern as approval
            denyForm.submit();
            
            // FALLBACK: If form submission doesn't work, force navigation after 3 seconds
            setTimeout(() => {
                console.log('🔄 Fallback: Checking if still on pending page...');
                if (window.location.pathname.includes('/pending')) {
                    console.log('🚀 Fallback: Redirecting to disapproved page...');
                    window.location.href = "{{ route('requests.disapproved') }}";
                }
            }, 3000);
            
        } else {
            throw new Error('Deny form not found');
        }
    } catch (error) {
        console.error('❌ Error during form submission:', error);
        resetDenyModal();
        alert('Error submitting denial. Please refresh the page and try again.');
    }
}

// Success message display function
function showSuccessMessage(message) {
    // Remove any existing success messages
    const existingAlerts = document.querySelectorAll('.alert-success');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new success alert
    const successAlert = document.createElement('div');
    successAlert.className = 'alert alert-success alert-dismissible fade show';
    successAlert.style.position = 'fixed';
    successAlert.style.top = '20px';
    successAlert.style.right = '20px';
    successAlert.style.zIndex = '9999';
    successAlert.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(successAlert);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (successAlert && successAlert.parentNode) {
            successAlert.remove();
        }
    }, 3000);
}

// Function to clear page notifications
function clearPageNotifications(page) {
    fetch('{{ route("notifications.clear.page") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            page: page
        })
    })
    .then(response => response.json())
    .then((data) => {
        if (data.success) {
            // Update notification badges
            updateNotificationBadges();
        }
    })
    .catch(error => console.error('Error clearing notifications:', error));
}

// Enhanced function to update notification badges with animations
function updateNotificationBadges() {
    fetch('{{ route("notifications.counts") }}')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update approved notification badge with animation
            const approvedBadge = document.querySelector('a[href*="approved"] .notification-badge');
            updateBadgeWithAnimation(approvedBadge, data.counts.approved, 'approved');
            
            // Update denied notification badge with animation
            const deniedBadge = document.querySelector('a[href*="disapproved"] .notification-badge');
            updateBadgeWithAnimation(deniedBadge, data.counts.denied, 'denied');
            
            // Update total unread badge with animation
            const totalBadges = document.querySelectorAll('a[href*="notifications"] .notification-badge');
            totalBadges.forEach(badge => {
                updateBadgeWithAnimation(badge, data.counts.total_unread, 'total');
            });
            
            // Update any other notification badges
            const allBadges = document.querySelectorAll('.notification-badge');
            allBadges.forEach(badge => {
                if (!badge.closest('a[href*="approved"]') && 
                    !badge.closest('a[href*="disapproved"]') && 
                    !badge.closest('a[href*="notifications"]')) {
                    updateBadgeWithAnimation(badge, data.counts.total_unread, 'general');
                }
            });
        }
    })
    .catch(error => console.error('Error updating badges:', error));
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
                
                // Add appear animation for new badges
                if (currentCount === 0) {
                    badge.style.animation = 'badgeAppear 0.5s ease-out';
                }
            } else {
                badge.style.display = 'none';
            }
            
            badge.classList.remove('updating');
        }, 300);
    }
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
@endsection