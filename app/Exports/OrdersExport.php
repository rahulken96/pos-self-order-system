<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromQuery, WithHeadings, WithMapping
{
    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from ? date('Y-m-d 00:00:00', strtotime($from)) : null;
        $this->to = $to ? date('Y-m-d 23:59:59', strtotime($to)) : null;
    }

    public function query()
    {
        $query = Order::query()->where('status', 'completed')->with(['table', 'payment']);

        if ($this->from && $this->to) {
            $query->whereBetween('created_at', [$this->from, $this->to]);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID Order',
            'Meja',
            'Nama Customer',
            'Telepon Customer',
            'Total Harga',
            'Status',
            'Metode Pembayaran',
            'Tanggal Dibuat',
        ];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->table ? 'Meja ' . $order->table->number : '-',
            $order->customer_name,
            $order->customer_phone,
            $order->total_price,
            $order->status,
            $order->payment ? ($order->payment->method === 'cash_to_kasir' ? 'Tunai' : 'Online (Xendit)') : '-',
            $order->created_at->format('d-m-Y H:i'),
        ];
    }
}
