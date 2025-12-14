@extends('layout.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/order.css') }}">
<link rel="stylesheet" href="{{ asset('css/universal-sidebar-scrollbar.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Include the bulletproof JavaScript -->
<script src="{{ asset('js/sidebar-fix.js') }}" defer></script>

<body>
    <button class="toggle-sidebar-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- UNIVERSAL SIDEBAR STRUCTURE - BULLETPROOF VERSION -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <img src="{{ asset('Pup/puplogo.png') }}" style="max-width: 40%; height: auto; padding: 10px; margin: 20px auto;" alt="PUP Logo">
            <h3 style="margin-bottom: 20px;">SMIS</h3>
            
            <a href="{{ route(Auth::user()->role === 'super admin' ? 'SuperAdmin.dashboard' : 'Admin.admin') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-home" style="margin-right: 8px;"></i> <span>Home</span>
            </a>
            
            @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'SuperAdmin')
            <a href="{{ route('usersmanage.index') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-users" style="margin-right: 8px;"></i> <span>User Management</span>
            </a>
            @endif
            
            <a href="{{ route('notifications.index') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-bell" style="margin-right: 8px;"></i> 
                <span>Notifications</span>
                @if(isset($notificationCounts) && $notificationCounts['total_unread'] > 0)
                    <span class="notification-badge">{{ $notificationCounts['total_unread'] }}</span>
                @endif
            </a>
            
            <!-- BULLETPROOF DROPDOWN BUTTON -->
            <a href="#" class="dropdown-btn" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;" role="button" aria-expanded="false">
                <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
                <i class="fa fa-caret-down" style="margin-left: 8px;"></i>
            </a>
            
            <!-- BULLETPROOF DROPDOWN CONTAINER -->
            <div class="dropdown-container" style="margin: 10px auto; text-align: center;" aria-hidden="true">
                @if(Auth::user()->role === 'User')
                <a href="{{ route('requests.create') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i> <span>Create New Request</span>
                </a>
                @endif
                @can('view-pending')
                <a href="{{ route('requests.pending') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-clock" style="margin-right: 8px;"></i> <span>Pending</span>
                    @if(isset($notificationCounts) && $notificationCounts['pending'] > 0)
                        <span class="notification-badge warning">{{ $notificationCounts['pending'] }}</span>
                    @endif
                </a>
                @endcan
                @can('view-approved')
                <a href="{{ route('requests.approved') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> <span>Approved</span>
                    @if(isset($notificationCounts) && $notificationCounts['approved'] > 0)
                        <span class="notification-badge success">{{ $notificationCounts['approved'] }}</span>
                    @endif
                </a>
                @endcan
                @can('view-released')
                <a href="{{ route('requests.released') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-truck" style="margin-right: 8px;"></i> <span>Released</span>
                    @if(isset($notificationCounts) && $notificationCounts['released'] > 0)
                        <span class="notification-badge purple">{{ $notificationCounts['released'] }}</span>
                    @endif
                </a>
                @endcan
                @can('view-cancelled')
                <a href="{{ route('requests.disapproved') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-times-circle" style="margin-right: 8px;"></i> <span>Disapproved</span>
                    @if(isset($notificationCounts) && $notificationCounts['denied'] > 0)
                        <span class="notification-badge danger">{{ $notificationCounts['denied'] }}</span>
                    @endif
                </a>
                @endcan
            </div>
            
            <a href="{{ route('reports') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-chart-line" style="margin-right: 8px;"></i> <span>Reports</span>
            </a>
            
            <a href="{{ route('AdHelp.AdFaq') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-question-circle" style="margin-right: 8px;"></i> <span>Help & Support</span>
            </a>

            <!-- NEW: Rate / Questionnaire Button (replace href with your Google Form link) -->
            <a id="rateSystemBtn" href="https://forms.gle/12SD5ZjPTXbsgqzC7" target="_blank" rel="noopener" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px; background: rgba(255,255,255,0.08); border-radius:5px;">
                <i class="fas fa-star-half-alt" style="margin-right: 8px;"></i> <span>Rate Our System</span>
            </a>
            
            <a href="{{ route('logout') }}" class="btn btn-logout" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
               <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> <span>Logout</span>
        </a>
     </a>
     <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
         @csrf
     </form>
    </div>
    <div class="main-content p-4">
        <div class="header">
            <h2>Create New Request</h2>
        </div>

        <div class="content-container mt-4">
            <form action="{{ route('orders.handleRequest') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="stockNumber" class="form-label">Stock Number</label>
                    <input type="text" class="form-control" id="stockNumber" name="stock_number" placeholder="Enter Stock Number" required>
                </div>
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter Quantity" required>
                </div>
                <div class="mb-3">
                    <label for="purpose" class="form-label">Purpose</label>
                    <textarea class="form-control" id="purpose" name="purpose" rows="3" placeholder="Enter Purpose" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Request</button>
                <button type="reset" class="btn btn-secondary">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Restore sidebar state from localStorage
            var sidebar = document.querySelector('.sidebar');
            var storedState = localStorage.getItem('sidebarState');
            if (storedState === 'collapsed') {
                sidebar.classList.add('collapsed');
            }

            // Toggle submenu visibility
            document.querySelectorAll('.sidebar-section-toggle').forEach(function(toggle) {
                toggle.addEventListener('click', function() {
                    this.nextElementSibling.classList.toggle('visible');
                });
            });

            // Dropdown functionality
            var dropdown = document.querySelectorAll('.dropdown-btn');
            dropdown.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    this.classList.toggle('active');
                    var dropdownContent = this.nextElementSibling;
                    if (dropdownContent) {
                        dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
                    }
                });
            });
        });

        function toggleSidebar() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
            if (sidebar.classList.contains('collapsed')) {
                localStorage.setItem('sidebarState', 'collapsed');
            } else {
                localStorage.setItem('sidebarState', 'expanded');
            }
        }
    </script>
</body>
@endsection
