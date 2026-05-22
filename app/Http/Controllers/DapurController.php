<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Events\OrderStatusUpdated;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DapurController extends Controller
{
    public function index()
    {
        $orders = Order::whereIn('status', ['pending', 'confirmed', 'cooking', 'ready'])
            ->with(['items.menuItem', 'table'])
            ->orderByRaw("CASE status 
                WHEN 'pending' THEN 1 
                WHEN 'confirmed' THEN 2 
                WHEN 'cooking' THEN 3 
                WHEN 'ready' THEN 4 
                ELSE 5 
             END")
            ->oldest() // Prioritize older orders
            ->get();

        return Inertia::render('Dapur/Index', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:cooking,ready',
        ]);

        $order->update(['status' => $request->status]);
        
        broadcast(new OrderStatusUpdated($order));

        return back()->with('success', 'Status pesanan berhasil diupdate.');
    }
}
