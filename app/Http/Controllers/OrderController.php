<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Requests;
use App\Models\RequestItem;
use App\Models\Supply; // Ensure this model exists
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{





    public function index()
    {

        
        $user = Auth::user();
    
        // OLD LINE REMOVED (orders table doesn't exist)
        // $orders = orders::where('username', Auth::user()->username)->get();
    
        // Get all the requests made (or filter per user if needed)
        $requests = Requests::all(); // or ->where('username', $user->username) if you add a user link
    
        $supplies = Supply::all()->toArray();
        $notification = Session::get('notification', []);
    
        return view('orders', compact('requests', 'supplies', 'notification'));
    }

    public function handleRequest(Request $request)
{
    $user = Auth::user();

    if ($request->has('request_action')) {
        $newRequest = new Requests(); // use RequestItem if that's your current model name

        $newRequest->office = $request->input('office');
        $newRequest->supply_name = $request->input('supply_name');
        $newRequest->purpose = $request->input('purpose');
        $newRequest->supply_quantity = $request->input('supply_quantity');
        $newRequest->status = 'Pending';

        $newRequest->save();

        Session::flash('notifications', [[
            'id'        => uniqid(),
            'type'      => 'info',
            'message'   => 'New supply request submitted: ' . $newRequest->supply_name,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'read'      => false,
        ]]);
        Session::flash('request_success', true);
        return redirect()->route('orders.index');
    }

    // Accept Request
    elseif ($request->has('accept_request')) {
        $reqId = $request->input('request_id');
        $reqItem = Requests::findOrFail($reqId);
        $reqItem->status = 'Accepted';
        $reqItem->save();

        // You can decide what 'accepting' means — here, maybe add the item to supply
        Supply::create([
            'ItemName'     => $reqItem->supply_name,
            'Description'  => $reqItem->purpose, // treating purpose as description?
            'Quantity'     => $reqItem->supply_quantity,
        ]);

        Session::flash('notifications', [[
            'id'        => uniqid(),
            'type'      => 'success',
            'message'   => 'Request accepted: ' . $reqItem->supply_name,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'read'      => false,
        ]]);

        return redirect()->route('orders.index');
    }

    // Decline Request
    elseif ($request->has('decline_request')) {
        $reqId = $request->input('request_id');
        $reqItem = Requests::findOrFail($reqId);
        $reqItem->status = 'Declined';
        $reqItem->save();

        Session::flash('notifications', [[
            'id'        => uniqid(),
            'type'      => 'error',
            'message'   => 'Request declined: ' . $reqItem->supply_name,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'read'      => false,
        ]]);

        return redirect()->route('orders.index');
    }

    // Delete Request (admin only)
    elseif ($request->has('delete_request') && strtolower($user->role) === 'admin') {
        $reqId = $request->input('request_id');
        Requests::destroy($reqId);
        return redirect()->route('orders.index');
    }

    return redirect()->route('orders.index');
}
    public function create()
    {
//fetch the supply in the database table 
         $supply = DB::table('supply')->get();

// return view('requests.create');
        
 return view('requests.create', compact('supply'));
        // Ensure the correct view path is returned
   
    }

    public function pending()
    {
        // Logic to fetch and display pending requests
        $pendingRequests = []; // Replace with actual logic to fetch pending requests
        return view('requests.pending', compact('pendingRequests'));
    }

    public function approved()
    {
        // Logic to fetch and display approved requests
        $approvedRequests = []; // Replace with actual logic to fetch approved requests
        return view('requests.approved', compact('approvedRequests'));
    }

    public function released()
    {
        // Logic to fetch and display released requests
        $releasedRequests = []; // Replace with actual logic to fetch released requests
        return view('requests.released', compact('releasedRequests'));
    }

    public function disapproved()
    {
        // Logic to fetch and display disapproved requests
        $disapprovedRequests = []; // Replace with actual logic to fetch disapproved requests
        return view('requests.disapproved', compact('disapprovedRequests'));
    }
}



