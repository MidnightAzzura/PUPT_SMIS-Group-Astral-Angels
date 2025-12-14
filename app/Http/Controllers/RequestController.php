<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestItem; 
use App\Models\Requests; 
use App\Models\Supply; 
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Users;
use App\Services\AdminAuditService;
use App\Exports\ApprovedRequestsExport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RequestApproved;
use App\Notifications\RequestDenied;
use Illuminate\Support\Facades\Log;
use App\Services\SidebarNotificationService;

class RequestController extends Controller
{
    protected $notificationService;

    public function __construct(SidebarNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

   
    public function pending(Request $httpRequest)
    {
        // Get current user role
        $userRole = strtolower(trim(Auth::user()->role));
        $userOffice = Auth::user()->Office;
        
        $query = Requests::with('user')->where('status', 'Pending');
        
        // Filter by specific request ID if provided (from notification)
        if ($httpRequest->has('request_id') && $httpRequest->request_id) {
            $query->where('id', $httpRequest->request_id);
        }
        
        // Apply office filter for non-admin/non-superadmin users BEFORE pagination
        if (!in_array($userRole, ['superadmin', 'admin'])) {
            $query->whereHas('user', function($q) use ($userOffice) {
                $q->where('Office', $userOffice);
            });
        } else {
            // For SuperAdmin/Admin: Apply office filter if provided in request
            if ($httpRequest->has('office') && $httpRequest->office) {
                $query->whereHas('user', function($q) use ($httpRequest) {
                    $q->where('Office', $httpRequest->office);
                });
            }
        }
        
        $pendingRequests = $query->orderBy('updated_at', 'desc')->paginate(10);

        // Get ALL unique offices from pending requests (for SuperAdmin/Admin dropdown)
        $allOffices = [];
        if (in_array($userRole, ['superadmin', 'admin'])) {
            $allOffices = Requests::with('user')
                ->where('status', 'Pending')
                ->whereHas('user', function($q) {
                    $q->whereNotNull('Office');
                })
                ->get()
                ->pluck('user.Office')
                ->unique()
                ->filter()
                ->sort()
                ->values()
                ->toArray();
        }

        // Add filter info to view for display
        $filterInfo = [
            'request_id' => $httpRequest->request_id ?? null,
            'office' => $httpRequest->office ?? null
        ];
        
        // Pass the selected office to the view
        $selectedOffice = $httpRequest->office ?? null;
             
        return view('requests.pending', compact('pendingRequests', 'filterInfo', 'selectedOffice', 'allOffices'));
    }   
    
    public function approved(Request $httpRequest)
    {
        // Get current user role
        $userRole = strtolower(trim(Auth::user()->role));
        $userOffice = Auth::user()->Office;
        
        $query = Requests::with('user')->where('status', 'Approved')
            ->where(function($q) {
                $q->whereNotNull('issue_quantity')
                  ->whereNotNull('received_by')
                  ->whereNotNull('received_position');
            });
        
        // Filter by specific request ID if provided (from notification)
        if ($httpRequest->has('request_id') && $httpRequest->request_id) {
            $query->where('id', $httpRequest->request_id);
        }
        
        // Apply office filter for non-admin/non-superadmin users BEFORE pagination
        if (!in_array($userRole, ['superadmin', 'admin'])) {
            $query->whereHas('user', function($q) use ($userOffice) {
                $q->where('Office', $userOffice);
            });
        } else {
            // For SuperAdmin/Admin: Apply office filter if provided in request
            if ($httpRequest->has('office') && $httpRequest->office) {
                $query->whereHas('user', function($q) use ($httpRequest) {
                    $q->where('Office', $httpRequest->office);
                });
            }
        }
        
        $approvedRequests = $query->orderBy('updated_at', 'desc')->paginate(10);
        
        foreach($approvedRequests as $req) {
            // Format timestamps
            $req->last_updated = $req->updated_at ? $req->updated_at->format('M d, Y H:i:s') : 'N/A';
            $req->created_date = $req->created_at ? $req->created_at->format('M d, Y H:i:s') : 'N/A';
            
            // Track RIS file history
            $req->has_ris = !empty($req->ris_file_path);
        }

        // Get ALL unique offices from approved requests (for SuperAdmin/Admin dropdown)
        $allOffices = [];
        if (in_array($userRole, ['superadmin', 'admin'])) {
            $allOffices = Requests::with('user')
                ->where('status', 'Approved')
                ->whereHas('user', function($q) {
                    $q->whereNotNull('Office');
                })
                ->get()
                ->pluck('user.Office')
                ->unique()
                ->filter()
                ->sort()
                ->values()
                ->toArray();
        }

        // Add filter info to view for display
        $filterInfo = [
            'request_id' => $httpRequest->request_id ?? null,
            'office' => $httpRequest->office ?? null
        ];
        
        // Pass the selected office to the view
        $selectedOffice = $httpRequest->office ?? null;
        
        return view('Admin.requests.approved', compact('approvedRequests', 'filterInfo', 'selectedOffice', 'allOffices'));
    }

    public function released(Request $httpRequest)
    {
        // Get current user role and office
        $userRole = strtolower(trim(Auth::user()->role));
        $userOffice = Auth::user()->Office;
        
        // Build query with role-based filtering
        $query = Requests::with('user')->where('status', 'released');
        
        // Apply office filter for non-admin/non-superadmin users BEFORE pagination
        if (!in_array($userRole, ['superadmin', 'admin'])) {
            $query->whereHas('user', function($q) use ($userOffice) {
                $q->where('Office', $userOffice);
            });
        } else {
            // For SuperAdmin/Admin: Apply office filter if provided in request
            if ($httpRequest->has('office') && $httpRequest->office) {
                $query->whereHas('user', function($q) use ($httpRequest) {
                    $q->where('Office', $httpRequest->office);
                });
            }
        }
        
        // Fetch released requests with pagination
        $releasedRequests = $query->orderBy('updated_at', 'desc')->paginate(10);
        
        // Get ALL unique offices from released requests (for SuperAdmin/Admin dropdown)
        $allOffices = [];
        if (in_array($userRole, ['superadmin', 'admin'])) {
            $allOffices = Requests::with('user')
                ->where('status', 'released')
                ->whereHas('user', function($q) {
                    $q->whereNotNull('Office');
                })
                ->get()
                ->pluck('user.Office')
                ->unique()
                ->filter()
                ->sort()
                ->values()
                ->toArray();
        }
        
        // Pass the selected office to the view for maintaining filter state
        $selectedOffice = $httpRequest->office ?? null;
            
        return view('requests.released', compact('releasedRequests', 'selectedOffice', 'allOffices'));
    }

    public function disapproved(Request $httpRequest)
    {
        // Get current user role
        $userRole = strtolower(trim(Auth::user()->role));
        $userOffice = Auth::user()->Office;
        
        $query = Requests::with('user')->whereIn('status', ['Disapproved', 'Cancelled', 'Expired']);
        
        // Filter by specific request ID if provided (from notification)
        if ($httpRequest->has('request_id') && $httpRequest->request_id) {
            $query->where('id', $httpRequest->request_id);
        }
        
        // Apply office filter for non-admin/non-superadmin users BEFORE pagination
        if (!in_array($userRole, ['superadmin', 'admin'])) {
            $query->whereHas('user', function($q) use ($userOffice) {
                $q->where('Office', $userOffice);
            });
        } else {
            // For SuperAdmin/Admin: Apply office filter if provided in request
            if ($httpRequest->has('office') && $httpRequest->office) {
                $query->whereHas('user', function($q) use ($httpRequest) {
                    $q->where('Office', $httpRequest->office);
                });
            }
        }
        
        $disapprovedRequests = $query->orderBy('updated_at', 'desc')->paginate(10);

        // Get ALL unique offices from disapproved requests (for SuperAdmin/Admin dropdown)
        $allOffices = [];
        if (in_array($userRole, ['superadmin', 'admin'])) {
            $allOffices = Requests::with('user')
                ->whereIn('status', ['Disapproved', 'Cancelled', 'Expired'])
                ->whereHas('user', function($q) {
                    $q->whereNotNull('Office');
                })
                ->get()
                ->pluck('user.Office')
                ->unique()
                ->filter()
                ->sort()
                ->values()
                ->toArray();
        }

        // Add filter info to view for display
        $filterInfo = [
            'request_id' => $httpRequest->request_id ?? null,
            'office' => $httpRequest->office ?? null
        ];
        
        // Pass the selected office to the view
        $selectedOffice = $httpRequest->office ?? null;
        
        return view('requests.disapproved', compact('disapprovedRequests', 'filterInfo', 'selectedOffice', 'allOffices'));
    }
 public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.stock_number' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:1000'
        ], [
            'items.*.quantity.required' => 'The quantity field is required',
            'items.*.quantity.min' => 'The quantity must be at least 1'
        ]);

        // Determine action: normal submit or cancel request
        $actionType = $request->input('action_type', 'submit'); // 'submit' or 'cancel'

        try {
            DB::beginTransaction();
            
            $user = Auth::user();
            $userOffice = $user->Office;
            $createdRequests = [];
            
            foreach($request->items as $item) {
                $supply = Supply::where('StockNumber', $item['stock_number'])->first();
                if(!$supply) {
                    throw new \Exception('Supply not found');
                }
                
                // Create request record
                $newRequest = new Requests();
                $newRequest->StockNumber = $item['stock_number'];
                $newRequest->supply_name = $supply->ItemDescription;
                $newRequest->purpose = $request->purpose;
                $newRequest->supply_quantity = $item['quantity'];
                // Set status depending on action type
                if ($actionType === 'cancel') {
                    $newRequest->status = 'Disapproved';
                    //Pressed the cancel button to and log into the disapproved page
                    $newRequest->status = 'Cancelled';
                    // record who performed the cancel (similar format used in deny())
                    $userRole = ucfirst($user->role);
                    $userName = trim("{$user->First_Name} {$user->Last_Name}");
                    $newRequest->updated_by = "{$userRole} - {$userName}";
                    $newRequest->updated_at = now();
                } else {
                    $newRequest->status = 'Pending';
                }
                $newRequest->Unit = $supply->Unit;
                $newRequest->Office = $userOffice; // Set the office from user
                $newRequest->user_id = auth()->id();
                $newRequest->save();

                // If cancelled, log denial for audit
                if ($actionType === 'cancel') {
                    try {
                        AdminAuditService::logRequestDenied($newRequest);
                    } catch (\Exception $auditEx) {
                        Log::warning('Audit log for cancelled request failed: ' . $auditEx->getMessage());
                    }
                }

                // Collect created requests for batch notification
                $createdRequests[] = $newRequest;
            }

            // Create single batch notification for all submitted/cancelled requests
            if (!empty($createdRequests)) {
                try {
                    // Create a batch identifier using timestamp and purpose
                    $timestamp = now()->format('Y-m-d_H:i:s');
                    $batchId = md5($request->purpose . '_' . $timestamp . '_' . auth()->id());
                    
                    // Use the first request as template and modify for batch
                    $batchRequest = clone $createdRequests[0];
                    $batchRequest->SupplyName = count($createdRequests) > 1 
                        ? "Batch of " . count($createdRequests) . " items"
                        : $createdRequests[0]->supply_name;
                    
                    // Store batch metadata in the request object for notification
                    $batchRequest->batch_id = $batchId;
                    $batchRequest->batch_request_ids = collect($createdRequests)->pluck('id')->toArray();
                    $batchRequest->batch_count = count($createdRequests);
                    
                    // Use appropriate notification type for submit vs cancel
                    $notificationType = $actionType === 'cancel' ? 'request_denied' : 'request_pending';
                    $this->notificationService->createRequestNotification($batchRequest, $notificationType);
                    
                    Log::info("Batch notification created", [
                        'batch_id' => $batchId,
                        'request_count' => count($createdRequests),
                        'request_ids' => collect($createdRequests)->pluck('id')->toArray(),
                        'user_email' => $user->email,
                        'notification_type' => $notificationType
                    ]);
                } catch (\Exception $notifyError) {
                    // Log notification error but don't fail the request submission
                    Log::error('Failed to send batch notification: ' . $notifyError->getMessage());
                }
            }

            DB::commit();

            // Redirect based on action type: cancelled requests go to disapproved
            if ($actionType === 'cancel') {
                return redirect()->route('requests.disapproved')
                                ->with('success', 'Request cancelled and logged as disapproved.');
            }

            return redirect()->route('requests.pending')
                            ->with('success', 'Request submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating request: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }    public function accept($stockNumber)
    {
        try {
            DB::beginTransaction();
            
            $currentUser = Auth::user();
            if (!$currentUser) {
                throw new \Exception('Unauthorized access');
            }

            // Get all pending requests with this stock number
            $requests = Requests::with('user')
                              ->where('StockNumber', $stockNumber)
                              ->where('status', 'Pending')
                              ->get();

            $affectedUsers = [];
            foreach ($requests as $request) {
                // Update request status
                $request->status = 'Approved';
                $request->approved_by = $currentUser->id;
                $request->approved_at = now();
                $risPath = $this->generateEditableRIS($stockNumber);
                $request->ris_file_path = $risPath;
                $request->approved_at = now();
                $request->save();

                // Log the approval action
                AdminAuditService::logRequestApproved($request);

                // Collect affected users for batch notification
                if ($request->user) {
                    $userId = $request->user->id;
                    if (!isset($affectedUsers[$userId])) {
                        $affectedUsers[$userId] = [
                            'user' => $request->user,
                            'requests' => []
                        ];
                    }
                    $affectedUsers[$userId]['requests'][] = $request;
                }
            }
            
            // Send batch notifications to affected users
            foreach ($affectedUsers as $userData) {
                $user = $userData['user'];
                $userRequests = $userData['requests'];
                $requestCount = count($userRequests);
                $firstRequest = $userRequests[0];
                
                try {
                    // Create batch identifier
                    $timestamp = now()->format('Y-m-d_H:i:s');
                    $batchId = md5('approval_' . $stockNumber . '_' . $timestamp . '_' . $user->id);
                    
                    // Create a single batch notification for this user (removed duplicate email notification)
                    $batchRequest = clone $firstRequest;
                    $batchRequest->supply_name = "Batch of {$requestCount} items: " . $firstRequest->supply_name;
                    
                    // Add batch metadata
                    $batchRequest->batch_id = $batchId;
                    $batchRequest->batch_request_ids = collect($userRequests)->pluck('id')->toArray();
                    $batchRequest->batch_count = $requestCount;
                    
                    // Send SINGLE batch email notification (consolidated)
                    $user->notify(new \App\Notifications\BatchRequestApproved($batchRequest, $userRequests, $batchId));
                    
                    // Create ONLY ONE database notification using the service
                    $notification = $this->notificationService->createRequestNotification($batchRequest, 'request_approved');
                    
                    // Debug log
                    Log::info('Batch approval notification created', [
                        'notification_id' => $notification ? $notification->notification_id : 'failed',
                        'batch_id' => $batchId,
                        'request_count' => $requestCount,
                        'request_ids' => collect($userRequests)->pluck('id')->toArray(),
                        'target_user' => $user->id,
                        'target_office' => $user->Office,
                        'email_sent' => 'batch_email'
                    ]);
                } catch (\Exception $notifyError) {
                    // Log notification error but don't fail the approval
                    Log::error('Failed to send batch approval notification: ' . $notifyError->getMessage());
                }
            }            DB::commit();
            
            // Create a more efficient script that uses our enhanced notification system
            $script = "<script>
                window.onload = function() {
                    // Use our enhanced notification system
                    if (typeof checkForNewNotifications === 'function') {
                        // This will trigger the notificationsUpdated event listener we added
                        checkForNewNotifications();
                    } else {
                        // Fallback to direct fetch if the enhanced system isn't available
                        fetch('/api/notifications/counts', {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Notifications updated after approval');
                                // Create and dispatch the event
                                const event = new CustomEvent('notificationsUpdated', {detail: data});
                                document.dispatchEvent(event);
                            }
                        })
                        .catch(error => console.error('Error updating notifications:', error));
                    }
                };
            </script>";
            
            return redirect()->route('requests.approved')
                            ->with('success', 'Requests approved successfully and notifications sent.')
                            ->with('script', $script);
                            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error approving requests: ' . $e->getMessage());
        }
    }    public function deny($stockNumber)
    {
        try {
            DB::beginTransaction();
            
            $user = Auth::user();
            $userRole = ucfirst($user->role); // Capitalize first letter
            $userName = trim("{$user->First_Name} {$user->Last_Name}"); 
            $updatedBy = "{$userRole} - {$userName}";
            
            // Get all pending requests for this stock number
            $requests = Requests::where('StockNumber', $stockNumber)
                          ->where('status', 'Pending')
                          ->get();
            
            // Group requests by user for batch notifications
            $userBatches = [];
            
            foreach ($requests as $request) {
                $request->status = 'Disapproved';
                $request->updated_by = $updatedBy;
                $request->updated_at = now();
                $request->save();

                // Log the denial action
                AdminAuditService::logRequestDenied($request);

                // Group requests by user for batch notifications
                $userId = $request->user_id;
                if (!isset($userBatches[$userId])) {
                    $userBatches[$userId] = [];
                }
                $userBatches[$userId][] = $request;
            }
            
            // Send batch notifications grouped by user
            foreach ($userBatches as $userId => $userRequests) {
                $user = Users::find($userId);
                if ($user && count($userRequests) > 0) {
                    try {
                        // Create batch identifier
                        $timestamp = now()->format('Y-m-d_H:i:s');
                        $batchId = md5('denial_' . $stockNumber . '_' . $timestamp . '_' . $userId);
                        
                        // For batch notifications, use the first request as template and modify the message
                        $batchRequest = clone $userRequests[0];
                        $batchRequest->SupplyName = count($userRequests) > 1 
                            ? "Batch of " . count($userRequests) . " denied items"
                            : $userRequests[0]->SupplyName;
                        
                        // Add batch metadata
                        $batchRequest->batch_id = $batchId;
                        $batchRequest->batch_request_ids = collect($userRequests)->pluck('id')->toArray();
                        $batchRequest->batch_count = count($userRequests);
                        
                        // Send SINGLE batch email notification (consolidated)
                        $user->notify(new \App\Notifications\BatchRequestDenied($batchRequest, $userRequests, 'Request denied by administrator', $batchId));
                        
                        // Create ONLY ONE database notification using the service
                        $result = $this->notificationService->createRequestNotification($batchRequest, 'request_denied');
                        
                        // Debug log after creating notification
                        Log::info('Batch denial notification created', [
                            'success' => $result,
                            'batch_count' => count($userRequests),
                            'batch_id' => $batchId,
                            'user_id' => $userId,
                            'office' => $user->Office ?? 'Unknown',
                            'email_sent' => 'batch_email'
                        ]);
                    } catch (\Exception $notifyError) {
                        // Log notification error but don't fail the denial
                        Log::error('Failed to send batch denial notification to user ID ' . $userId . ': ' . $notifyError->getMessage());
                    }
                }
            }
              DB::commit();
            
            // Add JavaScript to ensure notification updates after redirect
            $script = "<script>
                window.onload = function() {
                    fetch('/api/notifications/counts', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Notifications updated after denial');
                        // Dispatch an event that the main app can listen for
                        const event = new CustomEvent('notificationsUpdated', {detail: data});
                        document.dispatchEvent(event);
                    })
                    .catch(error => console.error('Error:', error));
                };
            </script>";
            
            return redirect()->route('requests.pending')
                            ->with('success', 'Request(s) have been disapproved successfully and notifications sent.')
                            ->with('script', $script);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error disapproving request: ' . $e->getMessage());
        }
    }//exporting or downloading the file into excel
    public function exportRIS()
    {
        return Excel::download(new ApprovedRequestsExport, 'approved_requests_ris.xlsx');
    }

    public function editRIS(Request $request, $stockNumber)
    {
        $batchKey = $request->get('batchKey');
        
        if ($batchKey) {
            // Handle batch editing - get all requests in the batch
            $allPendingRequests = Requests::where('status', 'Pending')->get();
            
            // Recreate the same grouping logic as in pending.blade.php
            $normalizedRequests = $allPendingRequests->map(function($req) {
                $req->normalizedPurpose = strtolower(trim($req->Purpose));
                $timestamp = $req->created_at ?? now();
                $minutes = floor($timestamp->minute / 10) * 10;
                $batchTimestamp = $timestamp->format('Y-m-d_H_') . $minutes;
                $req->batchKey = $req->normalizedPurpose . '_' . $batchTimestamp;
                return $req;
            });
            
            // Get all requests that match the batch key
            $batchRequests = $normalizedRequests->where('batchKey', $batchKey);
            
            if ($batchRequests->isEmpty()) {
                // Fallback to single request if batch is empty
                $requests = Requests::where('StockNumber', $stockNumber)->firstOrFail();
            } else {
                // Convert to array for the view
                $requests = $batchRequests->values()->all();
            }
        } else {
            // Handle single request editing
            $requests = Requests::where('StockNumber', $stockNumber)->firstOrFail();
        }
        
        // Generate RIS template for the primary request
        $primaryRequest = is_array($requests) ? $requests[0] : $requests;
        $this->generateEditableRIS($primaryRequest->StockNumber);
        
        return view('requests.editRIS', compact('requests'));
    }

    public function updateRIS(Request $request, $stockNumber)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validate([
                'quantities.*' => 'required|numeric|min:0',
                'issue_quantities.*' => 'required|numeric|min:0', // Changed from min:1 to min:0
                'stock_available.*' => 'required|in:yes,no',
                'stock_numbers.*' => 'required|string',
                'request_ids.*' => 'required|exists:requests,id',
                'received_by' => 'required|string',
                'received_position' => 'required|string',
                'purpose' => 'required|string',
                'remarks.*' => 'nullable|string'
            ]);
            
            // Add custom validation for zero quantities requiring remarks
            foreach ($validatedData['issue_quantities'] as $index => $quantity) {
                if ((int)$quantity === 0 && empty($validatedData['remarks'][$index])) {
                    return back()->withErrors([
                        'remarks.'.$index => 'A remark is required when issue quantity is zero.'
                    ])->withInput();
                }
            }

            // Process each item in the batch and collect request data
            $processedRequests = [];
            $userBatches = [];
            
            foreach ($validatedData['stock_numbers'] as $index => $itemStockNumber) {
                $requestId = $validatedData['request_ids'][$index];
                $requestRecord = Requests::findOrFail($requestId);
                
                // Update request record
                $requestRecord->supply_quantity = $validatedData['quantities'][$index];
                $requestRecord->status = 'Approved';
                $requestRecord->remarks = $validatedData['remarks'][$index] ?? '';
                $requestRecord->Purpose = $validatedData['purpose'];
                $requestRecord->received_by = $validatedData['received_by'];
                $requestRecord->received_position = $validatedData['received_position'];
                $requestRecord->stock_available = $validatedData['stock_available'][$index];
                $requestRecord->issue_quantity = $validatedData['issue_quantities'][$index];
                $requestRecord->updated_at = now();

                // Generate RIS for each request
                $risPath = $this->generateEditableRIS($itemStockNumber);
                $requestRecord->ris_file_path = $risPath;
                $requestRecord->save();

                // Store the processed request
                $processedRequests[] = $requestRecord;
                
                // Group requests by user for batch notifications
                $userId = $requestRecord->user_id;
                if (!isset($userBatches[$userId])) {
                    $userBatches[$userId] = [];
                }
                $userBatches[$userId][] = $requestRecord;

                // Update supply inventory if stock is available and issue quantity > 0
                if ($validatedData['stock_available'][$index] === 'yes' && (int)$validatedData['issue_quantities'][$index] > 0) {
                    $supply = Supply::where('StockNumber', $itemStockNumber)->first();
                    if ($supply) {
                        $supply->Quantity -= $validatedData['issue_quantities'][$index];
                        $supply->save();
                    }
                }
            }
            
            // Send batch notifications grouped by user
            foreach ($userBatches as $userId => $userRequests) {
                $user = Users::find($userId);
                if ($user && count($userRequests) > 0) {
                    try {
                        // Create batch identifier
                        $timestamp = now()->format('Y-m-d_H:i:s');
                        $batchId = md5('ris_approval_' . $stockNumber . '_' . $timestamp . '_' . $userId);
                        
                        // For batch notifications, use the first request as template and modify the message
                        $batchRequest = clone $userRequests[0];
                        $batchRequest->SupplyName = count($userRequests) > 1 
                            ? "Batch of " . count($userRequests) . " approved items"
                            : $userRequests[0]->SupplyName;
                        
                        // Add batch metadata
                        $batchRequest->batch_id = $batchId;
                        $batchRequest->batch_request_ids = collect($userRequests)->pluck('id')->toArray();
                        $batchRequest->batch_count = count($userRequests);
                        
                        // Send SINGLE batch email notification (consolidated)
                        $user->notify(new \App\Notifications\BatchRequestApproved($batchRequest, $userRequests, $batchId));
                        
                        // Create ONLY ONE database notification using the service
                        $this->notificationService->createRequestNotification($batchRequest, 'request_approved');
                        
                        Log::info("RIS batch notification created", [
                            'batch_id' => $batchId,
                            'request_count' => count($userRequests),
                            'request_ids' => collect($userRequests)->pluck('id')->toArray(),
                            'user_email' => $user->email,
                            'email_sent' => 'batch_email'
                        ]);
                    } catch (\Exception $notifyError) {
                        // Log notification error but don't fail the approval
                        Log::error('Failed to send batch approval notification to user ID ' . $userId . ': ' . $notifyError->getMessage());
                    }
                }
            }
            
            DB::commit();
            return redirect()->route('requests.approved')
                            ->with('success', 'Batch RIS has been approved, inventory updated, and email notifications sent to users.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }    public function generateEditableRIS($stockNumber)
    {
        $request = Requests::where('StockNumber', $stockNumber)->firstOrFail();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document header information
        $sheet->setCellValue('H1', 'PUP-RISL-6-PSMO-010');
        $sheet->setCellValue('H2', 'Rev. 2');
        $sheet->setCellValue('H3', 'Effectivity Date: ' . now()->format('F d, Y'));
        $sheet->setCellValue('H5', 'Appendix 63');
        
        // Right align the header information
        $sheet->getStyle('H1:H5')->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Set main title and center it
        $sheet->setCellValue('A7', 'REQUISITION AND ISSUE SLIP');
        $sheet->mergeCells('A7:H7');
        $sheet->getStyle('A7')
              ->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getFont()->setBold(true);

        // Set entity information
        $sheet->setCellValue('A9', 'Entity Name:');
        $sheet->setCellValue('C9', 'PUP TAGUIG CAMPUS');
        $sheet->setCellValue('G9', 'Fund Cluster: ______________________');

        $sheet->setCellValue('A11', 'Division:');
        $sheet->setCellValue('F11', 'Responsibility Center Code: ______________________');

        $sheet->setCellValue('A12', 'Office:');
        $sheet->setCellValue('C12', 'HEAD OF ACADEMIC');
        $sheet->setCellValue('F12', 'RIS No.: _____________________');

        // Create table headers
        $sheet->mergeCells('A14:D14');
        $sheet->mergeCells('E14:F14');
        $sheet->mergeCells('G14:H14');
        
        $sheet->setCellValue('A14', 'Requisition');
        $sheet->setCellValue('E14', 'Stock Available?');
        $sheet->setCellValue('G14', 'Issue');
        
        $sheet->setCellValue('A15', 'Stock No.');
        $sheet->setCellValue('B15', 'Unit');
        $sheet->setCellValue('C15', 'Description');
        $sheet->setCellValue('D15', 'Quantity');
        $sheet->setCellValue('E15', 'Yes');
        $sheet->setCellValue('F15', 'No');
        $sheet->setCellValue('G15', 'Quantity');
        $sheet->setCellValue('H15', 'Remarks');

        // Style the headers
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A14:H15')->applyFromArray($headerStyle);

        // Populate the table with data
        $sheet->setCellValue('A16', $request->StockNumber);
        $sheet->setCellValue('B16', $request->Unit);
        $sheet->setCellValue('C16', $request->supply_name);
        $sheet->setCellValue('D16', $request->supply_quantity);
        $sheet->setCellValue('E16', $request->stock_available === 'yes' ? '✓' : '');
        $sheet->setCellValue('F16', $request->stock_available === 'no' ? '✓' : '');
        $sheet->setCellValue('G16', $request->issue_quantity);
        $sheet->setCellValue('H16', $request->remarks);

        // Style the data row
        $sheet->getStyle('A16:H16')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Add purpose section
        $sheet->setCellValue('A18', 'Purpose:');
        $sheet->setCellValue('B18', $request->purpose ?? 'OFFICE USE');

        // Add signature section
        $row = 21;
        $sheet->setCellValue('B' . $row, 'Requested by:');
        $sheet->setCellValue('D' . $row, 'Approved by:');
        $sheet->setCellValue('F' . $row, 'Issued by:');
        $sheet->setCellValue('H' . $row, 'Received by:');

        $row += 2;
        $sheet->setCellValue('B' . $row, 'DR. RHYAN V. MOLINAR');
        $sheet->setCellValue('D' . $row, 'DR. MARISSA B. FERRER');
        $sheet->setCellValue('F' . $row, 'GINA A. DELA CRUZ');
        $sheet->setCellValue('H' . $row, $request->received_by);

        $row += 1;
        $sheet->setCellValue('B' . $row, 'HAP');
        $sheet->setCellValue('D' . $row, 'DIRECTOR');
        $sheet->setCellValue('F' . $row, 'PROPERTY CUSTODIAN');
        $sheet->setCellValue('H' . $row, $request->received_position);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(20);

        // Save the RIS file
        $filePath = storage_path('app/ris_templates/' . $stockNumber . '_RIS.xlsx');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

        // Ensure the directory exists
        $directory = storage_path('app/ris_templates');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $writer->save($filePath);

        return $filePath;
    }    public function downloadRIS($stockNumber)
    {
        try {
            DB::beginTransaction();
            
            // Get the primary request
            $primaryRequest = Requests::where('StockNumber', $stockNumber)->firstOrFail();
            
            // Find all related requests in the same batch using the same grouping logic
            $allApprovedRequests = Requests::where('status', 'Approved')->get();
            
            $normalizedRequests = $allApprovedRequests->map(function($request) {
                $request->normalizedPurpose = strtolower(trim($request->purpose ?? $request->Purpose ?? 'office use'));
                $timestamp = $request->created_at ?? now();
                $minutes = floor($timestamp->minute / 10) * 10;
                $batchTimestamp = $timestamp->format('Y-m-d_H_') . $minutes;
                $request->batchKey = $request->normalizedPurpose . '_' . $batchTimestamp;
                return $request;
            });
        
        // Get the batch key for the primary request
        $primaryBatchKey = $normalizedRequests->where('StockNumber', $stockNumber)->first()->batchKey ?? null;
        
        // Get all requests in the same batch
        $batchRequests = $normalizedRequests->where('batchKey', $primaryBatchKey);
        
        // If no batch found, fallback to single request
        if ($batchRequests->isEmpty()) {
            $batchRequests = collect([$primaryRequest]);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document header information and align right
        $sheet->setCellValue('H1', 'PUP-RISL-6-PSMO-010');
        $sheet->setCellValue('H2', 'Rev. 2');
        $sheet->setCellValue('H3', 'Effectivity Date: June 3, 2022');
        $sheet->setCellValue('H5', 'Appendix 63');
        
        // Right align the header information
        $sheet->getStyle('H1:H5')->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Set main title and center it
        $sheet->setCellValue('A7', 'REQUISITION AND ISSUE SLIP');
        $sheet->mergeCells('A7:H7');
        $sheet->getStyle('A7')
              ->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getFont()->setBold(true);

        // Set entity information - use dynamic office from first request
        $userOffice = $primaryRequest->user ? strtoupper($primaryRequest->user->Office ?? 'HEAD OF ACADEMIC') : 'HEAD OF ACADEMIC';
        
        $sheet->setCellValue('A9', 'Entity Name :');
        $sheet->setCellValue('C9', 'PUP TAGUIG CAMPUS');
        $sheet->setCellValue('G9', 'Fund Cluster : ______________________');

        $sheet->setCellValue('A11', 'Division :');
        $sheet->setCellValue('F11', 'Responsibility Center Code : ______________________');

        $sheet->setCellValue('A12', 'Office :');
        $sheet->setCellValue('C12', $userOffice);
        $sheet->setCellValue('F12', 'RIS No. : _____________________________________');

        // Create table headers with merged cells
        $sheet->mergeCells('A14:D14');
        $sheet->mergeCells('E14:F14');
        $sheet->mergeCells('G14:H14');
        $sheet->setCellValue('A14', 'Requisition');
        $sheet->setCellValue('E14', 'Stock Available?');
        $sheet->setCellValue('G14', 'Issue');
        
        $sheet->setCellValue('A15', 'Stock No.');
        $sheet->setCellValue('B15', 'Unit');
        $sheet->setCellValue('C15', 'Description');
        $sheet->setCellValue('D15', 'Quantity');
        $sheet->setCellValue('E15', 'Yes');
        $sheet->setCellValue('F15', 'No');
        $sheet->setCellValue('G15', 'Quantity');
        $sheet->setCellValue('H15', 'Remarks');

        // Apply borders and styles to header cells
        $headerStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ],
            'font' => [
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ];
        
        // Apply to header rows
        $sheet->getStyle('A14:H15')->applyFromArray($headerStyle);

        // Populate the table with all batch requests
        $rowIndex = 16;
        $totalItems = $batchRequests->count();
        
        // Count additional items
        $additionalItemsCount = $batchRequests->sum(function($req) {
            return $req->items ? $req->items->count() : 0;
        });
          // Add batch header if there are multiple items
        if ($totalItems > 1 || $additionalItemsCount > 0) {
            $displayPurpose = $primaryRequest->purpose ?: $primaryRequest->Purpose ?: 'OFFICE USE';
            $sheet->mergeCells('A' . $rowIndex . ':H' . $rowIndex);
            // Use setCellValueExplicit with TYPE_STRING to prevent formula interpretation
            $sheet->setCellValueExplicit(
                'A' . $rowIndex, 
                'BATCH REQUEST - ' . strtoupper($displayPurpose) . ' (Total Items: ' . ($totalItems + $additionalItemsCount) . ')',
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $sheet->getStyle('A' . $rowIndex)->getAlignment()
                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $rowIndex)->getFont()->setBold(true);
            $sheet->getStyle('A' . $rowIndex)->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');
            $sheet->getStyle('A' . $rowIndex . ':H' . $rowIndex)->applyFromArray($headerStyle);
            $rowIndex++;
        }
        
        // Add all requests in the batch
        foreach ($batchRequests as $request) {
            // Sanitize data to prevent formula errors
            $stockNum = $this->sanitizeExcelValue($request->StockNumber);
            $unit = $this->sanitizeExcelValue($request->Unit ?? 'pcs');
            $supplyName = $this->sanitizeExcelValue($request->supply_name);
            $quantity = $this->sanitizeNumericValue($request->supply_quantity);
            $issueQty = $this->sanitizeNumericValue($request->issue_quantity ?? $request->supply_quantity);
            $remarks = $this->sanitizeExcelValue($request->remarks ?? '');
            
            // Add main request item
            $sheet->setCellValueExplicit('A' . $rowIndex, $stockNum, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B' . $rowIndex, $unit, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $rowIndex, $supplyName, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rowIndex, $quantity);
            $sheet->setCellValue('E' . $rowIndex, $request->stock_available === 'yes' ? '●' : '○');
            $sheet->setCellValue('F' . $rowIndex, $request->stock_available === 'no' ? '●' : '○');
            $sheet->setCellValue('G' . $rowIndex, $issueQty);
            $sheet->setCellValueExplicit('H' . $rowIndex, $remarks, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            
            // Apply borders to data row
            $sheet->getStyle('A' . $rowIndex . ':H' . $rowIndex)->applyFromArray($headerStyle);
            $rowIndex++;
            
            // Add additional items if they exist
            if ($request->items && $request->items->count() > 0) {
                foreach ($request->items as $item) {
                    $itemStockNum = $this->sanitizeExcelValue($item->StockNumber);
                    $itemUnit = $this->sanitizeExcelValue($item->unit ?? 'pcs');
                    $itemName = $this->sanitizeExcelValue($item->supply_name . ' (Additional Item)');
                    $itemQty = $this->sanitizeNumericValue($item->supply_quantity);
                    
                    $sheet->setCellValueExplicit('A' . $rowIndex, $itemStockNum, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('B' . $rowIndex, $itemUnit, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('C' . $rowIndex, $itemName, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValue('D' . $rowIndex, $itemQty);
                    $sheet->setCellValue('E' . $rowIndex, '○');
                    $sheet->setCellValue('F' . $rowIndex, '○');
                    $sheet->setCellValue('G' . $rowIndex, $itemQty);
                    $sheet->setCellValueExplicit('H' . $rowIndex, '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    
                    // Apply borders and light background for additional items
                    $sheet->getStyle('A' . $rowIndex . ':H' . $rowIndex)->applyFromArray($headerStyle);
                    $sheet->getStyle('A' . $rowIndex . ':H' . $rowIndex)->getFill()
                          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                          ->getStartColor()->setRGB('F5F5F5');
                    $rowIndex++;
                }
            }
        }        // Add "NOTHING FOLLOWS" marker
        $sheet->mergeCells('A' . $rowIndex . ':C' . $rowIndex);
        $sheet->setCellValueExplicit('A' . $rowIndex, '***NOTHING FOLLOWS***', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->getStyle('A' . $rowIndex)->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $rowIndex . ':H' . $rowIndex)->applyFromArray($headerStyle);
        $rowIndex++;

        // Add purpose section
        $mostCommonPurpose = $primaryRequest->purpose ?: $primaryRequest->Purpose ?: 'OFFICE USE';
        $sheet->setCellValue('A' . ($rowIndex + 3), 'Purpose:');
        $sheet->setCellValueExplicit('B' . ($rowIndex + 3), strtoupper($mostCommonPurpose), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Add signature blocks
        $signatureRow = $rowIndex + 5;
        
        // Add signature labels
        $sheet->setCellValue('B' . $signatureRow, 'Requested by:');
        $sheet->setCellValue('D' . $signatureRow, 'Approved by:');
        $sheet->setCellValue('F' . $signatureRow, 'Issued by:');
        $sheet->setCellValue('H' . $signatureRow, 'Received by:');

        // Style for signature lines and text
        $signatureStyle = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ];

        // Use dynamic requester information
        $requesterName = $primaryRequest->user ? strtoupper($primaryRequest->user->First_Name . ' ' . $primaryRequest->user->Last_Name) : 'DR. RHYAN V. MOLINAR';
        $requesterDesignation = $primaryRequest->user ? strtoupper($primaryRequest->user->Office ?? 'HAP') : 'HAP';        // Add signature lines and details
        $sheet->setCellValue('A' . ($signatureRow + 2), 'Printed Name :');
        $sheet->setCellValueExplicit('B' . ($signatureRow + 2), $requesterName, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D' . ($signatureRow + 2), 'DR. MARISSA B. FERRER', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('F' . ($signatureRow + 2), 'GINA A. DELA CRUZ', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('H' . ($signatureRow + 2), $primaryRequest->received_by ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
          $sheet->setCellValue('A' . ($signatureRow + 3), 'Designation :');
        $sheet->setCellValueExplicit('B' . ($signatureRow + 3), $requesterDesignation, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D' . ($signatureRow + 3), 'DIRECTOR', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('F' . ($signatureRow + 3), 'PROPERTY CUSTODIAN', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('H' . ($signatureRow + 3), $primaryRequest->received_position ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Date fields
        $sheet->setCellValue('A' . ($signatureRow + 4), 'Date :');

        // Apply signature styles
        foreach (['B', 'D', 'F', 'H'] as $col) {
            $sheet->getStyle($col . ($signatureRow + 1))->applyFromArray($signatureStyle);
            $sheet->getStyle($col . ($signatureRow + 2))->getAlignment()
                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . ($signatureRow + 3))->getAlignment()
                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . ($signatureRow + 4))->applyFromArray($signatureStyle);
        }        // Add AO reference
        $sheet->setCellValueExplicit('A' . ($signatureRow + 6), 'AO 6/15/02', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(8);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(8);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(20);        // Update all requests in the batch to 'released' status
        foreach ($batchRequests as $request) {
            // Find the request in the database and update its status
            if (isset($request->id)) {
                $requestModel = Requests::find($request->id);
                if ($requestModel && $requestModel->status === 'Approved') {
                    $requestModel->status = 'released';
                    $requestModel->updated_at = now();
                    $requestModel->save();
                    
                    // Log the release action in audit trail
                    AdminAuditService::logRequestReleased($requestModel);
                    
                    // Log the status change
                    Log::info('Request ID ' . $request->id . ' marked as released');
                }
            }
        }
        
        DB::commit();
          // Create the file
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $fileName = 'RIS_Batch_' . $primaryRequest->StockNumber . '_' . now()->format('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        // Add a session flash message to inform the user
        session()->flash('success', 'RIS downloaded and requests marked as released');
        
        // Return the file for download
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing RIS download: ' . $e->getMessage());
            return back()->with('error', 'Error processing RIS download: ' . $e->getMessage());
        }
    }    /**
     * Sanitize values for Excel to prevent formula errors
     */
    private function sanitizeExcelValue($value)
    {
        if ($value === null) {
            return '';
        }
        
        $value = (string) $value;
        
        // Prevent formula injection by prefixing problematic characters
        if (strlen($value) > 0) {
            // Check for formula-like characters at the beginning
            if (in_array($value[0], ['=', '+', '-', '@', '\''])) {
                $value = "'" . $value; // Add a single quote to prevent Excel treating it as a formula
            }
            
            // Check for strings that might cause CSV injection or special interpretation
            if (substr($value, 0, 1) === '=' || substr($value, 0, 1) === '@') {
                $value = "'" . $value;
            }
        }
        
        return $value;
    }

    /**
     * Sanitize numeric values for Excel
     */
    private function sanitizeNumericValue($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        
        return is_numeric($value) ? (float) $value : 0;
    }

    /**
     * Display print-ready RIS view
     */    public function printRIS($stockNumber, Request $request)
    {
        try {
            $batchKey = $request->get('batchKey');
            $autoPrint = $request->get('autoprint', false);
            
            if ($batchKey) {
                // Handle batch printing - get all requests in the batch
                $allApprovedRequests = Requests::with('user')->where('status', 'Approved')->get();
                
                // Recreate the same grouping logic as in approved.blade.php
                $normalizedRequests = $allApprovedRequests->map(function($req) {
                    $req->normalizedPurpose = strtolower(trim($req->purpose ?? $req->Purpose ?? 'office use'));
                    $timestamp = $req->created_at ?? now();
                    $minutes = floor($timestamp->minute / 10) * 10;
                    $batchTimestamp = $timestamp->format('Y-m-d_H_') . $minutes;
                    $req->batchKey = $req->normalizedPurpose . '_' . $batchTimestamp;
                    return $req;
                });
                
                // Get all requests that match the batch key
                $batchRequests = $normalizedRequests->where('batchKey', $batchKey);
                
                if ($batchRequests->isEmpty()) {
                    // Fallback to single request if batch is empty and stockNumber is provided
                    if ($stockNumber) {
                        $requests = Requests::with('user')->where('StockNumber', $stockNumber)->firstOrFail();
                    } else {
                        return redirect()->back()->with('error', 'No requests found for the specified batch');
                    }
                } else {
                    // Convert to array for the view
                    $requests = $batchRequests->values()->all();
                }
            } else if ($stockNumber) {
                // Handle single request printing
                $requests = Requests::with('user')->where('StockNumber', $stockNumber)->firstOrFail();            } else {
                // Neither batch key nor stock number provided
                return redirect()->back()->with('error', 'Missing required parameters for printing');
            }
            
            return view('requests.printRIS', [
                'requests' => $requests,
                'autoPrint' => $autoPrint
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading print RIS: ' . $e->getMessage());
            return back()->with('error', 'Error loading print RIS: ' . $e->getMessage());
        }
    }    /**
     * Deny a group of requests based on batch key.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function denyGroup(Request $request)
    {
        // Enhanced logging for debugging
        Log::info('Deny Group Request Started', [
            'request_data' => $request->all(),
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->role ?? 'unknown'
        ]);

        // Validate the incoming request
        $validated = $request->validate([
            'batchKey' => 'required|string',
        ]);
        
        $batchKey = $validated['batchKey'];
        Log::info("Processing deny request for batch key: {$batchKey}");
        
        try {
            DB::beginTransaction();
            
            // Get the parts from the batch key
            $parts = explode('_', $batchKey);
            
            // The first part is the normalized purpose
            $normalizedPurpose = $parts[0];
            
            // The rest is the timestamp
            array_shift($parts);
            $batchTimestamp = implode('_', $parts);
            
            // Fix: Use the correct model and query approach
            $timestamp = \Carbon\Carbon::createFromFormat('Y-m-d_H_i', $batchTimestamp);
            $startTime = $timestamp->copy();
            $endTime = $timestamp->copy()->addMinutes(10);
            
            // Find requests with matching normalized purpose and timestamp
            $requests = Requests::where(DB::raw("LOWER(TRIM(Purpose))"), $normalizedPurpose)
                          ->whereBetween('created_at', [$startTime, $endTime])
                          ->get();
        
            if ($requests->isEmpty()) {
                Log::warning("No requests found for batch key: {$batchKey}");
                return redirect()->back()->with('error', 'No requests found matching this group.');
            }
            
            Log::info("Found {$requests->count()} requests to deny for batch: {$batchKey}");
              // Get user info for updated_by field
            $user = Auth::user();
            $userRole = ucfirst($user->role); // Capitalize first letter
            $userName = trim("{$user->First_Name} {$user->Last_Name}"); 
            $updatedBy = "{$userRole} - {$userName}";
            
            // Update all requests in this group to 'Disapproved' status
            $affectedUsers = [];
            foreach ($requests as $request) {
                $oldStatus = $request->status;
                $request->status = 'Disapproved';
                $request->updated_by = $updatedBy;
                $request->updated_at = now();
                $request->save();

                // Log the denial action
                AdminAuditService::logRequestDenied($request);

                Log::info("Updated request ID {$request->id} from '{$oldStatus}' to 'Disapproved'");
                
                // Collect affected users for batch notification
                if ($request->user) {
                    $userId = $request->user->id;
                    if (!isset($affectedUsers[$userId])) {
                        $affectedUsers[$userId] = [
                            'user' => $request->user,
                            'requests' => []
                        ];
                    }
                    $affectedUsers[$userId]['requests'][] = $request;
                }
                
                // Also update any related items
                if ($request->items) {
                    foreach ($request->items as $item) {
                        $item->status = 'Disapproved';
                        $item->save();
                    }
                }
            }
            
            // Send batch notifications to affected users
            foreach ($affectedUsers as $userData) {
                $user = $userData['user'];
                $userRequests = $userData['requests'];
                $requestCount = count($userRequests);
                
                try {
                    // Create batch identifier
                    $timestamp = now()->format('Y-m-d_H:i:s');
                    $batchId = md5('denial_' . $batchKey . '_' . $timestamp . '_' . $user->id);
                    
                    $firstRequest = $userRequests[0];
                    
                    // Create a single batch notification for this user (removed duplicate email notification)
                    $batchRequest = clone $firstRequest;
                    $batchRequest->supply_name = "Batch of {$requestCount} items: " . $firstRequest->supply_name;
                    
                    // Add batch metadata
                    $batchRequest->batch_id = $batchId;
                    $batchRequest->batch_request_ids = collect($userRequests)->pluck('id')->toArray();
                    $batchRequest->batch_count = $requestCount;
                    
                    // Send SINGLE batch email notification (consolidated)
                    $user->notify(new \App\Notifications\BatchRequestDenied($batchRequest, $userRequests, 'Request denied by administrator', $batchId));
                    
                    // Create ONLY ONE database notification using the service
                    $this->notificationService->createRequestNotification($batchRequest, 'request_denied');
                    
                    Log::info("Batch denial notification created", [
                        'batch_id' => $batchId,
                        'request_count' => $requestCount,
                        'request_ids' => collect($userRequests)->pluck('id')->toArray(),
                        'user_email' => $user->email,
                        'email_sent' => 'batch_email'
                    ]);
                } catch (\Exception $notifyError) {
                    // Log notification error but don't fail the denial
                    Log::error('Failed to send batch denial notification for user ID ' . $user->id . ': ' . $notifyError->getMessage());
                }
            }
            
            DB::commit();
            
            // Enhanced success logging for debugging
            Log::info("Successfully denied batch: {$batchKey}", [
                'requests_updated' => $requests->count(),
                'affected_users' => count($affectedUsers),
                'redirect_route' => 'requests.disapproved'
            ]);
            
            // Add flash message for extra visibility
            session()->flash('deny_success', "Batch '{$batchKey}' with {$requests->count()} requests has been denied successfully.");
            
            return redirect()->route('requests.disapproved')
                            ->with('success', 'Request group has been denied successfully and email notifications sent to users.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Enhanced error logging
            Log::error("Failed to deny batch: {$batchKey}", [
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while denying the request group: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mark request(s) as released (for print functionality)
     * Similar to downloadRIS but just updates status without generating a file
     */
    public function markAsReleased($stockNumber)
    {
        try {
            DB::beginTransaction();
            
            // Get the primary request
            $primaryRequest = Requests::where('StockNumber', $stockNumber)->firstOrFail();
            
            // Find all related requests in the same batch using the same grouping logic
            $allApprovedRequests = Requests::where('status', 'Approved')->get();
            
            $normalizedRequests = $allApprovedRequests->map(function($request) {
                $request->normalizedPurpose = strtolower(trim($request->purpose ?? $request->Purpose ?? 'office use'));
                $timestamp = $request->created_at ?? now();
                $minutes = floor($timestamp->minute / 10) * 10;
                $batchTimestamp = $timestamp->format('Y-m-d_H_') . $minutes;
                $request->batchKey = $request->normalizedPurpose . '_' . $batchTimestamp;
                return $request;
            });
        
            // Get the batch key for the primary request
            $primaryBatchKey = $normalizedRequests->where('StockNumber', $stockNumber)->first()->batchKey ?? null;
            
            // Get all requests in the same batch
            $batchRequests = $normalizedRequests->where('batchKey', $primaryBatchKey);
            
            // If no batch found, fallback to single request
            if ($batchRequests->isEmpty()) {
                $batchRequests = collect([$primaryRequest]);
            }
            
            // Update status to 'released' for all requests in the batch
            foreach ($batchRequests as $request) {
                $requestModel = Requests::find($request->id);
                if ($requestModel && $requestModel->status === 'Approved') {
                    $requestModel->status = 'released';
                    $requestModel->updated_at = now();
                    $requestModel->save();
                    
                    // Log the release action in audit trail
                    AdminAuditService::logRequestReleased($requestModel);
                    
                    // Log the status change
                    Log::info('Request ID ' . $request->id . ' marked as released (via print)');
                }
            }
            
            DB::commit();
            
            // Add a session flash message to inform the user
            session()->flash('success', 'Requests marked as released');
            
            // Return a success response
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking requests as released: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'error' => 'Error marking requests as released: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the status of a request and prepare for download
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusAndDownload(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'requestIds' => 'array',
                'requestIds.*' => 'integer',
                'stockNumber' => 'nullable|string',
                'batchKey' => 'nullable|string', 
                'purpose' => 'nullable|string',
                'newStatus' => 'required|in:released'
            ]);

            $updatedRequests = [];

            // If we have specific request IDs, update those
            if (!empty($validated['requestIds'])) {
                $supplyRequests = Requests::whereIn('id', $validated['requestIds'])->get();
                
                if ($supplyRequests->isEmpty()) {
                    return response()->json(['success' => false, 'message' => 'No requests found with provided IDs'], 404);
                }
                
                // Update all requests in the batch
                foreach ($supplyRequests as $supplyRequest) {
                    $supplyRequest->status = $validated['newStatus'];
                    $supplyRequest->save();
                    
                    // Log the release action in audit trail if changing to released
                    if ($validated['newStatus'] === 'released') {
                        AdminAuditService::logRequestReleased($supplyRequest);
                    }
                    
                    $updatedRequests[] = $supplyRequest->id;
                }
                
                $logMessage = 'Batch of requests updated to released status';
                $stockNumber = $supplyRequests->first()->StockNumber;
                
            } else if (!empty($validated['stockNumber'])) {
                // Fallback: If no request IDs, update by stock number (legacy support)
                $supplyRequest = Requests::where('StockNumber', $validated['stockNumber'])->first();
                
                if (!$supplyRequest) {
                    return response()->json(['success' => false, 'message' => 'Request not found'], 404);
                }

                $supplyRequest->status = $validated['newStatus'];
                $supplyRequest->save();
                
                // Log the release action in audit trail if changing to released
                if ($validated['newStatus'] === 'released') {
                    AdminAuditService::logRequestReleased($supplyRequest);
                }
                
                $updatedRequests[] = $supplyRequest->id;
                $logMessage = 'Single request updated to released status';
                $stockNumber = $validated['stockNumber'];
                
            } else {
                return response()->json(['success' => false, 'message' => 'No request identifiers provided'], 400);
            }

            // Log the action
            $user = auth()->user();
            Log::info($logMessage, [
                'user_id' => $user->id,
                'user_name' => $user->First_Name . ' ' . $user->Last_Name,
                'stock_number' => $stockNumber,
                'updated_request_ids' => $updatedRequests,
                'batch_key' => $validated['batchKey'] ?? null,
                'purpose' => $validated['purpose'] ?? null,
                'old_status' => 'Approved',
                'new_status' => $validated['newStatus'],
                'total_updated' => count($updatedRequests)
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Successfully updated ' . count($updatedRequests) . ' request(s)',
                'updated_count' => count($updatedRequests),
                'updated_ids' => $updatedRequests
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating request status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API endpoint to check for real-time updates on released requests
     * Returns JSON response with latest released requests data
     */
    public function checkReleasedUpdates(Request $request)
    {
        try {
            // Get the last check timestamp from the request
            $lastCheck = $request->query('last_check', null);
            
            $query = Requests::with('user')->where('status', 'released');
            
            // If we have a last check timestamp, only get requests updated after that
            if ($lastCheck) {
                $query->where('updated_at', '>', $lastCheck);
            }
            
            $newReleased = $query->orderBy('updated_at', 'desc')->get();
            
            // Process the data similar to the main released method
            $normalizedRequests = $newReleased->map(function($request) {
                $request->normalizedPurpose = strtolower(trim($request->purpose ?? $request->Purpose ?? 'office use'));
                
                $timestamp = $request->created_at ?? now();
                $minutes = floor($timestamp->minute / 10) * 10;
                $batchTimestamp = $timestamp->format('Y-m-d_H_') . $minutes;
                
                $request->batchKey = $request->normalizedPurpose . '_' . $batchTimestamp;
                $request->batchTime = $timestamp;
                
                return $request;
            });
            
            $groupedRequests = $normalizedRequests->groupBy('batchKey');
            
            return response()->json([
                'success' => true,
                'hasUpdates' => $newReleased->isNotEmpty(),
                'count' => $newReleased->count(),
                'timestamp' => now()->toISOString(),
                'requests' => $groupedRequests
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error checking for updates: ' . $e->getMessage()
            ], 500);
        }
    }
}