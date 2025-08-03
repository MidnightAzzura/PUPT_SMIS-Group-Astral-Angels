@extends('layout.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/faq.css') }}">

<body>
    <button class="toggle-sidebar-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar" id="sidebar">
        <img src="{{ asset('Pup/puplogo.png') }}" style="width: 60px; height: 60px; padding: 0; margin: 20px auto;" alt="PUP Logo">
        <h3 style="margin-bottom: 20px;">SMIS</h3>
        <a href="{{ route(Auth::user()->role === 'superadmin' ? 'SuperAdmin.dashboard' : 'Admin.admin') }}" style="margin-bottom: 10px;">
            <i class="fas fa-home" style="margin-right: 8px;"></i> <span>Home</span>
        </a>        <a href="{{ route('usersmanage.index') }}" style="margin-bottom: 10px;">
            <i class="fas fa-users" style="margin-right: 8px;"></i> <span>User Management</span>
        </a>
        <a href="{{ route('notifications.index') }}" style="margin-bottom: 10px;">
            <i class="fas fa-bell" style="margin-right: 8px;"></i> 
            <span>Notifications</span>
            @if(isset($notificationCounts) && $notificationCounts['total_unread'] > 0)
                <span class="notification-badge">{{ $notificationCounts['total_unread'] }}</span>
            @endif
        </a>
        <a href="#" class="dropdown-btn" style="margin-bottom: 10px;">
            <i class="fas fa-box" style="margin-right: 8px;"></i> <span>Orders</span>
            <i class="fa fa-caret-down" style="margin-left: auto;"></i>
        </a>
        <div class="dropdown-container" style="margin: 10px 0; text-align: left; width: 90%; align-self: flex-start; margin-left: 5%;">
            {{-- <a href="{{ route('requests.create') }}" style="margin-bottom: 10px;">
                <i class="fas fa-plus" style="margin-right: 8px;"></i> <span>Create New Request</span>
            </a> --}}
            <a href="{{ route('requests.pending') }}" style="margin-bottom: 10px; text-align: left; justify-content: flex-start;">
                <i class="fas fa-clock" style="margin-right: 8px;"></i> <span>Pending</span>
            </a>
            <a href="{{ route('requests.approved') }}" style="margin-bottom: 10px; text-align: left; justify-content: flex-start;">
                <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> <span>Approved</span>
            </a>
            <a href="{{ route('requests.released') }}" style="margin-bottom: 10px; text-align: left; justify-content: flex-start;">
                <i class="fas fa-truck" style="margin-right: 8px;"></i> <span>Released</span>
            </a>
            <a href="{{ route('requests.disapproved') }}" style="margin-bottom: 10px; text-align: left; justify-content: flex-start;">
                <i class="fas fa-times-circle" style="margin-right: 8px;"></i> <span>Disapproved</span>
            </a>
            {{-- <a href="{{ route('orders.index') }}" style="margin-bottom: 10px;">
                <i class="fas fa-file-alt" style="margin-right: 8px;"></i> <span>Order Request</span>
            </a> --}}
        </div>
        <a href="{{ route('reports') }}" style="margin-bottom: 10px;">
            <i class="fas fa-chart-line" style="margin-right: 8px;"></i> <span>Reports</span>
        </a>
        <a href="{{ route('help.support') }}" style="margin-bottom: 10px;">
            <i class="fas fa-question-circle" style="margin-right: 8px;"></i> <span>Help & Support</span>
        </a>
        <a href="#" class="btn btn-logout" style="margin-bottom: 10px;" onclick="showLogoutModal()">
           <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> <span>Logout</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>

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
                            <h4>Admin Guide</h4>
                            <p>Learn how to manage the system effectively</p>
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
                        <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button">Orders & Management</button>
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
                                            <li><strong>Inventory Managers:</strong> Handle inventory tracking and supply distribution</li>
                                            <li><strong>Department Heads:</strong> Review and approve supply requests</li>
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
                                        How do I manage user accounts?
                                    </button>
                                </h2>
                                <div id="accountOne" class="accordion-collapse collapse show" data-bs-parent="#accordionAccount">
                                    <div class="accordion-body">
                                        <p>To manage user accounts:</p>
                                        <ol>
                                            <li>Navigate to "User Management" in the sidebar</li>
                                            <li>View all registered users and their status</li>
                                            <li>Use the "Add User" button to create new accounts</li>
                                            <li>Click on any user to edit their information or change permissions</li>
                                            <li>Use the activate/deactivate options to control access</li>
                                        </ol>
                                        <p>Remember to assign appropriate roles based on user responsibilities.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accountTwo">
                                        How do I reset user passwords?
                                    </button>
                                </h2>
                                <div id="accountTwo" class="accordion-collapse collapse" data-bs-parent="#accordionAccount">
                                    <div class="accordion-body">
                                        <p>To reset a user's password:</p>
                                        <ol>
                                            <li>Go to "User Management"</li>
                                            <li>Find the user in the list</li>
                                            <li>Click on their profile</li>
                                            <li>Select "Reset Password"</li>
                                            <li>A temporary password will be generated and sent to their email</li>
                                        </ol>
                                        <p>Users will be required to change the temporary password on their next login.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Orders & Management FAQs -->
                    <div class="tab-pane fade" id="orders" role="tabpanel">
                        <div class="accordion" id="accordionOrders">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ordersOne">
                                        How do I review and approve requests?
                                    </button>
                                </h2>
                                <div id="ordersOne" class="accordion-collapse collapse show" data-bs-parent="#accordionOrders">
                                    <div class="accordion-body">
                                        <p>To review and approve supply requests:</p>
                                        <ol>
                                            <li>Navigate to "Orders" → "Pending" to see all pending requests</li>
                                            <li>Click on a request to view detailed information</li>
                                            <li>Review the requested items, quantities, and justification</li>
                                            <li>Check inventory availability</li>
                                            <li>Click "Approve" or "Disapprove" with appropriate comments</li>
                                        </ol>
                                        <p>Approved requests will automatically move to the "Approved" section for processing.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ordersTwo">
                                        How do I manage inventory levels?
                                    </button>
                                </h2>
                                <div id="ordersTwo" class="accordion-collapse collapse" data-bs-parent="#ordersOrders">
                                    <div class="accordion-body">
                                        <p>To manage inventory:</p>
                                        <ol>
                                            <li>Access the inventory management section</li>
                                            <li>Monitor stock levels and set reorder points</li>
                                            <li>Add new items to the inventory catalog</li>
                                            <li>Update quantities when new supplies arrive</li>
                                            <li>Generate reports on inventory usage and trends</li>
                                        </ol>
                                        <p>Regular monitoring helps prevent stockouts and ensures smooth operations.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ordersThree">
                                        How do I generate reports?
                                    </button>
                                </h2>
                                <div id="ordersThree" class="accordion-collapse collapse" data-bs-parent="#accordionOrders">
                                    <div class="accordion-body">
                                        <p>To generate reports:</p>
                                        <ul>
                                            <li>Navigate to the "Reports" section in the sidebar</li>
                                            <li>Select the type of report you need (inventory, requests, usage, etc.)</li>
                                            <li>Set the date range and filter criteria</li>
                                            <li>Choose export format (PDF, Excel, etc.)</li>
                                            <li>Click "Generate Report"</li>
                                        </ul>
                                        <p>Reports help track system usage, inventory trends, and departmental needs.</p>
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
            
            <!-- Admin Guide Section -->
            <div id="guide-section" class="guide-section mb-4">
                <div class="section-header">
                    <h3><i class="fas fa-book me-2"></i>Admin Guide</h3>
                </div>
                <p class="lead">Learn how to manage the SMIS system effectively with our step-by-step guides.</p>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="guide-card">
                            <div class="guide-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="guide-content">
                                <h4>User Management</h4>
                                <p>Learn how to manage user accounts and permissions.</p>
                                <button class="btn btn-sm btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#userGuideModal">
                                    <i class="fas fa-play-circle me-1"></i> View Guide
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="guide-card">
                            <div class="guide-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="guide-content">
                                <h4>Request Approval Process</h4>
                                <p>Understand how to review and approve supply requests.</p>
                                <button class="btn btn-sm btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#approvalGuideModal">
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
                                            <p class="card-text mb-3">Learn how to manage the system effectively with our comprehensive video guides.</p>
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
                                <p>Support Hours: Monday - Friday, 8:00 AM - 5:00 PM</p>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <p>Location: Pup Taguig, Property Custodian Office, Building A</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Admin Guide Modals -->
    <div class="modal fade" id="userGuideModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #800000; color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-users me-2"></i>User Management Guide
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="guide-steps">
                        <div class="guide-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h5>Access User Management</h5>
                                <p>Click on "User Management" in the sidebar to view all registered users.</p>
                            </div>
                        </div>
                        
                        <div class="guide-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h5>Add New Users</h5>
                                <p>Click "Add User" to create new accounts. Fill in required information and assign appropriate roles.</p>
                            </div>
                        </div>
                        
                        <div class="guide-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h5>Manage Existing Users</h5>
                                <p>Click on any user to edit their information, change permissions, or activate/deactivate accounts.</p>
                            </div>
                        </div>
                        
                        <div class="guide-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h5>Monitor User Activity</h5>
                                <p>Review user login history and activity logs to ensure system security.</p>
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
    
    <div class="modal fade" id="approvalGuideModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #800000; color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Request Approval Process
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="guide-steps">
                        <div class="guide-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h5>Review Pending Requests</h5>
                                <p>Navigate to "Orders" → "Pending" to see all requests awaiting approval.</p>
                            </div>
                        </div>
                        
                        <div class="guide-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h5>Evaluate Request Details</h5>
                                <p>Click on each request to review items, quantities, justification, and requester information.</p>
                            </div>
                        </div>
                        
                        <div class="guide-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h5>Check Inventory Availability</h5>
                                <p>Verify that requested items are available in sufficient quantities.</p>
                            </div>
                        </div>
                        
                        <div class="guide-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h5>Make Decision</h5>
                                <p>Click "Approve" or "Disapprove" and provide appropriate comments for your decision.</p>
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
</body>
@endsection