<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: bold; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; font-size: 11px; }
        .summary-box { margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary-box table { width: 100%; }
        .summary-box td { font-size: 11px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 10px 8px; text-align: left; }
        .table th { background-color: #f5f5f5; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .grand-total { background-color: #f9f9f9; font-size: 13px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ strtoupper(config('app.name', 'Restoran')) }}</h1>
        <p>Laporan Ringkasan Penjualan</p>
        <p>Periode: {{ $from ? date('d/m/Y', strtotime($from)) : 'Awal' }} - {{ $to ? date('d/m/Y', strtotime($to)) : 'Akhir' }}</p>
    </div>

    <div class="summary-box">
        <table>
            <tr>
                <td><strong>Total Transaksi:</strong> {{ $orders->count() }} Order</td>
                <td class="text-right"><strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Meja</th>
                <th>Customer</th>
                <th>Metode</th>
                <th class="text-right">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td>Meja {{ $order->table->number ?? '-' }}</td>
                <td>{{ $order->customer_name }}</td>
                <td>{{ $order->payment ? ($order->payment->method === 'cash_to_kasir' ? 'Tunai' : 'Online (Xendit)') : '-' }}</td>
                <td class="text-right">{{ number_format($order->total_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="grand-total bold">
                <td colspan="5" class="text-right">TOTAL PENDAPATAN</td>
                <td class="text-right">Rp {{ number_format($orders->sum('total_price'), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
