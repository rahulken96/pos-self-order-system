<?php

namespace App\Http\Controllers;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Illuminate\Http\Request;

class KasirController extends Controller
{
    public function index()
    {
        $tables = Table::with(['orders' => function ($q) {
            $q->whereIn('status', ['draft', 'pending', 'confirmed', 'cooking', 'ready'])
              ->with(['items.menuItem'])
              ->latest();
        }])->orderBy('number')->get();

        return Inertia::render('Kasir/Index', compact('tables'));
    }

    public function processPayment(Request $request, Order $order)
    {
        $request->validate([
            'amount' => 'required|numeric|min:' . $order->total_price,
        ]);

        Payment::create([
            'order_id'      => $order->id,
            'method'        => 'cash_to_kasir',
            'amount'        => $request->amount,
            'change_amount' => $request->amount - $order->total_price,
            'processed_by'  => auth()->id(),
        ]);

        $order->update(['status' => 'completed']);
        $order->table->update(['status' => 'available']);

        broadcast(new OrderStatusUpdated($order));

        return response()->json([
            'message' => 'Pembayaran berhasil!',
            'change'  => $request->amount - $order->total_price,
        ]);
    }

    public function cancelOrder(Order $order)
    {
        $order->update(['status' => 'cancelled']);
        $order->table->update(['status' => 'available']);

        broadcast(new OrderStatusUpdated($order));

        return response()->json([
            'message' => 'Pesanan berhasil dibatalkan dan meja telah di-reset.',
        ]);
    }

    public function printReceipt(Order $order)
    {
        $order->load(['items.menuItem', 'table', 'payment']);
        $pdf = Pdf::loadView('receipts.order', compact('order'));
        return $pdf->stream('struk-meja-' . $order->table->number . '.pdf');
    }
}
