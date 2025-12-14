<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supply;     // Eloquent model for the Items table
use App\Models\Log;      // Eloquent model for logging actions
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Requests; // Add this import for the Requests model

class AdminController extends Controller
{
    // Load notifications from storage (using Laravel’s Storage façade)
    private function loadNotifications()
    {
        if (Storage::exists('notifications.json')) {
            $data = Storage::get('notifications.json');
            $notifications = json_decode($data, true);
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $notifications,
                count($notifications),
                10, // items per page
                request()->get('page', 1)
            );
        }
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1);
    }

    // Save notifications to storage
    private function saveNotifications($notifications)
    {
        Storage::put('notifications.json', json_encode($notifications));
    }

   public function index(Request $request)
    {
        // Ensure the authenticated user is an admin
        if (auth()->user()->role !== 'SuperAdmin' && Auth::user()->role !== 'Admin') {
            return redirect()->route('login');
        }

        // Load notifications from file
        $notifications = $this->loadNotifications();

        // ---------------------------------------------------------
        // 1. DASHBOARD STATISTICS (Calculated from ALL supplies)
        // ---------------------------------------------------------
        // We fetch all supplies first to calculate the top-level dashboard stats
        // so they don't change when you filter the table below.
        $all_supplies_raw = Supply::all(); 
        
        // Calculate basic counts
        $total_products = $all_supplies_raw->count();
        $total_stock = $all_supplies_raw->sum('Quantity');
        $out_of_stock = $all_supplies_raw->where('Quantity', 0)->count();
        $suppliers = $all_supplies_raw->unique('Supplier')->count();

        // Calculate enhanced statistics for charts
        $low_stock_threshold = 5; // Updated to match your 'Low Stock' definition (1-5)
        
        $good_stock = $all_supplies_raw->filter(function ($item) use ($low_stock_threshold) {
            return $item->Quantity > $low_stock_threshold;
        })->count();
        
        $low_stock = $all_supplies_raw->filter(function ($item) use ($low_stock_threshold) {
            return $item->Quantity > 0 && $item->Quantity <= $low_stock_threshold;
        })->count();
        
        // Calculate percentages
        $stock_availability_percentage = ($total_products > 0) 
            ? round((($total_products - $out_of_stock) / $total_products) * 100) 
            : 0;
            
        $items_need_restocking = ($total_products > 0) 
            ? round(($out_of_stock / $total_products) * 100) 
            : 0;

        // Fetch recently released items logic...
        $recently_released = Requests::where('status', 'released')
            ->where('updated_at', '>=', now()->subDays(7))
            ->orderBy('updated_at', 'desc')
            ->get();
        
        $recently_released_count = $recently_released->count();
        $recently_released_percentage = ($total_products > 0) 
            ? round(($recently_released_count / $total_products) * 100) 
            : 0;

        // Chart data formatting
        $chart_labels = ['Good Stock', 'Low Stock', 'Out of Stock'];
        $chart_data = [$good_stock, $low_stock, $out_of_stock];
        $chart_colors = ['rgba(34, 197, 94, 0.8)', 'rgba(251, 191, 36, 0.8)', 'rgba(239, 68, 68, 0.8)'];

        // ---------------------------------------------------------
        // 2. FILTERING LOGIC (The Fix for Ghost Pages)
        // ---------------------------------------------------------
        // We start a NEW query strictly for the table data
        $query = Supply::query();

        // A. Category Filter
        if ($request->filled('category')) {
            $query->where('Category', $request->category);
        }

        // B. Status Filter (Using your preferred logic)
        // Available > 5, Low = 1-5, No = 0
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'available':
                    $query->where('Quantity', '>', 5);
                    break;
                case 'low':
                    $query->whereBetween('Quantity', [1, 5]);
                    break;
                case 'no':
                    $query->where('Quantity', 0);
                    break;
            }
        }

        // C. Search Filter
        $search = $request->input('search', '');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('StockNumber', 'like', '%' . $search . '%')
                  ->orWhere('ItemDescription', 'like', '%' . $search . '%');
            });
        }

        // D. Fetch Results (5 per page)
        $filtered_supplies = $query->orderBy('StockNumber', 'asc')->paginate(5);
        
        // ---------------------------------------------------------
        // 3. GET CATEGORIES LIST
        // ---------------------------------------------------------
        // This is needed for the dropdown in your Blade file
        $categories = Supply::whereNotNull('Category')->distinct()->pluck('Category')->sort();

        // Get notification counts (Keep existing logic)
        try {
            $notificationCounts = \App\Services\SidebarNotificationService::getSuperAdminNotificationCounts();
            $enhancedCounts = \App\Services\NotificationDisplayService::getAllPageCounts();
            $finalNotificationCounts = array_merge($notificationCounts, $enhancedCounts);
        } catch (\Exception $e) {
            $finalNotificationCounts = ['approved' => 0, 'denied' => 0, 'disapproved' => 0, 'pending' => 0, 'released' => 0, 'total_unread' => 0];
        }

        return view('Admin.admin', [
            'notifications' => $notifications,
            'filtered_supplies' => $filtered_supplies,
            'categories' => $categories, // Added this!
            'search' => $search,
            'filter' => $request->input('filter', 'all'),
            
            // Dashboard Stats
            'total_products' => $total_products,
            'total_stock' => $total_stock,
            'out_of_stock' => $out_of_stock,
            'suppliers' => $suppliers,
            'good_stock' => $good_stock,
            'low_stock' => $low_stock,
            'total_available' => $good_stock,
            'total_low_stock' => $low_stock,
            'total_out_of_stock' => $out_of_stock,
            'stock_availability_percentage' => $stock_availability_percentage,
            'items_need_restocking_percentage' => $items_need_restocking,
            
            // Charts & Extras
            'chart_labels' => $chart_labels,
            'chart_data' => $chart_data,
            'chart_colors' => $chart_colors,
            'recently_released' => $recently_released,
            'recently_released_count' => $recently_released_count,
            'recently_released_percentage' => $recently_released_percentage,
            'notificationCounts' => $finalNotificationCounts
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validate incoming data
        $request->validate([
            'supply_name'        => 'required|string',
            'supply_description' => 'required|string',
            'supply_quantity'    => 'required|integer'
        ]);

        $supply = Supply::findOrFail($id);
        $supply->item_name   = $request->supply_name;
        $supply->descriptions = $request->supply_description;
        $supply->quantity    = $request->supply_quantity;
        $supply->save();

        // Log the edit action
        Log::create([
            'action'      => 'edit',
            'supply_name' => $request->supply_name,
            'description' => $request->supply_description,
            'quantity'    => $request->supply_quantity,
        ]);

        // Add a notification
        $notifications = $this->loadNotifications();
        $notifications[] = [
            'id'        => uniqid(),
            'type'      => 'info',
            'message'   => 'Supply edited: ' . $request->supply_name,
            'timestamp' => now()->toDateTimeString(),
            'read'      => false
        ];
        $this->saveNotifications($notifications);

        return redirect()->route('Admin.admin');
    }

    public function destroy($id)
    {
        $supply = Supply::findOrFail($id);
        $supply_name = $supply->item_name;
        $supply->delete();

        // Log the deletion action
        Log::create([
            'action'      => 'delete',
            'supply_name' => $supply_name,
        ]);

        // Add a notification
        $notifications = $this->loadNotifications();
        $notifications[] = [
            'id'        => uniqid(),
            'type'      => 'warning',
            'message'   => 'Supply deleted: ' . $supply_name,
            'timestamp' => now()->toDateTimeString(),
            'read'      => false
        ];
        $this->saveNotifications($notifications);

        return redirect()->route('Admin.admin');
    }

    public function markNotificationRead(Request $request)
    {
        $notificationId = $request->input('notification_id');
        $notifications = Storage::exists('notifications.json') ? 
            json_decode(Storage::get('notifications.json'), true) : [];

        foreach ($notifications as &$notification) {
            if ($notification['id'] === $notificationId) {
                $notification['read'] = true;
            }
        }
        $this->saveNotifications($notifications);

        return redirect()->route('Admin.admin', ['page' => $request->get('page', 1)]);
    }
}