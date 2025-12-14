

<?php $__env->startSection('content'); ?>

<link rel="stylesheet" href="<?php echo e(asset('css/createreq.css')); ?>?v=<?php echo e(time()); ?>&bust=<?php echo e(rand()); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/notification-badges.css')); ?>?v=<?php echo e(time()); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/universal-sidebar-scrollbar.css')); ?>?v=<?php echo e(time()); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dark-mode.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dark-mode-switch.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/user-settings-dropdown.css')); ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Include the bulletproof JavaScript -->
<script src="<?php echo e(asset('js/sidebar-fix.js')); ?>?v=<?php echo e(time()); ?>" defer></script>

<body>
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
            )); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-home" style="margin-right: 8px;"></i> <span>Home</span>
            </a>
            
            <?php if(Auth::user()->role === 'Admin' || Auth::user()->role === 'SuperAdmin'): ?>
            <a href="<?php echo e(route('usersmanage.index')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-users" style="margin-right: 8px;"></i> <span>User Management</span>
            </a>
            <?php endif; ?>
            
            <a href="<?php echo e(route('notifications.index')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-bell" style="margin-right: 8px;"></i> 
                <span>Notifications</span>
                <?php if(isset($notificationCounts) && $notificationCounts['total_unread'] > 0): ?>
                    <span class="notification-badge"><?php echo e($notificationCounts['total_unread']); ?></span>
                <?php endif; ?>
            </a>
            
            <!-- BULLETPROOF DROPDOWN BUTTON -->
            <a href="#" class="dropdown-btn" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;" role="button" aria-expanded="false">
                <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
                <i class="fa fa-caret-down" style="margin-left: 8px;"></i>
            </a>

            <!-- BULLETPROOF DROPDOWN CONTAINER -->
            <div class="dropdown-container" style="margin: 10px auto; text-align: center;" aria-hidden="true">
                <?php if(Auth::user()->role === 'User'): ?>
                <a href="<?php echo e(route('requests.create')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px; background-color: rgba(255, 255, 255, 0.1);">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i> <span>Create New Request</span>
                </a>
                <?php endif; ?>
                <a href="<?php echo e(route('requests.pending')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-clock" style="margin-right: 8px;"></i> 
                    <span>Pending</span>
                    <?php if(isset($notificationCounts) && $notificationCounts['pending'] > 0): ?>
                        <span class="notification-badge warning"><?php echo e($notificationCounts['pending']); ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo e(route('requests.approved')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;" onclick="clearPageNotifications('approved')">
                    <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> 
                    <span>Approved</span>
                    <?php if(isset($notificationCounts) && $notificationCounts['approved'] > 0): ?>
                        <span class="notification-badge success"><?php echo e($notificationCounts['approved']); ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo e(route('requests.released')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-truck" style="margin-right: 8px;"></i> 
                    <span>Released</span>
                    <?php if(isset($notificationCounts) && $notificationCounts['released'] > 0): ?>
                        <span class="notification-badge purple"><?php echo e($notificationCounts['released']); ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo e(route('requests.disapproved')); ?>" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;" onclick="clearPageNotifications('disapproved')">
                    <i class="fas fa-times-circle" style="margin-right: 8px;"></i> 
                    <span>Disapproved</span>
                    <?php if(isset($notificationCounts) && $notificationCounts['denied'] > 0): ?>
                        <span class="notification-badge danger"><?php echo e($notificationCounts['denied']); ?></span>
                    <?php endif; ?>
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
                    console.log("User button clicked");
                    
                    this.classList.add('btn-clicked');
                    setTimeout(() => this.classList.remove('btn-clicked'), 300);
                    
                    if (userMenu.style.display === 'block') {
                        userMenu.style.display = 'none';
                        userMenu.classList.remove('show');
                    } else {
                        userMenu.style.display = 'block';
                        userMenu.classList.add('show');
                    }
                };

                document.addEventListener('click', function(e) {
                    if (!userButton.contains(e.target) && !userMenu.contains(e.target)) {
                        userMenu.classList.remove('show');
                        userMenu.style.display = 'none';
                    }
                });
            }
        });
    </script>

    <div class="main-content">
        <div class="header">
            <h2><i class="fas fa-file-alt"></i> Create New Request</h2>
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p class="mb-0"><?php echo e($error); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if(session('success')): ?>
            <div class="alert alert-success" id="successAlert">
                <i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="request-form-container">
            <form id="requestForm" action="<?php echo e(route('requests.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <!-- Added hidden action_type to indicate submit vs cancel -->
                <input type="hidden" name="action_type" id="action_type" value="">

                <div class="mb-4">
                    <label for="stockNumber" class="form-label">Create a request</label>
                    <div class="input-group">
                        <button type="button" class="btn btn-maroon" data-bs-toggle="modal" data-bs-target="#searchModal">
                            <i class="fas fa-plus"></i> Add Items
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="table-container">
                        <h4><i class="fas fa-list"></i> Request List</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Stock Number</th>
                                        <th>Information</th>
                                        <th>Category</th>
                                        <th>Unit of Measure</th>
                                        <th>Quantity</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Placeholder row -->
                                    <tr class="nothing-follows">
                                        <td colspan="6">Nothing Follows</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="purpose" class="form-label">Purpose</label>
                    <textarea class="form-control" id="purpose" name="purpose" rows="3" placeholder="Enter the purpose of your request" required></textarea>
                </div>
                
                <button type="button" class="btn btn-primary" id="submitButton">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </form>
        </div>
    </div>

    <!-- Supply Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalLabel"><i class="fas fa-boxes"></i> Supply Inventory</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><i class="fas fa-info-circle"></i> Select an item from the list below</p>
                    <p><i class="fas fa-search"></i> Use the search box to find items quickly</p>
                    <p><i class="fas fa-mouse-pointer"></i> Click the select button to add an item to your request</p>
                    
                    <div class="mb-3">
                        <?php
                            // Build a list of unique categories (fall back to property names)
                            // Normalize by trimming and deduplicate/sort case-insensitively
                            $categories = $supply
                                ->map(function($i){
                                    return $i->Category ?? $i->category ?? null;
                                })
                                ->filter()
                                ->map(function($c){ return trim((string) $c); })
                                ->unique(function($c){ return strtolower($c); })
                                ->sortBy(function($c){ return strtolower($c); })
                                ->values();
                        ?>

                        <div class="d-flex gap-2 mb-2" style="align-items:center;">
                            <label for="categoryFilter" class="mb-0" style="min-width:75px;"><i class="fas fa-filter"></i> Category</label>
                            <select id="categoryFilter" class="form-select" style="max-width:320px;">
                                <option value="all">All Categories</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cat); ?>"><?php echo e($cat); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <input type="text" id="searchInput" class="search-input" placeholder="Search by stock number, description or unit...">
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Stock No.</th>
                                    <th>Details</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    <th>Availability</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $supply; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    // low-stock threshold (adjust as needed)
                                    $lowThreshold = 5;
                                    $isAvailable = $item->Quantity > 0;
                                    $isLow = $isAvailable && $item->Quantity <= $lowThreshold;
                                    $category = $item->Category ?? $item->category ?? 'Uncategorized';
                                ?>
                                <tr class="supply-item" data-quantity="<?php echo e($item->Quantity); ?>" data-low="<?php echo e($isLow ? '1' : '0'); ?>" data-category="<?php echo e($category); ?>">
                                    <td><?php echo e($item->StockNumber); ?></td>
                                    <td><?php echo e($item->ItemDescription); ?></td>
                                    <td><?php echo e($category); ?></td>
                                    <td><?php echo e($item->Unit); ?></td>
                                    <td>
                                        <?php if(!$isAvailable): ?>
                                            <span class="status-badge unavailable">
                                                <i class="fas fa-times-circle"></i> Out of Stock
                                            </span>
                                        <?php elseif($isLow): ?>
                                            <span class="status-badge low" title="Low stock">
                                                <i class="fas fa-exclamation-triangle"></i> Low Stock
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge available">
                                                <i class="fas fa-check-circle"></i> Available
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-primary btn-sm"
                                                title="<?php echo e(!$isAvailable ? 'Out of stock' : ($isLow ? 'Low stock — select with caution' : 'Select item')); ?>"
                                                onclick="selectItem('<?php echo e($item->StockNumber); ?>', '<?php echo e(addslashes($item->ItemDescription)); ?>', '<?php echo e($item->Unit); ?>', <?php echo e($item->Quantity); ?>, '<?php echo e(addslashes($category)); ?>')"
                                                <?php echo e(!$isAvailable ? 'disabled' : ''); ?>>
                                            <i class="fas fa-plus-circle"></i> Select
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION CONTROLS: will be filled by JS -->
                    <nav aria-label="Supply pagination" class="mt-2">
                        <ul id="supplyPagination" class="pagination justify-content-center mb-0" style="user-select:none;">
                            <!-- JS will render prev / page numbers / next here -->
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
                <div class="modal-header">
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

    <!-- Confirmation Modal for Submit / Cancel -->
    <div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmSubmitModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Request Action</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Please confirm your action:</p>
                    <ul>
                        <li><strong>Confirm Submit</strong> — submit the request as usual.</li>
                        <li><strong>Cancel Request</strong> — mark this request as cancelled/disapproved so it will be logged on the disapproved list.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <!-- Cancel Request will set action_type='cancel' and submit the form -->
                    <button type="button" class="btn btn-danger" id="cancelRequestBtn">
                        <i class="fas fa-times-circle"></i> Cancel Request
                    </button>
                    <!-- Confirm Submit will set action_type='submit' and submit the form -->
                    <button type="button" class="btn btn-success" id="confirmSubmitBtn">
                        <i class="fas fa-check-circle"></i> Confirm Submit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel result modal: shown after AJAX cancel completes -->
    <div class="modal fade" id="cancelResultModal" tabindex="-1" aria-labelledby="cancelResultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelResultModalLabel"><i class="fas fa-info-circle me-2"></i>Request Cancelled</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>The request has been cancelled and logged into the Disapproved list.</p>
                    <p>You can view it on the Disapproved page using the link below.</p>
                </div>
                <div class="modal-footer">
                    <a id="gotoDisapprovedLink" href="<?php echo e(route('requests.disapproved')); ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> View Disapproved Requests
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Toast Container -->
    <div id="customToast"></div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // provide route for external JS to call (keeps blade templating in view)
        window.appData = window.appData || {};
        window.appData.notificationsCountsUrl = "<?php echo e(route('notifications.counts')); ?>";
        // expose disapproved page url for cancel result link
        window.appData.disapprovedUrl = "<?php echo e(route('requests.disapproved')); ?>";
    </script>

    <script src="<?php echo e(asset('js/createreq.js')); ?>" defer></script>
    <script src="<?php echo e(asset('js/notification-badges.js')); ?>" defer></script>

    <!-- NEW: hydrate categoryFilter from localStorage to reflect admin changes (customCategories) -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        try {
            const stored = JSON.parse(localStorage.getItem('customCategories') || '[]');
            const sel = document.getElementById('categoryFilter');
            if (!sel) return;
            
            // Merge existing options and stored categories into a case-insensitive map
            const map = new Map();
            Array.from(sel.options).forEach(opt => {
                const val = (opt.value || '').trim();
                if (!val) return;
                map.set(val.toLowerCase(), val);
            });
            (Array.isArray(stored) ? stored : []).forEach(c => {
                if (!c) return;
                const trimmed = String(c).trim();
                if (!trimmed) return;
                if (!map.has(trimmed.toLowerCase())) map.set(trimmed.toLowerCase(), trimmed);
            });
            
            // Build sorted list (keep 'all' first)
            const entries = Array.from(map.values()).filter(v => v.toLowerCase() !== 'all');
            entries.sort((a,b) => a.toLowerCase().localeCompare(b.toLowerCase()));
            
            // Rebuild select options: 'All Categories' first then sorted entries
            sel.innerHTML = '';
            const allOpt = document.createElement('option');
            allOpt.value = 'all';
            allOpt.text = 'All Categories';
            sel.appendChild(allOpt);
            entries.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v;
                opt.text = v;
                sel.appendChild(opt);
            });
            
            // Restore last selection if present
            const last = localStorage.getItem('lastSelectedCategory');
            if (last && Array.from(sel.options).some(o => (o.value||'') === last)) sel.value = last;
            
            // Persist selection when user picks a category
            sel.addEventListener('change', function () {
                try { localStorage.setItem('lastSelectedCategory', this.value || 'all'); } catch(e) {}
            });
        } catch (e) {
            console.warn('Category hydration failed', e);
        }
    });
    </script>

    </body>
    </html>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u741082352/domains/smis.ojt-ims-bsit.net/public_html/resources/views/requests/create.blade.php ENDPATH**/ ?>