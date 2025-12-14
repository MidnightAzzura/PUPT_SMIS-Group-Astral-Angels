<?php

namespace App\Http\Controllers;
use App\Models\Requests;
use App\Models\AdminAuditLog;
use App\Models\ArchivedRecord;
use App\Models\User;
use App\Services\AdminAuditService;
use App\Exports\LogsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Exports\AuditLogsExport;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Logs

    public function Logs(Request $request){
        // Get current user role
        $userRole = strtolower(trim(auth()->user()->role));
        $userOffice = auth()->user()->Office;
        
        // Filter parameters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $action = $request->input('status');
        $office = $request->input('office');
        $logType = $request->input('log_type', 'requests'); // Default to requests
        $adminId = $request->input('admin_id');
        $actionType = $request->input('action_type');

        // Get regular request logs
        $requestQuery = Requests::with('user');
        
        if ($startDate && $endDate) {
            $requestQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        
        if ($action && $action !== 'all') {
            $requestQuery->where('status', $action);
        }

        // Apply office filter for non-admin/non-superadmin users BEFORE pagination
        if (!in_array($userRole, ['superadmin', 'admin'])) {
            $requestQuery->whereHas('user', function($q) use ($userOffice) {
                $q->where('Office', $userOffice);
            });
        } else {
            // For SuperAdmin/Admin: Apply office filter if provided in request
            if ($office && $office !== 'all') {
                $requestQuery->whereHas('user', function($q) use ($office) {
                    $q->where('Office', $office);
                });
            }
        }

        // Get audit logs for SuperAdmin actions
        $auditQuery = AdminAuditLog::with('admin');
        
        if ($startDate && $endDate) {
            $auditQuery->whereBetween('performed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        
        if ($actionType && $actionType !== 'all') {
            $auditQuery->where('action_type', $actionType);
        }
        
        if ($adminId && $adminId !== 'all') {
            $auditQuery->where('admin_id', $adminId);
        }

        // Get distinct statuses and offices for filter dropdowns
        $actions = Requests::distinct()->pluck('status')->toArray();
        
        // Get ALL unique offices from requests (for SuperAdmin/Admin dropdown)
        $allOffices = [];
        if (in_array($userRole, ['superadmin', 'admin'])) {
            $allOffices = Requests::with('user')
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
        
        // Set offices for view (single office for regular users)
        $offices = in_array($userRole, ['superadmin', 'admin']) ? $allOffices : [$userOffice];
            
        // Get admin action types for filter
        $actionTypes = AdminAuditLog::distinct()->pluck('action_type')->toArray();
        
        // Get all admins for filter
        $admins = User::whereIn('role', ['SuperAdmin', 'Admin'])->get();

        // Fetch the appropriate logs based on log type with pagination
        if ($logType === 'audit') {
            $logs = $auditQuery->orderBy('performed_at', 'desc')->paginate(20);
        } else {
            // Default to request logs
            $logs = $requestQuery->orderBy('created_at', 'desc')->paginate(20);
        }
        
        // Pass the selected office to the view for maintaining filter state
        $selectedOffice = $office ?? null;

        return view('reports', compact(
            'logs', 'startDate', 'endDate', 'action', 'actions', 
            'office', 'offices', 'logType', 'actionTypes', 'actionType',
            'admins', 'adminId', 'selectedOffice', 'allOffices'
        ));
    }

    // Export requests to Excel
    public function exportLogs(Request $request) {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $action    = $request->input('status'); // for request logs
        $logType   = $request->input('log_type', 'requests');
        $actionType = $request->input('action_type'); // for audit logs
        $adminId    = $request->input('admin_id');     // for audit logs

        // NEW: handle SuperAdmin audit logs export to mirror what's on screen
        if ($logType === 'audit') {
            $filename = 'superadmin_audit_logs';
            if ($actionType && $actionType !== 'all') {
                $filename .= '_' . strtolower($actionType);
            }
            if ($adminId && $adminId !== 'all') {
                $filename .= '_admin_' . $adminId;
            }
            if ($startDate && $endDate) {
                $filename .= '_' . $startDate . '_to_' . $endDate;
            }
            $filename .= '.xlsx';

            return Excel::download(
                new AuditLogsExport($startDate, $endDate, $actionType, $adminId),
                $filename
            );
        }

        // Default/unchanged behavior: export request logs
        $filename = 'supply_requests';
        if ($action && $action !== 'all') {
            $filename .= '_' . $action;
        }
        if ($startDate && $endDate) {
            $filename .= '_' . $startDate . '_to_' . $endDate;
        }
        $filename .= '.xlsx';

        return Excel::download(new LogsExport($startDate, $endDate, $action), $filename);
    }

    //delete requests only for the admin

    public function deleteLogs(){

        if (!in_array(strtolower(auth()->user()->role), ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized Access');
        }

        try {
            // Check if there are any records to delete
            $deletedCount = Requests::count();
            
            if ($deletedCount === 0) {
                return redirect()->route('reports')->with('warning', 'No records found to delete.');
            }
            
            // Enable actual deletion of all records
            Requests::truncate();
            
            return redirect()->route('reports')->with('success', "Successfully deleted all {$deletedCount} records from the database.");
        } catch (\Exception $e) {
            return redirect()->route('reports')->with('error', 'An error occurred while deleting all records: ' . $e->getMessage());
        }
    }

    // Delete filtered requests - requires admin role
    public function deleteFiltered(Request $request) {
        // Check if user has admin privileges
        if (!in_array(strtolower(auth()->user()->role), ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized Access');
        }

        // Validate the request
        $request->validate([
            'delete_start_date' => 'nullable|date',
            'delete_end_date' => 'nullable|date|after_or_equal:delete_start_date',
            'delete_status' => 'required|string'
        ]);

        $startDate = $request->input('delete_start_date');
        $endDate = $request->input('delete_end_date');
        $status = $request->input('delete_status');

        // Build the query for deletion
        $query = Requests::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $query->where('created_at', '>=', $startDate . ' 00:00:00');
        } elseif ($endDate) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Count records that will be deleted
        $recordCount = $query->count();

        if ($recordCount === 0) {
            return redirect()->route('reports')->with('warning', 'No records found matching the specified criteria.');
        }

        // Enable actual deletion
        try {
            $deletedCount = $query->delete();
            
            if ($deletedCount > 0) {
                return redirect()->route('reports')->with('success', "Successfully deleted {$deletedCount} records.");
            } else {
                return redirect()->route('reports')->with('warning', 'No records were deleted.');
            }
        } catch (\Exception $e) {
            return redirect()->route('reports')->with('error', 'An error occurred while deleting records: ' . $e->getMessage());
        }
    }

    /**
     * Get all archived records for the modal display
     */
    public function getArchivedRecords(Request $request)
    {
        // Check if user has admin privileges
        if (!in_array(strtolower(auth()->user()->role), ['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
        }

        try {
            // Paginate archived records 20 per page
            $perPage = 20;
            $page = $request->input('page', 1);

            $paginator = ArchivedRecord::with('archivedBy')
                ->orderBy('archived_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Transform items for frontend
            $transformed = $paginator->getCollection()->map(function ($record) {
                $archivedByName = 'Unknown';
                if ($record->archivedBy) {
                    $archivedByName = trim((($record->archivedBy->First_Name ?? $record->archivedBy->first_name ?? '') . ' ' . ($record->archivedBy->Last_Name ?? $record->archivedBy->last_name ?? '')));
                    if ($archivedByName === '') {
                        $archivedByName = ($record->archivedBy->name ?? 'Unknown');
                    }
                }

                return [
                    'id' => $record->id,
                    'type' => $record->record_type,
                    'reference' => $record->reference,
                    'description' => $record->description,
                    'archived_at' => optional($record->archived_at)->toDateTimeString(),
                    'archived_by' => $archivedByName,
                    'record_data' => $record->record_data
                ];
            });

            // set transformed collection back on paginator
            $paginator->setCollection($transformed);

            return response()->json([
                'success' => true,
                'records' => $paginator->items(),
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load archived records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive filtered records instead of deleting them
     */
    public function archiveFiltered(Request $request)
    {
        // Check if user has admin privileges
        if (!in_array(strtolower(auth()->user()->role), ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized Access');
        }

        // Get filter parameters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');
        $office = $request->input('office');
        $logType = $request->input('log_type', 'requests');
        $actionType = $request->input('action_type');
        $adminId = $request->input('admin_id');

        DB::beginTransaction();
        
        try {
            $archivedCount = 0;

            if ($logType === 'requests') {
                // Archive request records
                $requestQuery = Requests::with('user');
                
                if ($startDate && $endDate) {
                    $requestQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
                
                if ($status && $status !== 'all') {
                    $requestQuery->where('status', $status);
                }
                
                if ($office && $office !== 'all') {
                    $requestQuery->whereHas('user', function($q) use ($office) {
                        $q->where('office', $office);
                    });
                }

                $requests = $requestQuery->get();
                
                foreach ($requests as $request) {
                    ArchivedRecord::create([
                        'record_type' => 'requests',
                        'original_id' => $request->id,
                        'record_data' => $request->toArray(),
                        'reference' => $request->StockNumber,
                        'description' => "Request for {$request->supply_name} (Qty: {$request->supply_quantity}) - Status: {$request->status}",
                        'archived_by' => auth()->id(),
                        'archived_at' => now()
                    ]);
                }
                
                // Delete original records after archiving
                $archivedCount = $requestQuery->delete();
                
            } elseif ($logType === 'audit') {
                // Archive audit log records
                $auditQuery = AdminAuditLog::with('admin');
                
                if ($startDate && $endDate) {
                    $auditQuery->whereBetween('performed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
                
                if ($actionType && $actionType !== 'all') {
                    $auditQuery->where('action_type', $actionType);
                }
                
                if ($adminId && $adminId !== 'all') {
                    $auditQuery->where('admin_id', $adminId);
                }

                $auditLogs = $auditQuery->get();
                
                foreach ($auditLogs as $log) {
                    // build readable admin name (support different field casings)
                    $adminName = 'Unknown Admin';
                    if ($log->admin) {
                        $adminName = trim((($log->admin->First_Name ?? $log->admin->first_name ?? '') . ' ' . ($log->admin->Last_Name ?? $log->admin->last_name ?? '')));
                        if ($adminName === '') {
                            $adminName = ($log->admin->name ?? 'Unknown Admin');
                        }
                    } elseif (!empty($log->admin_name)) {
                        $adminName = $log->admin_name;
                    }

                    ArchivedRecord::create([
                        'record_type' => 'audit',
                        'original_id' => $log->id,
                        'record_data' => $log->toArray(),
                        'reference' => $adminName,
                        'description' => "Admin Action: {$log->action_type} - {$log->details}",
                        'archived_by' => auth()->id(),
                        'archived_at' => now()
                    ]);
                 }
                 
                 // Delete original records after archiving
                 $archivedCount = $auditQuery->delete();
             }

            if ($archivedCount === 0) {
                DB::rollBack();
                return redirect()->route('reports')->with('warning', 'No records found matching the specified criteria.');
            }

            DB::commit();
            return redirect()->route('reports')->with('success', "Successfully archived {$archivedCount} records. They can be restored from the Archive History.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('reports')->with('error', 'An error occurred while archiving records: ' . $e->getMessage());
        }
    }

    /**
     * Restore a single archived record
     */
    public function restoreRecord(Request $request)
    {
        // Check if user has admin privileges
        if (!in_array(strtolower(auth()->user()->role), ['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $request->validate([
            'record_id' => 'required|integer|exists:archived_records,id',
            'record_type' => 'required|string|in:requests,audit'
        ]);

        DB::beginTransaction();
        
        try {
            $archivedRecord = ArchivedRecord::findOrFail($request->record_id);
            
            if ($archivedRecord->record_type === 'requests') {
                // Restore to requests table
                $requestData = $archivedRecord->record_data;
                unset($requestData['id']); // Remove the old ID to avoid conflicts
                
                Requests::create($requestData);
                
            } elseif ($archivedRecord->record_type === 'audit') {
                // Restore to admin_audit_logs table
                $auditData = $archivedRecord->record_data;
                unset($auditData['id']); // Remove the old ID to avoid conflicts
                
                AdminAuditLog::create($auditData);
            }

            // Delete from archived records
            $archivedRecord->delete();
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Record restored successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore multiple archived records
     */
    public function restoreMultipleRecords(Request $request)
    {
        // Check if user has admin privileges
        if (!in_array(strtolower(auth()->user()->role), ['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $request->validate([
            'records' => 'required|array',
            'records.*.id' => 'required|integer|exists:archived_records,id',
            'records.*.type' => 'required|string|in:requests,audit'
        ]);

        DB::beginTransaction();
        
        try {
            $restoredCount = 0;
            
            foreach ($request->records as $recordInfo) {
                $archivedRecord = ArchivedRecord::findOrFail($recordInfo['id']);
                
                if ($archivedRecord->record_type === 'requests') {
                    // Restore to requests table
                    $requestData = $archivedRecord->record_data;
                    unset($requestData['id']); // Remove the old ID to avoid conflicts
                    
                    Requests::create($requestData);
                    
                } elseif ($archivedRecord->record_type === 'audit') {
                    // Restore to admin_audit_logs table
                    $auditData = $archivedRecord->record_data;
                    unset($auditData['id']); // Remove the old ID to avoid conflicts
                    
                    AdminAuditLog::create($auditData);
                }

                // Delete from archived records
                $archivedRecord->delete();
                $restoredCount++;
            }
            
            DB::commit();
            return response()->json([
                'success' => true,
                'restored_count' => $restoredCount,
                'message' => "{$restoredCount} record(s) restored successfully!"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore records: ' . $e->getMessage()
            ], 500);
        }
    }
}
