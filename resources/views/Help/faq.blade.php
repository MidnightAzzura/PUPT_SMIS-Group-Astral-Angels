@extends('layout.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/faq.css') }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode-switch.css') }}">
<link rel="stylesheet" href="{{ asset('css/user-settings-dropdown.css') }}">

 <button class="toggle-sidebar-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
   <div class="sidebar" id="sidebar" style="text-align: center;">
        <img src="{{ asset('Pup/puplogo.png') }}" style="max-width: 40%; height: auto; padding: 10px; margin: 20px auto;" alt="PUP Logo">
        <h3 style="margin-bottom: 20px;">SMIS</h3>        <a href="{{ route('dashboard.use') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
            <i class="fas fa-home" style="margin-right: 8px;"></i> <span>Home</span>
        </a>
        <a href="{{ route('notifications.index') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
            <i class="fas fa-bell" style="margin-right: 8px;"></i> 
            <span>Notifications</span>
            @if(isset($notificationCounts) && $notificationCounts['total_unread'] > 0)
                <span class="notification-badge">{{ $notificationCounts['total_unread'] }}</span>
            @endif
        </a>
        <a href="#" class="dropdown-btn" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
            <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
            <i class="fa fa-caret-down" style="margin-left: 8px;"></i>
        </a>
        <div class="dropdown-container" style="margin: 10px auto; text-align: center;">
            <a href="{{ route('requests.create') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-plus" style="margin-right: 8px;"></i> <span>Create New Request</span>
            </a>
              <a href="{{ route('requests.pending') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-clock" style="margin-right: 8px;"></i> <span>Pending</span>
            </a>
            <a href="{{ route('requests.approved') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> <span>Approved</span>
            </a>
            <a href="{{ route('requests.released') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-truck" style="margin-right: 8px;"></i> <span>Released</span>
            </a>
            <a href="{{ route('requests.disapproved') }}" style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-times-circle" style="margin-right: 8px;"></i> <span>Disapproved</span>
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

    <div class="main-content p-4">
        <div class="header">
            <h2><i class="fas fa-life-ring me-2"></i>Help & Support Center</h2>
        </div>
        
        <div class="content-container">
            <!-- Quick Navigation Cards -->
            <div class="quick-nav-section mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="quick-nav-card" onclick="scrollToSection('faq-section')">
                            <i class="fas fa-question-circle fa-2x mb-2"></i>
                            <h4>Frequently Asked Questions</h4>
                            <p>Find answers to common questions about SMIS</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="quick-nav-card" onclick="scrollToSection('guide-section')">
                            <i class="fas fa-book fa-2x mb-2"></i>
                            <h4>User Guide</h4>
                            <p>Learn how to use the system effectively</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="quick-nav-card" onclick="scrollToSection('contact-section')">
                            <i class="fas fa-headset fa-2x mb-2"></i>
                            <h4>Contact Support</h4>
                            <p>Get in touch with our support team</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="search-section mb-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="faqSearch" placeholder="Search for help topics...">
                </div>
            </div>

            <!-- FAQ Section -->
            <div id="faq-section" class="faq-section mb-4">
                <div class="section-header">
                    <h3><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h3>
                </div>
                <p class="lead">Welcome to the Help and Support section of the Supply Management Information System (SMIS). Find answers to common questions below.</p>
                
                <!-- FAQ Categories -->
                <ul class="nav nav-tabs mb-3" id="faqTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">General</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button">Account & Access</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button">Orders & Requests</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="technical-tab" data-bs-toggle="tab" data-bs-target="#technical" type="button">Technical Issues</button>
                    </li>
                </ul>
                
                <!-- FAQ Content -->
                <div class="tab-content" id="faqTabContent">
                    <!-- General FAQs -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="accordion" id="accordionGeneral">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                        What is SMIS?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionGeneral">
                                    <div class="accordion-body">
                                        <p>The Supply Management Information System (SMIS) is a comprehensive platform designed to streamline inventory management, supply requests, and distribution processes within the organization.</p>
                                        <p>Key features include:</p>
                                        <ul>
                                            <li>Centralized inventory tracking</li>
                                            <li>Automated request processing</li>
                                            <li>Real-time status updates</li>
                                            <li>Comprehensive reporting tools</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                        Who can access the system?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionGeneral">
                                    <div class="accordion-body">
                                        <p>The system is accessible to:</p>
                                        <ul>
                                            <li><strong>System Administrator:</strong> Manages the overall system, users, and configurations</li>
                                            <li><strong>Offices:</strong> Can submit supply requests and track their status</li>
                                            <li><strong>Student Assistant (Selected):</strong> May assist with processing requests and inventory management</li>
                                            <li><strong>Authorized Personnel:</strong> Staff with specific permissions based on their role</li>
                                        </ul>
                                        <p>Each user type has different permissions and access levels tailored to their responsibilities.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                        What are the system requirements?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionGeneral">
                                    <div class="accordion-body">
                                        <p>SMIS is a web-based application that works on most modern devices. Recommended requirements:</p>
                                        <ul>
                                            <li><strong>Browser:</strong> Chrome (latest), Firefox (latest), Edge (latest)</li>
                                            <li><strong>Internet Connection:</strong> Stable broadband connection</li>
                                            <li><strong>Screen Resolution:</strong> Minimum 1366 x 768</li>
                                        </ul>
                                        <p>For optimal performance, ensure your browser is updated to the latest version.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account & Access FAQs -->
                    <div class="tab-pane fade" id="account" role="tabpanel">
                        <div class="accordion" id="accordionAccount">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accountOne">
                                        How do I reset my password?
                                    </button>
                                </h2>
                                <div id="accountOne" class="accordion-collapse collapse show" data-bs-parent="#accordionAccount">
                                    <div class="accordion-body">
                                        <p>To reset your password:</p>
                                        <ol>
                                            <li>Click on the "Forgot Password" link on the login page</li>
                                            <li>Enter your registered email address</li>
                                            <li>Check your email for a password reset link</li>
                                            <li>Click the link and follow instructions to create a new password</li>
                                        </ol>
                                        <p>If you don't receive the email within a few minutes, check your spam folder or contact support.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accountTwo">
                                        How do I update my profile information?
                                    </button>
                                </h2>
                                <div id="accountTwo" class="accordion-collapse collapse" data-bs-parent="#accordionAccount">
                                    <div class="accordion-body">
                                        <p>To update your profile:</p>
                                        <ol>
                                            <li>Click on your username in the top-right corner</li>
                                            <li>Select "Profile" from the dropdown menu</li>
                                            <li>Click "Edit Profile"</li>
                                            <li>Update your information and click "Save Changes"</li>
                                        </ol>
                                        <p>Note: Some information may require administrator approval to change.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Orders & Requests FAQs -->
                    <div class="tab-pane fade" id="orders" role="tabpanel">
                        <div class="accordion" id="accordionOrders">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ordersOne">
                                        How do I create a new supply request?
                                    </button>
                                </h2>
                                <div id="ordersOne" class="accordion-collapse collapse show" data-bs-parent="#accordionOrders">
                                    <div class="accordion-body">
                                        <p>To create a new supply request:</p>
                                        <ol>
                                            <li>Navigate to "Orders" in the sidebar</li>
                                            <li>Click on "Create New Request"</li>
                                            <li>Fill out the required information</li>
                                            <li>Select the items you need from the inventory</li>
                                            <li>Specify quantities and your purpose for requesting the item</li>
                                            <li>Click "Submit Request"</li>
                                        </ol>
                                        <p>You'll receive a confirmation email with your request reference number.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ordersTwo">
                                        How do I track my request status?
                                    </button>
                                </h2>
                                <div id="ordersTwo" class="accordion-collapse collapse" data-bs-parent="#accordionOrders">
                                    <div class="accordion-body">
                                        <p>To track your request status:</p>
                                        <ol>
                                            <li>Go to "Orders" in the sidebar</li>
                                            <li>Select the appropriate category (Pending, Approved, Released, etc.)</li>
                                            <li>Find your request using the search feature or by browsing the list</li>
                                            <li>Click on the request to view detailed status information</li>
                                        </ol>
                                        <p>You'll also receive email notifications when your request status changes.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ordersThree">
                                        Can I modify or cancel my request?
                                    </button>
                                </h2>
                                <div id="ordersThree" class="accordion-collapse collapse" data-bs-parent="#accordionOrders">
                                    <div class="accordion-body">
                                        <p>Yes, with some limitations:</p>
                                        <ul>
                                            <li><strong>Pending requests:</strong> Can be modified or cancelled directly by you</li>
                                            <li><strong>Approved requests:</strong> Cannot be modified, but may be cancelled with justification</li>
                                            <li><strong>Released requests:</strong> Cannot be modified or cancelled</li>
                                        </ul>
                                        <p>To modify or cancel a request, open the request details and look for the "Edit" or "Cancel" button if available.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Technical Issues FAQs -->
                    <div class="tab-pane fade" id="technical" role="tabpanel">
                        <div class="accordion" id="accordionTechnical">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#technicalOne">
                                        Common Issues & Solutions
                                    </button>
                                </h2>
                                <div id="technicalOne" class="accordion-collapse collapse show" data-bs-parent="#accordionTechnical">
                                    <div class="accordion-body">
                                        <p><strong>Login Issues:</strong></p>
                                        <ul>
                                            <li>Verify your username and password</li>
                                            <li>Clear browser cache and cookies</li>
                                            <li>Use the "Forgot Password" feature to reset</li>
                                            <li>Ensure Caps Lock is off</li>
                                            <li>Contact system administrator if issues persist</li>
                                        </ul>
                                        
                                        <p><strong>System Performance:</strong></p>
                                        <ul>
                                            <li>Check your internet connection</li>
                                            <li>Clear browser cache (press Ctrl+F5)</li>
                                            <li>Use recommended browsers (Chrome, Firefox)</li>
                                            <li>Close unnecessary browser tabs</li>
                                            <li>Try disabling browser extensions temporarily</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#technicalTwo">
                                        Browser Compatibility
                                    </button>
                                </h2>
                                <div id="technicalTwo" class="accordion-collapse collapse" data-bs-parent="#accordionTechnical">
                                    <div class="accordion-body">
                                        <p>SMIS works best with the following browsers:</p>
                                        <ul>
                                            <li>Google Chrome (Version 90+)</li>
                                            <li>Mozilla Firefox (Version 88+)</li>
                                            <li>Microsoft Edge (Version 90+)</li>
                                            <li>Safari (Version 14+)</li>
                                        </ul>
                                        <p>If you're experiencing issues, try updating your browser to the latest version.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- User Guide Section -->
            <div id="guide-section" class="guide-section mb-4">
                <div class="section-header">
                    <h3><i class="fas fa-book me-2"></i>User Guide</h3>
                </div>
                <p class="lead">Learn how to use the SMIS system effectively with our step-by-step guides.</p>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="guide-card">
                            <div class="guide-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="guide-content">
                                <h4>How to Create a Supply Request</h4>
                                <p>Learn the process of creating and submitting supply requests.</p>
                                <button class="btn btn-sm btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#requestGuideModal">
                                    <i class="fas fa-play-circle me-1"></i> View Guide
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="guide-card">
                            <div class="guide-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="guide-content">
                                <h4>Tracking Your Requests</h4>
                                <p>Monitor the status of your submitted requests.</p>
                                <button class="btn btn-sm btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#trackingGuideModal">
                                    <i class="fas fa-play-circle me-1"></i> View Guide
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="mt-4">
                    <h4 class="mb-3">Video Tutorials</h4>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                                <div class="row g-0">
                                    <div class="col-md-3" style="background-color: #800000;">
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <i class="fab fa-youtube fa-4x text-white"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="card-body p-4">
                                            <h4 class="card-title fw-bold mb-2">Video Tutorials</h4>
                                            <p class="card-text mb-3">Learn how to use the system effectively with our comprehensive video guides.</p>
                                            <a href="https://www.youtube.com/watch?v=f3nRftSI1po&list=PLTxw4Mbircegd7TgrYTCtr_DuFAqX-Vi7&pp=gAQB" 
                                               target="_blank" 
                                               class="btn btn-danger rounded-pill px-4">
                                                <i class="fab fa-youtube me-2"></i> View Playlist
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Contact Support Section -->
            <div id="contact-section" class="contact-section mb-4">
                <div class="section-header">
                    <h3><i class="fas fa-headset me-2"></i>Contact Support</h3>
                </div>
                <p class="lead">Need additional help? Our support team is ready to assist you.</p>
                
                <div class="row">
                    <div class="col-12">
                        <div class="contact-methods">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <p>
                                    Email:
                                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=smispupttaguig@gmail.com&su=Support%20Request&body=Hello%20SMIS%20Support%2C%0A%0A"
                                       target="_blank"
                                       rel="noopener noreferrer">
                                        smispupttaguig@gmail.com
                                    </a>
                                </p>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <p>Phone: (02) 8123-4567</p>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-clock"></i>
                                <p>Support Hours: Monday - Friday, 8:00 AM - 3:00 PM</p>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <p>Location: Supply Management Office, Ground Floor, Main Building</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Guide Modals -->
    <div class="modal fade" id="requestGuideModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #800000; color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-shopping-cart me-2"></i>How to Create a Supply Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="guide-steps">
                        <div class="guide-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h5>Navigate to Create Request</h5>
                                <p>Click on "Orders" in the sidebar, then select "Create New Request" from the dropdown menu.</p>
                                <img src="{{ asset('images/guides/create-request-1.jpg') }}" alt="Navigate to Create Request" class="img-fluid rounded">
                            </div>
                        </div>
                        
                        <div class="guide-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h5>Fill in Request Details</h5>
                                <p>Complete all required fields in the request form, including purpose and department information.</p>
                            </div>
                        </div>
                        
                        <div class="guide-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h5>Select Items</h5>
                                <p>Browse or search for items you need, and add them to your request with the required quantities.</p>
                            </div>
                        </div>
                        
                        <div class="guide-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h5>Review and Submit</h5>
                                <p>Review your request details carefully, then click "Submit Request" to finalize.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="trackingGuideModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #800000; color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-search me-2"></i>Tracking Your Requests
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="guide-steps">
                        <div class="guide-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h5>Access Orders Menu</h5>
                                <p>Click on "Orders" in the sidebar to view the dropdown menu.</p>
                            </div>
                        </div>
                        
                        <div class="guide-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h5>Select Request Status</h5>
                                <p>Choose from "Pending," "Approved," "Released," or "Disapproved" to filter requests by status.</p>
                            </div>
                        </div>
                        
                        <div class="guide-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h5>Locate Your Request</h5>
                                <p>Use the search function or browse the list to find your specific request.</p>
                            </div>
                        </div>
                        
                        <div class="guide-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h5>View Details</h5>
                                <p>Click on the request to view complete details, including status history and estimated completion time.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        function toggleSidebar() {
            var sidebar = document.querySelector('.sidebar');
            var mainContent = document.querySelector('.main-content');
            var logo = sidebar.querySelector('img');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
            
            if (sidebar.classList.contains('collapsed')) {
                logo.style.display = 'none';
                localStorage.setItem('sidebarState', 'collapsed');
            } else {
                logo.style.display = 'block';
                localStorage.setItem('sidebarState', 'expanded');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            var sidebarState = localStorage.getItem('sidebarState');
            if (sidebarState === 'collapsed') {
                var sidebar = document.querySelector('.sidebar');
                var mainContent = document.querySelector('.main-content');
                var logo = sidebar.querySelector('img');
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
                logo.style.display = 'none';
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
        
        // FAQ search functionality
        document.getElementById('faqSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const accordionItems = document.querySelectorAll('.accordion-item');
            
            accordionItems.forEach(item => {
                const question = item.querySelector('.accordion-button').textContent.toLowerCase();
                const answer = item.querySelector('.accordion-body').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        // Smooth scrolling to sections
        function scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            window.scrollTo({
                top: section.offsetTop - 20,
                behavior: 'smooth'
            });
        }
    </script>

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
</body>
@endsection