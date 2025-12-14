@extends('layout.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-notification-fix.css') }}">

<div style="padding: 20px;">
    <h1>Notification Badge Test Page</h1>
    
    <h2>Debug Information</h2>
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <p><strong>User Role:</strong> {{ Auth::user()->role ?? 'Not logged in' }}</p>
        <p><strong>NotificationCounts Available:</strong> {{ isset($notificationCounts) ? 'Yes' : 'No' }}</p>
        @if(isset($notificationCounts))
            <p><strong>Notification Data:</strong></p>
            <pre>{{ json_encode($notificationCounts, JSON_PRETTY_PRINT) }}</pre>
        @endif
    </div>
    
    <h2>Test Sidebar</h2>
    <div class="sidebar" style="width: 300px; background: #2c3e50; padding: 20px; border-radius: 5px;">
        <a href="#" style="display: flex; align-items: center; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; background: #34495e; border-radius: 3px; position: relative;">
            <i class="fas fa-bell" style="margin-right: 8px;"></i> 
            <span>Notifications</span>
            @if(isset($notificationCounts) && $notificationCounts['total_unread'] > 0)
                <span class="notification-badge">{{ $notificationCounts['total_unread'] }}</span>
            @endif
        </a>
        
        <a href="#" style="display: flex; align-items: center; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; background: #34495e; border-radius: 3px; position: relative;">
            <i class="fas fa-clock" style="margin-right: 8px;"></i> 
            <span>Pending</span>
            @if(isset($notificationCounts) && $notificationCounts['pending'] > 0)
                <span class="notification-badge">{{ $notificationCounts['pending'] }}</span>
            @endif
        </a>
        
        <a href="#" style="display: flex; align-items: center; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; background: #34495e; border-radius: 3px; position: relative;">
            <i class="fas fa-thumbs-up" style="margin-right: 8px;"></i> 
            <span>Approved</span>
            @if(isset($notificationCounts) && $notificationCounts['approved'] > 0)
                <span class="notification-badge">{{ $notificationCounts['approved'] }}</span>
            @endif
        </a>
        
        <a href="#" style="display: flex; align-items: center; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; background: #34495e; border-radius: 3px; position: relative;">
            <i class="fas fa-truck" style="margin-right: 8px;"></i> 
            <span>Released</span>
            @if(isset($notificationCounts) && $notificationCounts['released'] > 0)
                <span class="notification-badge">{{ $notificationCounts['released'] }}</span>
            @endif
        </a>
        
        <a href="#" style="display: flex; align-items: center; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; background: #34495e; border-radius: 3px; position: relative;">
            <i class="fas fa-times-circle" style="margin-right: 8px;"></i> 
            <span>Denied</span>
            @if(isset($notificationCounts) && $notificationCounts['denied'] > 0)
                <span class="notification-badge">{{ $notificationCounts['denied'] }}</span>
            @endif
        </a>
    </div>
    
    <h2>Static Badge Tests</h2>
    <div style="margin-top: 20px;">
        <p>Sample badges: 
            <span class="notification-badge">5</span>
            <span class="notification-badge badge-warning">3</span>
            <span class="notification-badge badge-success">12</span>
        </p>
    </div>
</div>

@endsection
