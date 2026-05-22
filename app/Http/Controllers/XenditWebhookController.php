<?php

namespace App\Http\Controllers;

use App\Events\OrderStatusUpdated;
use App\Models\Payment;
use Illuminate\Http\Request;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        if ($request->header('x-callback-token') !== config('services.xendit.webhook_token', env('XENDIT_WEBHOOK_TOKEN'))) {
            abort(403);
        }

        $payment = Payment::where('xendit_invoice_id', $request->id)->firstOrFail();
        $payment->update(['xendit_status' => $request->status]);

        if ($request->status === 'PAID') {
            $order = $payment->order;
            $order->update(['status' => 'completed']);
            $order->table->update(['status' => 'available']);

            if (class_exists(OrderStatusUpdated::class)) {
                event(new OrderStatusUpdated($order));
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
