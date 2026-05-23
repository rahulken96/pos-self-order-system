<?php

namespace App\Http\Controllers;

use App\Events\BillRequested;
use App\Events\OrderPlaced;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Table $table)
    {
        $order = Order::where('table_id', $table->id)
            ->whereIn('status', ['draft', 'pending', 'confirmed', 'cooking', 'ready'])
            ->first();

        if ($order) {
            // Check if this browser is the owner of the active order
            if (session("active_order_{$table->id}") == $order->id) {
                $categories = Category::with(['menuItems' => fn($q) => $q->where('is_available', true)])->get();

                // Calculate estimated wait time based on active queue
                $activeQueue = Order::whereIn('status', ['pending', 'confirmed', 'cooking'])->count();
                $estimatedWaitTime = max(10, 10 + ($activeQueue * 5)); // min 10m, +5m per order

                return Inertia::render('Customer/Menu', [
                    'table' => $table,
                    'categories' => $categories,
                    'order' => $order->load('items.menuItem'),
                    'estimated_wait_time' => $estimatedWaitTime,
                ]);
            } else {
                // Table is occupied by someone else (or session got cleared)
                return Inertia::render('Customer/TableOccupied', [
                    'table' => $table,
                    'error' => session('error'),
                ]);
            }
        }

        return Inertia::render('Customer/Start', compact('table'));
    }

    public function start(Request $request, Table $table)
    {
        // Double check table occupancy status to prevent race conditions
        $existing = Order::where('table_id', $table->id)
            ->whereIn('status', ['draft', 'pending', 'confirmed', 'cooking', 'ready'])
            ->first();

        if ($existing) {
            return redirect()->route('customer.order', $table->id)
                ->with('error', 'Meja sedang digunakan.');
        }

        $request->validate([
            'customer_name'  => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
        ]);

        $order = Order::create([
            'table_id'       => $table->id,
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'status'         => 'draft',
            'total_price'    => 0,
        ]);

        $table->update(['status' => 'occupied']);

        // Store active order ID in browser session
        session(["active_order_{$table->id}" => $order->id]);

        return redirect()->route('customer.order', $table->id);
    }

    public function resume(Request $request, Table $table)
    {
        $request->validate([
            'customer_phone' => 'required|string|max:20',
        ]);

        $order = Order::where('table_id', $table->id)
            ->whereIn('status', ['draft', 'pending', 'confirmed', 'cooking', 'ready'])
            ->first();

        if ($order && $order->customer_phone === $request->customer_phone) {
            session(["active_order_{$table->id}" => $order->id]);
            return redirect()->route('customer.order', $table->id);
        }

        return redirect()->route('customer.order', $table->id)
            ->with('error', 'Nomor handphone tidak cocok dengan pesanan aktif.');
    }

    public function addItem(Request $request, Order $order)
    {
        $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        $menuItem = MenuItem::findOrFail($request->menu_item_id);
        $existing = $order->items()->where('menu_item_id', $menuItem->id)->first();

        if ($existing) {
            $existing->update([
                'quantity' => $existing->quantity + $request->quantity,
                'subtotal' => $existing->subtotal + ($menuItem->price * $request->quantity),
            ]);
        } else {
            $order->items()->create([
                'menu_item_id' => $menuItem->id,
                'quantity'     => $request->quantity,
                'price'        => $menuItem->price,
                'subtotal'     => $menuItem->price * $request->quantity,
                'notes'        => $request->notes,
            ]);
        }

        $order->update(['total_price' => $order->items()->sum('subtotal')]);

        return response()->json($order->load('items.menuItem'));
    }

    public function submitOrder(Order $order)
    {
        $order->update(['status' => 'pending']);
        if (class_exists(OrderPlaced::class)) {
            event(new OrderPlaced($order));
        }
        return response()->json(['message' => 'Pesanan dikirim ke dapur!']);
    }

    public function requestBill(Order $order)
    {
        $order->update([
            'bill_requested'    => true,
            'bill_requested_at' => now(),
        ]);

        if (class_exists(BillRequested::class)) {
            broadcast(new BillRequested($order));
        }

        return response()->json(['message' => 'Permintaan bill terkirim ke kasir!']);
    }

    public function payOnline(Order $order)
    {
        \Xendit\Configuration::setXenditKey(config('services.xendit.secret_key'));
        
        $apiInstance = new \Xendit\Invoice\InvoiceApi();
        $create_invoice_request = new \Xendit\Invoice\CreateInvoiceRequest([
            'external_id' => 'ORDER-' . $order->id . '-' . time(),
            'amount' => $order->total_price,
            'description' => 'Pembayaran Meja ' . $order->table->number,
            'customer' => [
                'given_names' => $order->customer_name,
                'mobile_number' => $order->customer_phone,
            ],
            'success_redirect_url' => route('customer.payStatus', $order->id),
            'failure_redirect_url' => route('customer.order', $order->table_id),
            'currency' => 'IDR'
        ]);

        $invoice = $apiInstance->createInvoice($create_invoice_request);

        Payment::create([
            'order_id'          => $order->id,
            'method'            => 'xendit',
            'amount'            => $order->total_price,
            'xendit_invoice_id' => $invoice['id'],
            'xendit_payment_url'=> $invoice['invoice_url'],
            'xendit_status'     => 'PENDING',
        ]);

        return response()->json(['payment_url' => $invoice['invoice_url']]);
    }

    public function payStatus(Order $order)
    {
        return Inertia::render('Customer/PayStatus', compact('order'));
    }
}
