@extends('layout.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/enhanced-admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/notification-badges.css') }}">
<link rel="stylesheet" href="{{ asset('css/universal-sidebar-scrollbar.css') }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode-switch.css') }}">
<link rel="stylesheet" href="{{ asset('css/user-settings-dropdown.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Force correct toggle button positioning to match dashboard -->
<style>
/* Force User Management header to be white for readability on maroon background */
.header h2 {
    color: #ffffff !important;
}

/* Dark mode - keep white */
[data-theme="dark"] .header h2 {
    color: #ffffff !important;
}

.toggle-sidebar-btn {
    position: fixed !important;
    top: 2px !important; /* moved up from 10px */
    left: 10px !important;
    z-index: 1000 !important;
    background-color: #800000 !important;
    color: white !important;
    border: none !important;
    border-radius: 5px !important;
    padding: 8px 12px !important;
    cursor: pointer !important;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2) !important;
}

/* AGGRESSIVE PUP LOGO FIX - Override all CSS that might be hiding it */
.sidebar img[src*="puplogo.png"],
.sidebar img[alt*="PUP Logo"],
.sidebar-content img[src*="puplogo.png"],
div.sidebar img {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 60px !important;
    height: 60px !important;
    padding: 0 !important;
    margin: 20px auto !important;
    max-width: 60px !important;
    max-height: 60px !important;
    position: relative !important;
    z-index: 10 !important;
}

/* KEEP LOGO VISIBLE EVEN WHEN COLLAPSED - Make it smaller but still visible */
.sidebar.collapsed img[src*="puplogo.png"] {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 40px !important;
    height: 40px !important;
    margin: 10px auto !important;
}

/* Force sidebar content to show properly */
.sidebar-content {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    width: 100% !important;
}

/* Database Management Button Styles */
.action-bar .btn-group {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.action-bar .btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.action-bar .btn-success:hover {
    background: linear-gradient(135deg, #218838, #1e7e34);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.action-bar .btn-warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    border: none;
    color: #212529;
    font-weight: 600;
    transition: all 0.3s ease;
}

.action-bar .btn-warning:hover {
    background: linear-gradient(135deg, #e0a800, #dc6502);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    color: white;
}

.action-bar .btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.action-bar .btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

/* Database Modal Enhancements */
.modal-content {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    border: none;
}

.modal-header.bg-success {
    background: linear-gradient(135deg, #28a745, #20c997) !important;
    border-radius: 15px 15px 0 0;
}

.modal-header.bg-warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14) !important;
    border-radius: 15px 15px 0 0;
}

/* Add themed danger header to match system theme */
.modal-header.bg-danger {
    background: linear-gradient(135deg, #dc3545, #a30018) !important;
    color: #fff !important;
    border-radius: 15px 15px 0 0 !important;
}

.alert-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    border: none;
    color: white;
    border-radius: 10px;
}

.alert-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    border: none;
    color: white;
    border-radius: 10px;
}

/* Progress indicator for file upload */
.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Button animation */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.btn:active {
    animation: pulse 0.3s ease-in-out;
}

/* Blur the background (no tint) when Add/Edit User modal is open */
.modal-backdrop.backdrop-blur,
.modal-backdrop.backdrop-blur.show {
    background-color: transparent !important; /* remove any dark/black tint */
    opacity: 1 !important;                   /* override Bootstrap default opacity */
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    transition: none !important;             /* avoid flicker from fade transition */
}

/* Optional: keep page content sharp; blur is handled by backdrop */
body.modal-open .content-wrapper {
    filter: none;
}

/* Position the Add User password toggle beside the "Password" label (no logic changes) */
#addUserModal #password + .password-toggle {
    position: absolute;
    top: -6px;         /* moved slightly up */
    right: 10px;       /* moved slightly inward from the edge */
    transform: none;
    background: transparent;
    border: 0;
    color: #6b7280;
    padding: 0;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
#addUserModal #password + .password-toggle i { margin-right: 2px; }
#addUserModal #password + .password-toggle .password-toggle-label {
    font-size: .875rem;
    margin-left: .25rem;
    color: #6b7280;
}

/* NEW: Modern pagination styles */
.modern-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    list-style: none;
    padding: 0;
    margin: 1.5rem 0;
}

.modern-pagination li {
    margin: 0 0.25rem;
}

.modern-pagination .page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border: 1px solid #007bff;
    border-radius: 0.375rem;
    color: #007bff;
    text-decoration: none;
    transition: background-color 0.3s, color 0.3s;
}

.modern-pagination .page-link:hover {
    background-color: #007bff;
    color: white;
}

.modern-pagination .active .page-link {
    background-color: #007bff;
    color: white;
    border: 1px solid #007bff;
}

.modern-pagination .disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: transparent;
    border-color: #dee2e6;
}
</style>

<!-- Include the bulletproof JavaScript -->
<script src="{{ asset('js/sidebar-fix.js') }}" defer></script>

<body>
    <button class="toggle-sidebar-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

<!-- UNIVERSAL SIDEBAR STRUCTURE - BULLETPROOF VERSION -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-content">
        <img src="{{ asset('Pup/puplogo.png') }}" style="width: 60px; height: 60px; padding: 0; margin: 20px auto;" alt="PUP Logo">
        <h3 style="margin-bottom: 20px;">SMIS</h3>
        <a href="{{ route(Auth::user()->role === 'superadmin' ? 'SuperAdmin.dashboard' : 'Admin.admin') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
            <i class="fas fa-home" style="margin-right: 8px;"></i> <span>Home</span>
        </a>
        <a href="{{ route('usersmanage.index') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px; background-color: rgba(255, 255, 255, 0.1);">
            <i class="fas fa-users" style="margin-right: 8px;"></i> <span>User Management</span>
        </a>        
        <a href="{{ route('notifications.index') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
            <i class="fas fa-bell" style="margin-right: 8px;"></i> 
            <span>Notifications</span>
            <span class="notification-badge" style="display: {{ (isset($notificationCounts) && $notificationCounts['total_unread'] > 0) ? 'inline-flex' : 'none' }};">
                {{ $notificationCounts['total_unread'] ?? 0 }}
            </span>
            {{-- DEBUG: Show if notificationCounts exists --}}
            @if(isset($notificationCounts))
                <small style="color: white; font-size: 10px;">({{ $notificationCounts['total_unread'] ?? 0 }})</small>
            @else
                <small style="color: red; font-size: 10px;">(NO DATA)</small>
            @endif
        </a>
        <a href="#" class="dropdown-btn" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
            <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
            <i class="fa fa-caret-down" style="margin-left: auto;"></i>
        </a>
        <div class="dropdown-container" style="margin: 10px auto;">
            <a href="{{ route('requests.pending') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-clock" style="margin-right: 8px;"></i> 
                <span>Pending</span>
            </a>            
            <a href="{{ route('requests.approved') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> 
                <span>Approved</span>
            </a>
            <a href="{{ route('requests.released') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-truck" style="margin-right: 8px;"></i> 
                <span>Released</span>
            </a>          
            <a href="{{ route('requests.disapproved') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-times-circle" style="margin-right: 8px;"></i> 
                <span>Disapproved</span>
            </a>
        </div>
        <a href="{{ route('reports') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
            <i class="fas fa-chart-line" style="margin-right: 8px;"></i> <span>Reports</span>
        </a>
        <a href="{{ route('help.support') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
            <i class="fas fa-question-circle" style="margin-right: 8px;"></i> <span>Help & Support</span>
        </a>
        <!-- NEW: Rate / Questionnaire Button (replace href with your Google Form link) -->
            <a id="rateSystemBtn" href="https://forms.gle/12SD5ZjPTXbsgqzC7" target="_blank" rel="noopener" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px; background: rgba(255,255,255,0.08); border-radius:5px;">
                <i class="fas fa-star-half-alt" style="margin-right: 8px;"></i> <span>Rate Our System</span>
            </a>
    </div>
</div>

<div class="content-wrapper">
    <!-- User Settings Dropdown -->
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
    
    <div class="container-fluid">
        <!-- Add Welcome Header Section -->
        <div class="dashboard-header modern-header" style="background: linear-gradient(135deg, #800020, #600018) !important; background-color: #800020 !important; border-radius: 10px !important; margin-bottom: 1.5rem !important; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;">
            <div class="header-content" style="color: white !important; padding: 0.75rem 1.5rem !important; background: transparent !important;">
                <h2 class="welcome-text" style="color: #ffffff !important; font-size: 1.5rem !important; margin: 0 !important;">
                    @if(Auth::user()->role == 'SuperAdmin')
                        <i class="fas fa-crown text-warning me-2" style="color: #fbbf24 !important;"></i>Welcome SuperAdmin,
                        <span class="user-name" style="color: #ffffff !important; font-weight: bold !important; background: none !important;">{{ Auth::user()->First_Name }} {{ Auth::user()->Middle_Initial }}. {{ Auth::user()->Last_Name }}!</span>
                    @else
                        <i class="fas fa-user-shield me-2" style="color: #06b6d4 !important;"></i>Welcome Admin,
                        <span class="user-name" style="color: #ffffff !important; font-weight: bold !important; background: none !important;">{{ Auth::user()->First_Name }} {{ Auth::user()->Middle_Initial }}. {{ Auth::user()->Last_Name }}!</span>
                    @endif
                </h2>
                <p class="header-subtitle" style="color: white !important; margin: 0.25rem 0 0 0 !important; font-size: 0.9rem !important;">Manage user accounts and permissions efficiently</p>
            </div>
        </div>
        
        <div class="header">
            <h2 id="userManagementHeader" style="color: #ffffff !important;"><i class="fas fa-users mr-2"></i> User Management</h2>
        </div>
        
        <script>
            // Force header color immediately on page load
            document.addEventListener('DOMContentLoaded', function() {
                const header = document.getElementById('userManagementHeader');
                if (header) {
                    // Always white for readability on maroon background
                    header.style.color = '#ffffff';
                    header.style.setProperty('color', '#ffffff', 'important');
                }
                
                // Also listen for theme changes
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'data-theme') {
                            // Always white for readability
                            header.style.setProperty('color', '#ffffff', 'important');
                        }
                    });
                });
                observer.observe(document.documentElement, { attributes: true });
            });
        </script>
        
        {{-- Global success overlay (click anywhere to dismiss; auto-dismiss by JS) --}}
        @if(session('success'))
            <div id="globalSuccessOverlay" class="global-success-overlay d-none" role="status" aria-live="polite" data-auto-dismiss="5000">
                <div class="global-success-toast" role="dialog" aria-label="Success notification">
                    <div class="toast-left">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="toast-body">
                        <div class="toast-title">Success</div>
                        <div class="toast-message">{{ session('success') }}</div>
                    </div>
                    <button class="global-success-close" aria-label="Close notification">&times;</button>
                </div>
            </div>
        @endif

        <div class="action-bar">
            @if(strtolower(Auth::user()->role) === 'superadmin' || Auth::user()->role === 'SuperAdmin')
                <div class="btn-group mr-3" role="group">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#backupDatabaseModal" title="Backup Database">
                        <i class="fas fa-download"></i> Backup Database
                    </button>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#restoreDatabaseModal" title="Restore Database">
                        <i class="fas fa-upload"></i> Restore Database
                    </button>
                </div>
            @endif
            @if(Auth::user()->role === 'SuperAdmin' || Auth::user()->can('add user'))
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus"></i> Add New User
                </button>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><i class="fas fa-building mr-2"></i> Office</th>
                        <th><i class="fas fa-user mr-2"></i> First Name</th>
                        <th><i class="fas fa-id-card mr-2"></i> Middle Initial</th>
                        <th><i class="fas fa-user-tag mr-2"></i> Last Name</th>
                        <th><i class="fas fa-user-shield mr-2"></i> Role</th>
                        <th class="text-center"><i class="fas fa-cogs mr-2"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->office ?? $user->Office }}</td>
                            <td>{{ $user->first_name ?? $user->First_Name }}</td>
                            <td>{{ $user->middle_initial ?? $user->Middle_Initial }}</td>
                            <td>{{ $user->last_name ?? $user->Last_Name }}</td>
                            <td>
                                <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if(Auth::user()->role === 'SuperAdmin' || Auth::user()->can('edit-user'))
                                        <button class="btn btn-warning btn-sm edit-user-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editUserModal" 
                                            data-user-id="{{ $user->id }}"
                                            data-user-firstname="{{ $user->first_name ?? $user->First_Name }}"
                                            data-user-middleinitial="{{ $user->middle_initial ?? $user->Middle_Initial }}"
                                            data-user-lastname="{{ $user->last_name ?? $user->Last_Name }}"
                                            data-user-role="{{ $user->role }}"
                                            data-user-permissions="{{ json_encode($user->permissions->pluck('name') ?? []) }}"
                                            data-user-email="{{ $user->email ?? $user->Email }}"
                                            title="Edit User">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    @endif
                                    @if(strtolower($user->role) !== 'superadmin' && (Auth::user()->role === 'SuperAdmin' || Auth::user()->can('delete-user')))
                                        <button class="btn btn-danger btn-sm" 
                                            onclick="confirmDelete({{ $user->id }}, '{{ $user->first_name ?? $user->First_Name }} {{ $user->last_name ?? $user->Last_Name }}')"
                                            title="Delete User">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="pagination-info text-muted">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() ?? 0 }} users
                </div>
            </div>

            <!-- Enhanced Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination modern-pagination">
                        {{ $users->links('pagination::bootstrap-4') }}
                    </ul>
                </nav>
            </div>

        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i> Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                {{-- show server-side validation errors inside the modal only --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('usersmanage.store') }}" method="POST" id="addUserForm">
                    @csrf
                    <!-- Hidden field to refresh CSRF token if needed -->
                    <input type="hidden" name="_token" id="csrf-token" value="{{ csrf_token() }}">
                    <!-- Backup method: Add timestamp to prevent caching issues -->
                    <input type="hidden" name="form_timestamp" value="{{ time() }}">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Office</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <input type="text"
                                           class="form-control @error('office') is-invalid @enderror"
                                           name="office"
                                           id="add-office"
                                           value="{{ old('office') }}" required>
                                    @error('office')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <!-- SMIS28-style validation feedback -->
                                <div class="invalid-feedback" id="add-office-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6" id="add-email-col">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input
                                        type="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        name="email"
                                        id="add-email"
                                        value="{{ old('email') }}"
                                        required
                                        pattern="[a-zA-Z0-9._%+-]+@gmail\.com$"
                                        placeholder="example@gmail.com"
                                        autocomplete="email"
                                        inputmode="email"
                                        oninput="document.getElementById('add-email-hidden').value = this.value"
                                    >
                                    <input type="hidden" name="Email" id="add-email-hidden" value="{{ old('email') }}">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <small class="form-text text-muted">Only Gmail accounts are accepted</small>
                                <!-- SMIS28-style validation feedback -->
                                <div class="invalid-feedback" id="add-email-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text"
                                           class="form-control @error('first_name') is-invalid @enderror"
                                           name="first_name"
                                           id="add-firstname"
                                           value="{{ old('first_name') }}" required>
                                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <!-- SMIS28-style validation feedback -->
                                <div class="invalid-feedback" id="add-firstname-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Middle Initial</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text"
                                           class="form-control @error('middle_initial') is-invalid @enderror"
                                           name="middle_initial"
                                           id="add-middle-initial"
                                           value="{{ old('middle_initial') }}"
                                           required maxlength="3"
                                           pattern="^[A-Za-z]{1,2}\.?$"
                                           title="Use 1–2 letters; period optional (e.g., L or L.)">
                                    @error('middle_initial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <small class="form-text text-muted" id="add-mi-tip">
                                    Use 1–2 letters; period optional (e.g., L or L.)
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                    <input type="text"
                                           class="form-control @error('last_name') is-invalid @enderror"
                                           name="last_name"
                                           id="add-lastname"
                                           value="{{ old('last_name') }}" required>
                                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <!-- SMIS28-style validation feedback -->
                                <div class="invalid-feedback" id="add-lastname-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                                    <select class="form-control @error('role') is-invalid @enderror" name="role">
                                        <option value="admin" {{ old('role')=='admin'?'selected':'' }}>Admin</option>
                                        <option value="user" {{ old('role')=='user'?'selected':'' }}>User</option>
                                    </select>
                                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <small class="form-text text-muted">
                                    A temporary password will be generated and sent to the user via email.
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="permissions-container">
                         @php
                            $permissions = [
                                'view-pending', 'view-approved', 'view-released', 'view-cancelled',
                                'add-user', 'edit-user', 'delete-user', 
                                'Add-Supply', 'Edit-Supply', 'Delete-Supply',
                                'Edit-Ris', 'Deny', 'Add-Category', 'Delete-Category', 'Add-Unit', 'Delete-Unit'
                            ];
                            $permissionGroups = [
                                'Requests' => ['view-pending', 'view-approved', 'view-released', 'view-cancelled', 'Edit-Ris', 'Deny'],
                                'Users' => ['add user', 'edit user', 'delete user'],
                                'Supply' => ['Add-Supply', 'Edit-Supply', 'Delete-Supply'],
                                'Categories' => ['Add-Category', 'Delete-Category'],
                                'Units' => ['Add-Unit', 'Delete-Unit'],
                            ];
                         @endphp
                            
                            <div class="row">
                                @foreach($permissionGroups as $group => $perms)
                                    <div class="col-md-4">
                                        <div class="permission-group">
                                            <h6>{{ $group }}</h6>
                                            @foreach($perms as $permission)
                                                @php $id = 'perm-' . str_replace(' ', '-', strtolower($permission)); @endphp
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" id="{{ $id }}"
                                                           @checked(in_array($permission, old('permissions', [])))>
                                                    <label class="form-check-label" for="{{ $id }}">
                                                        {{ ucwords(str_replace('-', ' ', $permission)) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="addUserSubmitBtn">
                            <i class="fas fa-save"></i> Save User
                        </button>
                        <!-- Fallback for when JavaScript fails -->
                        <noscript>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add User (No JS)
                            </button>
                        </noscript>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit mr-2"></i> Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" method="POST" autocomplete="on">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" id="edit-firstname" class="form-control" name="first_name" required>
                                </div>
                                <!-- SMIS28-style validation feedback -->
                                <div class="invalid-feedback" id="edit-firstname-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Middle Initial</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" id="edit-middle-initial" class="form-control" name="middle_initial"
                                           required maxlength="3
                                           pattern="^[A-Za-z]{1,2}\.?$
                                           title="Use 1–2 letters; period optional (e.g., L or L.)">
                                </div>
                                <small class="form-text text-muted" id="edit-mi-tip">
                                    Use 1–2 letters; period optional (e.g., L or L.)
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                    <input type="text" id="edit-lastname" class="form-control" name="last_name" required>
                                </div>
                                <!-- SMIS28-style validation feedback -->
                                <div class="invalid-feedback" id="edit-lastname-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- NEW: Email + Optional Password -->
                    <div class="row">
                        <div class="col-md-6" id="edit-email-col">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" id="edit-email" name="email" class="form-control"
                                           required pattern="[a-zA-Z0-9._%+-]+@gmail\.com$" placeholder="example@gmail.com"
                                           autocomplete="email" inputmode="email">
                                    <!-- NEW: backend compatibility field -->
                                    <input type="hidden" id="edit-email-hidden" name="Email" value="">
                                </div>
                                <small class="form-text text-muted">Only Gmail accounts are accepted</small>
                                <!-- SMIS28-style validation feedback -->
                                <div class="invalid-feedback" id="edit-email-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6" id="edit-password-col">
                            <div class="mb-3 position-relative">
                                <label class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" id="edit-password" name="password" class="form-control"
                                           minlength="8" autocomplete="new-password"
                                           oninput="checkEditPasswordStrength(this.value)">
                                    <button type="button" class="password-toggle" onclick="toggleEditPasswordVisibility(this)">
                                        <i class="fas fa-eye-slash"></i>
                                        <!-- Added: interactive label -->
                                        <span class="password-toggle-label ms-1">Show</span>
                                    </button>
                                </div>
                                <div class="password-strength-container mt-2">
                                    <div class="progress" style="height:5px;">
                                        <div class="progress-bar" id="edit-password-strength-meter" role="progressbar"
                                             style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="password-feedback mt-1 d-block" id="edit-password-feedback">
                                        Enter a new password to change it
                                    </small>
                                    <small class="crack-time mt-1 d-block" id="edit-password-crack-time" style="display:none;"></small>
                                    <!-- NEW helper line -->
                                    <small class="text-muted d-block mt-1">
                                        Tip: Use a mix of BIG caps (A-Z), small caps (a-z), numbers (0-9), and special characters (!@#$%...).
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODIFIED: wrapped role for dynamic locking -->
                    <div class="mb-3" id="edit-role-wrapper">
                        <label class="form-label">Role</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                            <select id="edit-role" name="role" class="form-control">
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="permissions-container">
                            @php
                                $userPermissions = $userPermissions ?? [];
    $permissionGroups = [
        'Requests' => ['view-pending', 'view-approved', 'view-released', 'view-cancelled', 'Edit-Ris', 'Deny'],
        'Users' => ['add user', 'edit user', 'delete user'],
        'Supply' => ['Add-Supply', 'Edit-Supply', 'Delete-Supply'],
        'Categories' => ['Add-Category', 'Delete-Category'],
        'Units' => ['Add-Unit', 'Delete-Unit'],
        
    ];
                            @endphp
                            
                            <div class="row">
                                @foreach($permissionGroups as $group => $perms)
                                    <div class="col-md-4">
                                        <div class="permission-group">
                                            <h6>{{ $group }}</h6>
                                            @foreach($perms as $permission)
                                                @php
                                                    $id = 'edit-perm-' . str_replace(' ', '-', strtolower($permission));
                                                    $checked = in_array(
                                                        $permission,
                                                        old('permissions', $userPermissions)
                                                    ) ? 'checked' : '';
                                                @endphp
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" id="{{ $id }}" {{ $checked }}>
                                                    <label class="form-check-label" for="{{ $id }}">
                                                        {{ ucwords(str_replace('-', ' ', $permission)) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Delete Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger"></i> Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                <p class="fw-bold" id="deleteUserName"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Delete User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Update Modal -->
<div class="modal fade" id="confirmUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Confirm Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to update this user's information?</p>
                <div id="updateDetails" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmUpdateBtn">
                    <i class="fas fa-check"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Add User Modal -->
<div class="modal fade" id="confirmAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Confirm Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to add this new user?</p>
                <div id="addDetails" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddBtn">
                    <i class="fas fa-plus"></i> Add User
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="color: #333; font-weight: 500; font-size: 16px; text-align: center;">Are you sure you want to logout from the system?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmLogoutBtn" onclick="confirmLogout()">
                    <span id="logoutBtnText">Confirm Logout</span>
                    <span id="logoutBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- No Permissions Warning Modal -->
<div class="modal fade" id="noPermissionsWarningModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger"></i> Permission Warning</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-user-lock text-warning" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center fw-bold">Warning: No Permissions Selected!</p>
                <p class="text-center">This user won't be able to access any features in the system. Are you sure you want to continue?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="continueWithNoPermissionsBtn">
                    <i class="fas fa-check"></i> Continue Anyway
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Database Backup Modal -->
<div class="modal fade" id="backupDatabaseModal" tabindex="-1" aria-labelledby="backupDatabaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-download"></i> Backup Database</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-database text-success" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center fw-bold">Create Complete Database Backup</p>
                <p class="text-center">This will download a complete SQL backup of the entire database including all tables, data, and structure.</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> The backup will include all user data, orders, supplies, and system configurations.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('nuclear.backup') }}" method="POST" style="display: inline;">
                    @csrf
                    <!-- CHANGED: type="button" so we can show the themed confirm modal -->
                    <button type="button" class="btn btn-success" id="confirmBackupBtn">
                        <i class="fas fa-download"></i> Download Backup
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- NEW: Themed Confirm Backup Modal -->
<div class="modal fade" id="confirmBackupModal" tabindex="-1" aria-hidden="true" aria-labelledby="confirmBackupModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmBackupModalLabel">
                    <i class="fas fa-shield-alt me-2"></i>Confirm Backup
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-database text-success" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center fw-bold mb-2">Create database backup?</p>
                <p class="text-center mb-0">This may take a few moments depending on database size.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="proceedBackupBtn">
                    <span class="label"><i class="fas fa-download"></i> Start Backup</span>
                    <span class="spinner-border spinner-border-sm d-none" id="proceedBackupSpinner" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Database Restore Modal -->
<div class="modal fade" id="restoreDatabaseModal" tabindex="-1" aria-labelledby="restoreDatabaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-upload"></i> Restore Database</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('nuclear.restore') }}" method="POST" enctype="multipart/form-data" id="restoreForm">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle"></i> CRITICAL WARNING</h6>
                        <p><strong>This action will completely replace the current database!</strong></p>
                        <ul class="mb-0">
                            <li>All current data will be permanently deleted</li>
                            <li>All users, orders, and configurations will be replaced</li>
                            <li>This action cannot be undone</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label for="sql_file" class="form-label fw-bold">
                            <i class="fas fa-file-code"></i> Select SQL Backup File
                        </label>
                        <input type="file" class="form-control" id="sql_file" name="sql_file" accept=".sql,.txt" required>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> Only .sql files are accepted. Maximum file size: 100MB
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmRestore" required>
                            <label class="form-check-label fw-bold text-danger" for="confirmRestore">
                                I understand this will completely replace the current database and cannot be undone
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmation_text" class="form-label fw-bold">
                            Type "RESTORE DATABASE" to confirm:
                        </label>
                        <input type="text" class="form-control" id="confirmation_text" placeholder="Type RESTORE DATABASE" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <!-- CHANGED: type to button; submit happens after themed confirmation -->
                    <button type="button" class="btn btn-danger" id="confirmRestoreBtn" disabled>
                        <i class="fas fa-upload"></i> Restore Database
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- NEW: Final themed confirmation for restore -->
<div class="modal fade" id="finalRestoreConfirmModal" tabindex="-1" aria-labelledby="finalRestoreConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title" id="finalRestoreConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>FINAL WARNING
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fw-bold text-center mb-2">This will completely replace your database.</p>
                <ul>
                    <li>All current data will be permanently deleted</li>
                    <li>All users, orders, and configurations will be replaced</li>
                    <li>This action cannot be undone</li>
                </ul>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-file-code me-1"></i>
                    Selected backup: <strong id="selectedBackupFile">No file selected</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="proceedRestoreBtn">
                    <span class="label"><i class="fas fa-skull-crossbones"></i> Yes, Replace Database</span>
                    <span class="spinner-border spinner-border-sm d-none" id="proceedRestoreSpinner" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Keep Bootstrap first -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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

<!-- If there are validation errors, auto-open the Add User modal -->
@if ($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('addUserModal');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
});
</script>
@endif

<script src="{{ asset('js/index.js') }}" defer></script>
<script src="{{ asset('js/notification-badges.js') }}" defer></script>
@endsection