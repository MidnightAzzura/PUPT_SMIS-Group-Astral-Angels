<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supply;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\AdminAuditLog; // Updated to correct model name
use App\Models\Requests as SupplyRequest; // Updated to correct model name

class SupplyController extends Controller
{
    public function index(Request $request)
    {
        $query = Supply::query();

        // Apply filters if needed (numeric-safe via CAST)
        if ($request->has('filter')) {
            if ($request->filter === 'low') {
                // 1–5 only
                $query->whereRaw('CAST(Quantity AS SIGNED) BETWEEN 1 AND 5');
            } elseif ($request->filter === 'no') {
                // exactly 0
                $query->whereRaw('CAST(Quantity AS SIGNED) = 0');
            } elseif ($request -> filter === 'available') {

                $query->whereRaw('CAST(Quantity AS SIGNED) > 5');

            }        
        }

        // Apply search across ItemDescription and StockNumber
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function($q) use ($term) {
                $q->where('ItemDescription', 'like', '%' . $term . '%')
                  ->orWhere('StockNumber', 'like', '%' . $term . '%');
            });
        }

        // NEW: Apply category filter (support either 'Category' or 'category' DB column)
        if ($request->filled('category')) {
            $cat = trim($request->category);
            // use COALESCE to be tolerant to either column name; trim to avoid whitespace mismatch
            $query->whereRaw("TRIM(COALESCE(`Category`, `category`, '')) = ?", [$cat]);
        }

        // Stable ordering for consistent pagination
        $query->orderBy('updated_at', 'desc');

        // Paginate and preserve current query params
        // SMALL CHANGE: show a few more items per page for better reflection in UI
        $filtered_supplies = $query->paginate(5)->appends($request->query());
        
        // 6. Get other data needed for the dashboard (Totals, Categories, etc.)
        $categories = Supply::distinct()->pluck('Category')->sort();
 
        // Calculate additional variables
        $total_products = Supply::count();
        $total_stock = Supply::sum('Quantity');
        // numeric-safe out of stock count
        $out_of_stock = Supply::whereRaw('CAST(Quantity AS SIGNED) = 0')->count();
        $suppliers = 5; // Replace with actual logic if you have a suppliers table

        // NEW: fetch distinct categories from existing supplies (support both 'Category' and 'category' columns)
        // Robust: coalesce both possible columns, trim, exclude empty values and return distinct ordered list
        try {
            $categories = Supply::select(DB::raw("TRIM(COALESCE(`Category`, `category`, '')) AS cat"))
                                 ->whereRaw("COALESCE(`Category`, `category`, '') <> ''")
                                 ->distinct()
                                 ->orderBy('cat')
                                 ->pluck('cat')
                                 ->map(function($v){ return is_string($v) ? trim($v) : $v; });
        } catch (\Throwable $e) {
            \Log::warning('Failed to load categories via COALESCE: ' . $e->getMessage());
            $categories = collect();
        }

        // Return the view with the supplies and additional data
        return view('Admin.admin', compact('filtered_supplies', 'total_products', 'total_stock', 'out_of_stock', 'suppliers', 'categories'));
    }

    /**
     * Check if a stock number exists and return its details
     */
    public function checkStockNumber(Request $request)
    {
        $stockNumber = $request->input('stockNumber');
        $supply = Supply::where('StockNumber', $stockNumber)->first();
        
        if ($supply) {
            // return both keys (Category and category) to be forgiving for various clients
            return response()->json([
                'exists' => true,
                'itemDescription' => $supply->ItemDescription,
                'quantity' => $supply->Quantity,
                'unit' => $supply->Unit,
                'Category' => $supply->Category ?? $supply->category ?? null
            ]);
        }
        
        return response()->json(['exists' => false]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'StockNumber' => 'required|string|max:255|regex:/^[A-Za-z0-9\s.,-]+$/',
                'ItemDescription' => 'required|string|max:255|regex:/^[A-Za-z0-9\s.,()\-\"\/]+$/',
                'Unit' => ['required','string','max:20','regex:/^[A-Za-z0-9\s\-\/]+$/'],
                'Quantity' => 'required|integer|min:0',
                'Category' => 'required|string|max:100', // ADDED: validate category
            ], [
                'StockNumber.required' => 'The stock number field is required.',
                'ItemDescription.required' => 'The description field is required.',
                'Unit.regex' => 'The unit may only contain letters, numbers, spaces, "-" or "/" and be up to 20 characters.',
            ]);
            
            // Check if supply with this stock number already exists
            $existingSupply = Supply::where('StockNumber', $request->StockNumber)->first();
            if ($existingSupply) {
                // Store original data for audit log
                $originalData = [
                    'StockNumber' => $existingSupply->StockNumber,
                    'ItemDescription' => $existingSupply->ItemDescription,
                    'Unit' => $existingSupply->Unit,
                    'Quantity' => $existingSupply->Quantity,
                    'Status' => $existingSupply->Status,
                    'Category' => $existingSupply->Category
                ];
                
                // Update quantity of existing supply instead of creating new
                $existingSupply->Quantity += $request->Quantity;
                // persist category into both possible attribute keys to be safe
                if ($request->filled('Category')) {
                    $val = trim($request->Category);
                    $existingSupply->setAttribute('Category', $val);
                }
                // Set status: 0 = Out of Stock, 1–5 = Low Stock, >5 = Available
                $existingSupply->Status = $existingSupply->Quantity == 0
                    ? 'Out of Stock'
                    : ($existingSupply->Quantity <= 5 ? 'Low Stock' : 'Available');
                $existingSupply->save();

                // Create audit log for quantity combination
                try {
                    AdminAuditLog::create([
                        'admin_id' => auth()->id(),
                        'admin_name' => auth()->user()->First_Name . ' ' . auth()->user()->Last_Name,
                        'admin_role' => auth()->user()->role,
                        'action_type' => 'UPDATE',
                        'target_type' => 'Supply',
                        'target_id' => $existingSupply->id,
                        'target_name' => $existingSupply->StockNumber,
                        'old_values' => $originalData,
                        'new_values' => [
                            'StockNumber' => $existingSupply->StockNumber,
                            'ItemDescription' => $existingSupply->ItemDescription,
                            'Unit' => $existingSupply->Unit,
                            'Quantity' => $existingSupply->Quantity,
                            'Status' => $existingSupply->Status
                        ],
                        'description' => "Added {$request->Quantity} units to existing supply: {$existingSupply->StockNumber} - {$existingSupply->ItemDescription}",
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'performed_at' => now()
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to create audit log for supply quantity addition: ' . $e->getMessage());
                }

        
                
                return redirect()->back()->with('success', 'Supply quantity updated successfully! New quantity: ' . $existingSupply->Quantity);
            }
            
            // Create new supply if it doesn't exist
            $supply = new Supply();
            $supply->StockNumber = $request->StockNumber;
            $supply->ItemDescription = $request->ItemDescription;
            $supply->Unit = trim($request->Unit);
            // persist category on both keys
            $catVal = trim($request->Category ?? '');
            $supply->setAttribute('Category', $catVal);

            // Set status: 0 = Out of Stock, 1–5 = Low Stock, >5 = Available
            $supply->Status = $request->Quantity == 0
                ? 'Out of Stock'
                : ($request->Quantity <= 5 ? 'Low Stock' : 'Available');
            $supply->Quantity = $request->Quantity;
            $supply->save();

            // Create audit log for new supply creation
            try {
                AdminAuditLog::create([
                    'admin_id' => auth()->id(),
                    'admin_name' => auth()->user()->First_Name . ' ' . auth()->user()->Last_Name,
                    'admin_role' => auth()->user()->role,
                    'action_type' => 'CREATE',
                    'target_type' => 'Supply',
                    'target_id' => $supply->id,
                    'target_name' => $supply->StockNumber,
                    'old_values' => null,
                    'new_values' => [
                        'StockNumber' => $supply->StockNumber,
                        'ItemDescription' => $supply->ItemDescription,
                        'Unit' => $supply->Unit,
                        'Quantity' => $supply->Quantity,
                        'Status' => $supply->Status
                    ],
                    'description' => "Created new supply item: {$supply->StockNumber} - {$supply->ItemDescription}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'performed_at' => now()
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create audit log for new supply: ' . $e->getMessage());
            }

        

            return redirect()->back()->with('success', 'Supply added successfully!');
        } catch (Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to add supply. ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $supply = Supply::findOrFail($id);
        
        // Store original data for audit log
        $originalData = [
            'StockNumber' => $supply->StockNumber,
            'ItemDescription' => $supply->ItemDescription,
            'Unit' => $supply->Unit,
            'Quantity' => $supply->Quantity,
            'Status' => $supply->Status
        ];
        
        // Create audit log for deletion
        try {
            AdminAuditLog::create([
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->First_Name . ' ' . auth()->user()->Last_Name,
                'admin_role' => auth()->user()->role,
                'action_type' => 'DELETE',
                'target_type' => 'Supply',
                'target_id' => $supply->id,
                'target_name' => $supply->StockNumber,
                'old_values' => $originalData,
                'new_values' => null,
                'description' => "Deleted supply item: {$supply->StockNumber} - {$supply->ItemDescription}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create audit log for supply deletion: ' . $e->getMessage());
        }
        
        $supply->delete();

        return redirect()->back()->with('success', 'Supply deleted successfully!');
    }
   public function update(Request $request, $id)
   {
       try {
           // Validate the incoming request
           $validated = $request->validate([
               'StockNumber' => 'required|string|max:255|regex:/^[A-Za-z0-9\s.,-]+$/',
               'ItemDescription' => 'required|string|max:255|regex:/^[A-Za-z0-9\s.,()\-\"\/]+$/',
               'Unit' => ['required','string','max:20','regex:/^[A-Za-z0-9\s\-\/]+$/'],
               'Quantity' => 'required|integer|min:0',
               'Category' => 'required|string|max:100', // ADDED: validate category
           ], [
               'Unit.regex' => 'The unit may only contain letters, numbers, spaces, "-" or "/" and be up to 20 characters.',
           ]);

           // Find the supply by id
           $supply = Supply::findOrFail($id);
           
           // Store original data for audit log
           $originalData = [
               'StockNumber' => $supply->StockNumber,
               'ItemDescription' => $supply->ItemDescription,
               'Unit' => $supply->Unit,
               'Quantity' => $supply->Quantity,
               'Status' => $supply->Status,
               'Category' => $supply->Category
           ];
           
           // Update the supply details
           $supply->StockNumber = $request->StockNumber;
           $supply->ItemDescription = $request->ItemDescription;
           $supply->Unit = trim($request->Unit);
           // persist category to both attribute names
           $catVal = trim($request->Category ?? '');
           $supply->setAttribute('Category', $catVal);

           $supply->Quantity = $request->Quantity;
           // Set status: 0 = Out of Stock, 1–5 = Low Stock, >5 = Available
           $supply->Status = $request->Quantity == 0
               ? 'Out of Stock'
               : ($request->Quantity <= 5 ? 'Low Stock' : 'Available');
           $supply->save();

           // Create audit log for the update
           try {
               AdminAuditLog::create([
                   'admin_id' => auth()->id(),
                   'admin_name' => auth()->user()->First_Name . ' ' . auth()->user()->Last_Name,
                   'admin_role' => auth()->user()->role,
                   'action_type' => 'UPDATE',
                   'target_type' => 'Supply',
                   'target_id' => $supply->id,
                   'target_name' => $supply->StockNumber,
                   'old_values' => $originalData,
                   'new_values' => [
                       'StockNumber' => $supply->StockNumber,
                       'ItemDescription' => $supply->ItemDescription,
                       'Unit' => $supply->Unit,
                       'Quantity' => $supply->Quantity,
                       'Status' => $supply->Status
                   ],
                   'description' => "Updated supply item: {$supply->StockNumber} - {$supply->ItemDescription}",
                   'ip_address' => $request->ip(),
                   'user_agent' => $request->userAgent(),
                   'performed_at' => now()
               ]);
           } catch (\Exception $e) {
               Log::warning('Failed to create audit log for supply update: ' . $e->getMessage());
           }


           // Return JSON for AJAX requests to support history modal refresh
           if ($request->expectsJson() || $request->ajax()) {
               // Return category explicitly under both keys
               $supplyArray = $supply->toArray();
               $supplyArray['category'] = $supplyArray['category'] ?? ($supplyArray['Category'] ?? null);
               $supplyArray['Category'] = $supplyArray['Category'] ?? ($supplyArray['category'] ?? null);

               return response()->json([
                   'success' => true,
                   'message' => 'Supply updated successfully',
                   'supply' => $supplyArray
               ]);
           }

           return redirect()->route('Admin.admin')->with('success', 'Supply updated successfully');
       } catch (Exception $e) {
           if ($request->expectsJson() || $request->ajax()) {
               return response()->json([
                   'success' => false,
                   'message' => 'Failed to update supply: ' . $e->getMessage()
               ], 500);
           }

           return redirect()->back()->with('error', 'Failed to update supply: ' . $e->getMessage());
       }
   }

public function getAllSupplies()
{
    try {
        // include category in the select so print/export endpoints get it
        $supplies = Supply::select('StockNumber', 'ItemDescription', 'Unit', 'Quantity', DB::raw("COALESCE(Category, category) as Category"))
                          ->get();
        
        return response()->json($supplies);
    } catch (Exception $e) {
        return response()->json(['error' => 'Failed to fetch supplies'], 500);
    }
}

/**
 * Return stock history (audit + requests) for a given stock number in JSON.
 */
public function history($stock, Request $request)
{
    try {
        $stock = trim($stock);
        if ($stock === '') {
            return response()->json(['history' => []]);
        }

        $supply = Supply::where('StockNumber', $stock)->first();
        if (!$supply) {
            return response()->json(['history' => []]);
        }

        $history = [];

        // 1. Collect audit logs referencing this stock (by target_name or description match)
        try {
            if (class_exists(AdminAuditLog::class)) {
                $auditLogs = AdminAuditLog::query()
                    ->where(function ($q) use ($stock, $supply) {
                        $q->where('target_name', $stock)
                          ->orWhere('target_name', $supply->ItemDescription)
                          ->orWhere('description', 'LIKE', "%{$stock}%")
                          ->orWhere('description', 'LIKE', "%{$supply->ItemDescription}%");
                    })
                    ->orderByDesc('performed_at')
                    ->limit(150)
                    ->get();

                foreach ($auditLogs as $log) {
                    $oldVals = $this->decodeMaybeJson($log->old_values ?? null);
                    $newVals = $this->decodeMaybeJson($log->new_values ?? null);
                    $prevQty = $oldVals['Quantity'] ?? $oldVals['quantity'] ?? null;
                    $newQty  = $newVals['Quantity'] ?? $newVals['quantity'] ?? null;
                    $history[] = [
                        'source'            => 'audit',
                        'performed_at'      => optional($log->performed_at)->toDateTimeString() ?? ($log->created_at ? $log->created_at->toDateTimeString() : null),
                        'action_type'       => $log->action_type ?? $log->action ?? 'update',
                        'prev_quantity'     => is_numeric($prevQty) ? (int)$prevQty : null,
                        'new_quantity'      => is_numeric($newQty) ? (int)$newQty : null,
                        'change'            => (is_numeric($prevQty) && is_numeric($newQty)) ? ((int)$newQty - (int)$prevQty) : null,
                        'requested_quantity'=> null,
                        'approved_quantity' => null,
                        'performed_by'      => optional($log->admin)->First_Name.' '.optional($log->admin)->Last_Name ?? ($log->admin_name ?? 'System'),
                        'details'           => $log->description ?? $log->target_name,
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Stock history audit log extraction failed: '.$e->getMessage());
        }

        // 2. Collect request records for this stock (pending/approved/released/disapproved)
        try {
            if (class_exists(SupplyRequest::class)) {
                $requests = SupplyRequest::query()
                    ->where('StockNumber', $stock)
                    ->orderByDesc('created_at')
                    ->limit(150)
                    ->get();

                foreach ($requests as $req) {
                    $approvedQty = null;
                    // Use the correct field names from Requests model
                    if (in_array(strtolower($req->status ?? ''), ['approved','released'])) {
                        $approvedQty = $req->issue_quantity ?? $req->supply_quantity ?? null;
                    }
                    $history[] = [
                        'source'            => 'request',
                        'performed_at'      => optional($req->created_at)->toDateTimeString(),
                        'action_type'       => strtolower($req->status ?? 'request'),
                        'prev_quantity'     => null,
                        'new_quantity'      => null,
                        'change'            => $approvedQty ? -1 * (int)$approvedQty : null, // negative indicates deduction
                        'requested_quantity'=> $req->supply_quantity ?? null,
                        'approved_quantity' => $approvedQty,
                        'performed_by'      => optional($req->user)->First_Name.' '.optional($req->user)->Last_Name,
                        'details'           => $req->supply_name ?? $supply->ItemDescription,
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Stock history request extraction failed: '.$e->getMessage());
        }

        // 3. Sort combined
        usort($history, function($a, $b) {
            return strcmp($b['performed_at'] ?? '', $a['performed_at'] ?? '');
        });

        return response()->json(['history' => $history]);
    } catch (\Throwable $e) {
        Log::error('Stock history error for '.$stock.': '.$e->getMessage());
        return response()->json([
            'error' => true,
            'message' => 'Server error retrieving history.'
        ], 500);
    }
}

private function decodeMaybeJson($val)
{
    if (!$val) return [];
    if (is_array($val)) return $val;
    if (is_string($val)) {
        $trim = trim($val);
        if ($trim === '' ) return [];
        try { $decoded = json_decode($trim, true); return is_array($decoded) ? $decoded : []; } catch (\Throwable $e) { return []; }
    }
    return [];
}
}