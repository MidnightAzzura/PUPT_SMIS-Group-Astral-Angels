<div class="batch-details-container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i>
                        Notification Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Action:</strong>
                            @if ($notification->action == 'request_approved')
                                <span class="badge bg-success ms-2">Approved</span>
                            @elseif ($notification->action == 'request_denied')
                                <span class="badge bg-danger ms-2">Denied</span>
                            @elseif ($notification->action == 'request_pending')
                                <span class="badge bg-warning text-dark ms-2">Pending</span>
                            @elseif ($notification->action == 'new_request_submitted')
                                <span class="badge bg-primary ms-2">New Request</span>
                            @elseif ($notification->action == 'batch_submitted')
                                <span class="badge bg-info ms-2">New Batch</span>
                            @else
                                <span class="badge bg-secondary ms-2">{{ ucfirst(str_replace('_', ' ', $notification->action)) }}</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Message:</strong>
                            {{ $notification->message }}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <strong>Date:</strong> {{ $notification->created_at->format('M d, Y h:i A') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Office:</strong> {{ $notification->office }}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <strong>Status:</strong> 
                            @if ($notification->is_read)
                                <span class="badge bg-secondary">Read</span>
                            @else
                                <span class="badge bg-warning text-dark">Unread</span>
                            @endif
                        </div>
                    </div>
                    @if ($batchInfo)
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <strong>Batch Info:</strong> 
                                <span class="badge bg-info">{{ $batchInfo['batch_count'] ?? count($requests) }} items in batch</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-list"></i>
                        Request Details ({{ count($requests) }} {{ count($requests) == 1 ? 'item' : 'items' }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Stock #</th>
                                    <th>Supply Name</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Purpose</th>
                                    <th>Requester</th>
                                    <th>Status</th>
                                    <th>Date Requested</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests as $request)
                                    <tr>
                                        <td>
                                            <code>{{ $request->StockNumber }}</code>
                                        </td>
                                        <td>
                                            <strong>{{ $request->supply_name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $request->supply_quantity }}</span>
                                        </td>
                                        <td>{{ $request->Unit }}</td>
                                        <td>
                                            <small class="text-muted">{{ \Illuminate\Support\Str::limit($request->Purpose ?? $request->purpose, 50) }}</small>
                                        </td>
                                        <td>
                                            @if ($request->user)
                                                <div>
                                                    <strong>{{ $request->user->First_Name }} {{ $request->user->Last_Name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $request->user->Office }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">Unknown User</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($request->status == 'Approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif ($request->status == 'Pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif ($request->status == 'Disapproved')
                                                <span class="badge bg-danger">Disapproved</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $request->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $request->created_at->format('M d, Y') }}<br>{{ $request->created_at->format('h:i A') }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($requests->isNotEmpty() && $requests->first()->purpose)
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-clipboard-list"></i>
                            Request Purpose
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $requests->first()->Purpose ?? $requests->first()->purpose }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar"></i>
                        Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary">{{ count($requests) }}</h4>
                                <small class="text-muted">Total Items</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success">{{ $requests->sum('supply_quantity') }}</h4>
                                <small class="text-muted">Total Quantity</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info">{{ $requests->unique('user_id')->count() }}</h4>
                                <small class="text-muted">Unique Requesters</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning">{{ $requests->unique('Office')->count() }}</h4>
                                <small class="text-muted">Offices Involved</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.batch-details-container .card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.batch-details-container .table th {
    font-size: 0.875rem;
    font-weight: 600;
}

.batch-details-container .table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

.batch-details-container code {
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.8rem;
}
</style>
