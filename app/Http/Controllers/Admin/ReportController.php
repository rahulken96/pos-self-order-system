<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Exports\OrdersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from', now()->subDays(6)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        // Start querying completed orders
        $ordersQuery = Order::where('status', 'completed')
            ->whereBetween('created_at', [
                date('Y-m-d 00:00:00', strtotime($from)),
                date('Y-m-d 23:59:59', strtotime($to))
            ])
            ->with(['table', 'payment']);

        $orders = $ordersQuery->orderBy('created_at', 'desc')->get();
        $totalSales = $orders->sum('total_price');
        $totalOrders = $orders->count();

        // Top 5 Menu Items
        $topItems = OrderItem::whereHas('order', function ($q) use ($from, $to) {
                $q->where('status', 'completed')
                  ->whereBetween('created_at', [
                      date('Y-m-d 00:00:00', strtotime($from)),
                      date('Y-m-d 23:59:59', strtotime($to))
                  ]);
            })
            ->select('menu_item_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->groupBy('menu_item_id')
            ->with('menuItem')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        // 7 Days sales chart
        $dailySales = Order::where('status', 'completed')
            ->whereBetween('created_at', [
                date('Y-m-d 00:00:00', strtotime(now()->subDays(6)->format('Y-m-d'))),
                date('Y-m-d 23:59:59', strtotime(now()->format('Y-m-d')))
            ])
            ->select(DB::raw("DATE(created_at) as date"), DB::raw("SUM(total_price) as total"))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Ensure all 7 days are represented in chart
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date' => now()->subDays($i)->format('d M'),
                'total' => (float) ($dailySales[$date] ?? 0)
            ];
        }

        // Payment method breakdown
        $paymentBreakdown = Order::where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [
                date('Y-m-d 00:00:00', strtotime($from)),
                date('Y-m-d 23:59:59', strtotime($to))
            ])
            ->join('payments', 'orders.id', '=', 'payments.order_id')
            ->select('payments.method', DB::raw('SUM(orders.total_price) as total_sales'), DB::raw('COUNT(orders.id) as count'))
            ->groupBy('payments.method')
            ->get()
            ->toArray();

        return Inertia::render('Admin/Reports/Index', [
            'orders' => $orders,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'topItems' => $topItems,
            'chartData' => $chartData,
            'paymentBreakdown' => $paymentBreakdown,
            'filters' => [
                'from' => $from,
                'to' => $to
            ]
        ]);
    }

    public function exportExcel(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        return Excel::download(
            new OrdersExport($from, $to),
            'laporan-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $orders = Order::where('status', 'completed')
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [
                    date('Y-m-d 00:00:00', strtotime($from)),
                    date('Y-m-d 23:59:59', strtotime($to))
                ]);
            })
            ->with(['table', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.summary', compact('orders', 'from', 'to'));
        return $pdf->download('laporan-' . now()->format('Y-m-d') . '.pdf');
    }
}
