<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supply;
use App\Services\NotificationService;
use App\Models\Log;
use App\Models\Requests; // Add this import for the Requests model
use Illuminate\Support\Facades\DB; // <-- added to support category queries

class DashboardController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        // $this->middleware('auth');
        $this->notificationService = $notificationService;
    }


    public function use(Request $request)
    {
        // Load notifications from JSON file
        $notifications = $this->notificationService->load();

        // Fetch supplies from the database
        $supplies = Supply::all()->toArray();

        // Calculate dashboard statistics
        $total_products = count($supplies);
        $total_stock = array_sum(array_column($supplies, 'Quantity'));
        $out_of_stock = count(array_filter($supplies, function ($supply) {
            return $supply['Quantity'] == 0;
        }));
        
        // Calculate good stock and low stock
        $low_stock = count(array_filter($supplies, function ($supply) {
            return $supply['Quantity'] > 0 && $supply['Quantity'] <= 5;
        }));
        $good_stock = count(array_filter($supplies, function ($supply) {
            return $supply['Quantity'] > 5;
        }));
        
        // Calculate percentage statistics
        $stock_availability_percentage = ($total_products > 0) 
            ? round((($total_products - $out_of_stock) / $total_products) * 100) 
            : 0;
        
        $need_restocking_percentage = ($total_products > 0) 
            ? round(($low_stock / $total_products) * 100) 
            : 0;
        
        // Fetch recently released items for the last 7 days
        $recently_released = Requests::where('status', 'released')
            ->where('updated_at', '>=', now()->subDays(7))
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Calculate recently released percentage (released items in last 7 days vs. total items)
        $recently_released_count = $recently_released->count();
        $recently_released_percentage = ($total_products > 0) 
            ? round(($recently_released_count / $total_products) * 100) 
            : 0;
        
        // Assuming there is a 'Supplier' column (adjust if necessary)
        $suppliers = count(array_unique(array_column($supplies, 'Supplier')));

        // --- NEW: categories (coalesce Category / category) ---
        try {
            $categories = Supply::select(DB::raw("TRIM(COALESCE(`Category`, `category`, '')) AS cat"))
                                 ->whereRaw("COALESCE(`Category`, `category`, '') <> ''")
                                 ->distinct()
                                 ->orderBy('cat')
                                 ->pluck('cat')
                                 ->map(function($v){ return is_string($v) ? trim($v) : $v; });
        } catch (\Throwable $e) {
            \Log::warning('Failed to load categories for dashboard: ' . $e->getMessage());
            $categories = collect();
        }
        $selectedCategory = trim((string) $request->query('category', ''));

        // --- NEW: totals for admin.js charts/consumers ---
        $total_available = $good_stock ?? 0;
        $total_low_stock = $low_stock ?? 0;
        $total_out_of_stock = $out_of_stock ?? 0;

        // Filter supplies based on stock status and search query
        $filter = $request->query('filter', 'all');
        $search = $request->query('search', '');

        $query = Supply::query();

        // Apply filters (numeric-safe to mirror SupplyController)
        if ($filter === 'low') {
            // 1–5 only
            $query->whereRaw('CAST(Quantity AS SIGNED) BETWEEN 1 AND 5');
        } elseif ($filter === 'no') {
            // exactly 0
            $query->whereRaw('CAST(Quantity AS SIGNED) = 0');
        } elseif ($filter === 'available') {
            $query->whereRaw('CAST(Quantity AS SIGNED) > 5');
        }

        // --- NEW: apply category filter (coalescing both columns) ---
        if (!empty($selectedCategory)) {
            $query->whereRaw("TRIM(COALESCE(`Category`, `category`, '')) = ?", [$selectedCategory]);
        }

        // Apply search only if provided
        if (!empty($search)) {
            $query->where('ItemDescription', 'LIKE', "%{$search}%");
        }

        // Stabilize sort so pagination is consistent across pages (supply table has no updated_at)
        $query->orderBy('StockNumber', 'asc');

        // Preserve current query params on pagination links (keep your per-page size)
        $filtered_supplies = $query->paginate(5)->appends($request->query());

        // Assume orders are stored in session (or adjust accordingly)
        $orders = session('orders', []);

        return view('dashboard.use', compact(
            'notifications', 'supplies', 'filtered_supplies',
            'total_products', 'total_stock', 'out_of_stock', 'suppliers',
            'filter', 'search', 'orders', 'good_stock', 'low_stock',
            'stock_availability_percentage', 'need_restocking_percentage',
            'recently_released', 'recently_released_count', 'recently_released_percentage',
            // --- NEW ---
            'categories', 'selectedCategory',
            'total_available', 'total_low_stock', 'total_out_of_stock'
        ));
    }

    public function editSupply(Request $request)
    {
        $validated = $request->validate([
            'supply_id'         => 'required|integer|exists:items,id',
            'supply_name'       => 'required|string',
            'supply_description'=> 'required|string',
            'supply_quantity'   => 'required|integer',
        ]);

        // Update the supply record
        $item = Supply::find($validated['supply_id']);
        $item->ItemName    = $validated['supply_name'];
        $item->Descriptions = $validated['supply_description'];
        $item->Quantity    = $validated['supply_quantity'];
        $item->UpdatedAt   = now();
        $item->save();

        // Log the action
        Log::create([
            'timestamp'   => now(),
            'supply_name' => $validated['supply_name'],
            'description' => $validated['supply_description'],
            'quantity'    => $validated['supply_quantity'],
        ]);

        // Add a notification
        $notifications = $this->notificationService->load();
        $notifications[] = [
            'id'        => uniqid(),
            'type'      => 'info',
            'message'   => 'Supply edited: ' . $validated['supply_name'],
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'read'      => false,
        ];
        $this->notificationService->save($notifications);

        return redirect()->route('dashboard.use');
    }

    public function markNotificationRead(Request $request)
    {
        $validated = $request->validate([
            'notification_id' => 'required|string'
        ]);

        $notifications = $this->notificationService->load();
        foreach ($notifications as &$notification) {
            if ($notification['id'] === $validated['notification_id']) {
                $notification['read'] = true;
            }
        }
        $this->notificationService->save($notifications);
        return redirect()->route('dashboard.use');
    }
}
