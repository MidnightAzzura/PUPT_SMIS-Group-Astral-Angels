    {{-- Notification Table Partial View - Used for AJAX refreshes --}}

@if ($notifications->isEmpty())
    <div class="empty-notifications">
        <div class="text-center py-5">
            <i class="fas fa-bell-slash fa-4x mb-3 text-muted"></i>
            <h3>No notifications found</h3>
            <p class="text-muted">You'll see notifications here when there are updates on your requests.</p>
        </div>
    </div>
@else    <div class="table-responsive">
        <table class="table notification-table" data-last-refresh="{{ now()->format('Y-m-d H:i:s') }}">
            <thead>
                <tr>
                    <th width="3%">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                        </div>
                    </th>
                    <th width="12%">Status</th>
                    <th width="35%">Message</th>
                    <th width="15%">Date/Time</th>
                    <th width="10%">Office</th>
                    <th width="5%">User ID</th>
                    <th width="20%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Filter out empty notifications and prevent duplicates
                    $seenNotifications = [];
                @endphp
                
                @foreach ($notifications as $notification)
                    @php
                        // Skip notifications with empty or invalid messages
                        if (empty(trim($notification->message)) || 
                            $notification->action === 'notification' && empty(trim($notification->message))) {
                            continue;
                        }
                        
                        // Create a signature to prevent duplicates
                        $notifSignature = md5($notification->message . $notification->created_at->format('Y-m-d H:i') . $notification->action);
                        
                        // Skip if we've seen this exact notification already
                        if (in_array($notifSignature, $seenNotifications)) {
                            continue;
                        }
                        $seenNotifications[] = $notifSignature;
                    @endphp
                    
                    <tr class="notification-item notification-row {{ $notification->is_read ? 'table-light' : 'table-warning' }}" data-notification-id="{{ $notification->notification_id }}">
                        <td>
                            <div class="form-check">
                                <input class="form-check-input notification-checkbox" type="checkbox" value="{{ $notification->notification_id }}">
                            </div>
                        </td>
                        <td>
                            @if ($notification->is_read)
                                <span class="badge bg-secondary"><i class="fas fa-check"></i> Read</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle"></i> Unread</span>
                            @endif
                            
                            @if ($notification->action == 'request_approved')
                                <span class="badge bg-success mt-1"><i class="fas fa-thumbs-up"></i> Approved</span>
                            @elseif ($notification->action == 'request_denied')
                                <span class="badge bg-danger mt-1"><i class="fas fa-thumbs-down"></i> Denied</span>
                            @endif
                        </td>
                        <td class="notification-message-cell">
                            @php
                                // Use dedicated NotificationCleaner helper for ultra-reliable cleaning
                                $isBatchNotification = \App\Helpers\NotificationCleaner::isBatchNotification($notification->message);
                                $cleanMessage = \App\Helpers\NotificationCleaner::cleanBatchMetadata($notification->message);
                            @endphp
                            {{ \Illuminate\Support\Str::limit($cleanMessage, 100) }}
                            @if (strlen($cleanMessage) > 100)
                                <a href="#" onclick="showFullMessage('{{ $notification->notification_id }}'); return false;" class="ms-1 text-primary"><i class="fas fa-expand-alt"></i></a>
                            @endif
                        </td>
                        <td class="date-time-cell">
                            {{ $notification->created_at->format('M d, Y') }}<br>
                            <small>{{ $notification->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $notification->office }}</span>
                        </td>
                        <td>{{ $notification->user_id }}</td>
                        <td>
                            <div class="btn-group">
                                {{-- View batch details button for batch notifications - using detected batch status --}}
                                @if ($isBatchNotification ?? false)
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewBatchDetails('{{ $notification->notification_id }}', '{{ $notification->request_id }}', '{{ $notification->action }}')">
                                        <i class="fas fa-eye"></i> View Batch
                                    </button>
                                @endif
                                @if (!$notification->is_read)
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="markAsRead('{{ $notification->notification_id }}')">
                                        <i class="fas fa-check"></i> Mark Read
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteNotification('{{ $notification->notification_id }}')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr id="full-message-{{ $notification->notification_id }}" class="d-none">
                        <td colspan="7" class="p-3 bg-light">
                            <div class="p-2">
                                <strong>Full message:</strong><br>
                                @php
                                    // Use dedicated NotificationCleaner helper for full message
                                    $cleanFullMessage = \App\Helpers\NotificationCleaner::cleanBatchMetadata($notification->message);
                                @endphp
                                {{ $cleanFullMessage }}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="showFullMessage('{{ $notification->notification_id }}')">
                                <i class="fas fa-compress-alt"></i> Show Less
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        function showFullMessage(notificationId) {
            const fullMessageEl = document.getElementById(`full-message-${notificationId}`);
            const messageCell = fullMessageEl.previousElementSibling;
            
            if (fullMessageEl.classList.contains('d-none')) {
                fullMessageEl.classList.remove('d-none');
                messageCell.classList.add('d-none');
            } else {
                fullMessageEl.classList.add('d-none');
                messageCell.classList.remove('d-none');
            }
        }

        function markAsRead(notificationId) {
            // AJAX call to mark notification as read
            fetch('/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ notification_id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI to show notification is read
                    const notificationRow = document.querySelector(`.notification-row[data-notification-id="${notificationId}"]`);
                    if (notificationRow) {
                        notificationRow.classList.remove('table-warning');
                        notificationRow.classList.add('table-light');
                        
                        // Update the badge
                        const badge = notificationRow.querySelector('td:nth-child(2) span:first-child');
                        if (badge) {
                            badge.className = 'badge bg-secondary';
                            badge.innerHTML = '<i class="fas fa-check"></i> Read';
                        }
                        
                        // Remove the "Mark Read" button
                        const markReadBtn = notificationRow.querySelector('.btn-outline-success');
                        if (markReadBtn) {
                            markReadBtn.remove();
                        }
                    }
                }
            })
            .catch(error => console.error('Error marking notification as read:', error));
        }

        function deleteNotification(notificationId) {
            if (!confirm('Are you sure you want to delete this notification?')) {
                return;
            }
            
            // AJAX call to delete notification
            fetch(`/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove notification row from table
                    const notificationRow = document.querySelector(`.notification-row[data-notification-id="${notificationId}"]`);
                    const fullMessageRow = document.getElementById(`full-message-${notificationId}`);
                    
                    if (notificationRow) {
                        notificationRow.remove();
                    }
                    
                    if (fullMessageRow) {
                        fullMessageRow.remove();
                    }
                    
                    // If no notifications left, show empty state
                    if (document.querySelectorAll('.notification-row').length === 0) {
                        document.querySelector('.table-responsive').innerHTML = `
                            <div class="empty-notifications">
                                <div class="text-center py-5">
                                    <i class="fas fa-bell-slash fa-4x mb-3 text-muted"></i>
                                    <h3>No notifications found</h3>
                                    <p class="text-muted">You'll see notifications here when there are updates on your requests.</p>
                                </div>
                            </div>
                        `;
                    }
                }
            })
            .catch(error => console.error('Error deleting notification:', error));
        }

        function viewBatchDetails(notificationId, requestId, action) {
            // Show loading state
            const loadingHtml = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading batch details...</p>
                </div>
            `;
            
            // Create or update modal
            let modal = document.getElementById('batchDetailsModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.id = 'batchDetailsModal';
                modal.innerHTML = `
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Batch Request Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="batchDetailsContent">
                                ${loadingHtml}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            } else {
                document.getElementById('batchDetailsContent').innerHTML = loadingHtml;
            }
            
            // Show modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            // Fetch batch details
            fetch(`/notifications/${notificationId}/batch-details`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('batchDetailsContent').innerHTML = data.html;
                } else {
                    document.getElementById('batchDetailsContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Error loading batch details: ${data.message || 'Unknown error'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error fetching batch details:', error);
                document.getElementById('batchDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error loading batch details. Please try again.
                    </div>
                `;
            });
        }
    </script>
@endif
