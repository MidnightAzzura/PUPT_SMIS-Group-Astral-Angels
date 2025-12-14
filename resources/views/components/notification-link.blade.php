@props(['type' => 'default', 'count' => 0, 'route' => '', 'icon' => 'fas fa-bell', 'text' => 'Notifications'])

@php
    $badgeClass = match($type) {
        'approved' => 'notification-badge success',
        'denied', 'disapproved' => 'notification-badge danger', 
        'pending' => 'notification-badge warning',
        'released' => 'notification-badge purple',
        'notifications' => 'notification-badge info',
        default => 'notification-badge'
    };
@endphp

<a href="{{ $route }}" 
   style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px; position: relative;"
   @if(in_array($type, ['approved', 'disapproved', 'denied']))
   onclick="clearPageNotifications('{{ $type === 'denied' ? 'disapproved' : $type }}')"
   @endif>
    <i class="{{ $icon }}" style="margin-right: 8px;"></i>
    <span>{{ $text }}</span>
    @if($count > 0)
        <span class="{{ $badgeClass }}" data-type="{{ $type }}">{{ $count }}</span>
    @endif
</a>
