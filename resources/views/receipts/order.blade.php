<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan - Meja {{ $order->table->number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; color: #1a1a1a; width: 80mm; margin: 0 auto; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .separator { border-top: 1px dashed #999; margin: 8px 0; }
        .header { padding: 12px 8px 8px; text-align: center; }
        .header h1 { font-size: 16px; font-weight: bold; letter-spacing: 2px; }
        .header p { font-size: 10px; color: #555; margin-top: 2px; }
        .section { padding: 6px 8px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .item-name { flex: 1; }
        .item-qty { width: 30px; text-align: center; }
        .item-price { width: 70px; text-align: right; }
        .total-row { font-weight: bold; font-size: 13px; }
        .footer { text-align: center; padding: 12px 8px; font-size: 10px; color: #555; }
        .badge { display: inline-block; background: #1a1a1a; color: #fff; padding: 2px 8px; font-size: 10px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ strtoupper(config('app.name', 'RESTORAN')) }}</h1>
        <p>Sistem Self-Order</p>
        <p>{{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="separator"></div>

    <div class="section">
        <div class="row">
            <span>No. Meja</span>
            <span class="bold">Meja {{ $order->table->number }}</span>
        </div>
        <div class="row">
            <span>Pelanggan</span>
            <span class="bold">{{ $order->customer_name }}</span>
        </div>
        <div class="row">
            <span>No. Order</span>
            <span>#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
    </div>

    <div class="separator"></div>

    <div class="section">
        <div class="row bold">
            <span class="item-name">Item</span>
            <span class="item-qty">Qty</span>
            <span class="item-price">Subtotal</span>
        </div>
        <div class="separator" style="margin: 4px 0;"></div>

        @foreach($order->items as $item)
        <div style="margin-bottom: 6px;">
            <div class="row">
                <span class="item-name">{{ $item->menuItem->name ?? 'Item' }}</span>
                <span class="item-qty">{{ $item->quantity }}</span>
                <span class="item-price">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($item->notes)
            <div style="font-size: 10px; color: #777; padding-left: 4px; font-style: italic;">
                * {{ $item->notes }}
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="separator"></div>

    <div class="section">
        <div class="row total-row">
            <span>TOTAL</span>
            <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
        </div>

        @if($order->payment)
        <div class="separator" style="margin: 6px 0;"></div>
        <div class="row">
            <span>Metode Bayar</span>
            <span>{{ $order->payment->method === 'cash_to_kasir' ? 'Tunai' : 'Online (Xendit)' }}</span>
        </div>
        @if($order->payment->method === 'cash_to_kasir')
        <div class="row">
            <span>Uang Diterima</span>
            <span>Rp {{ number_format($order->payment->amount, 0, ',', '.') }}</span>
        </div>
        <div class="row bold">
            <span>Kembalian</span>
            <span>Rp {{ number_format($order->payment->change_amount, 0, ',', '.') }}</span>
        </div>
        @endif
        @endif
    </div>

    <div class="separator"></div>

    <div class="footer">
        <p>Terima kasih telah berkunjung!</p>
        <p style="margin-top: 4px;">Semoga hari Anda menyenangkan 🙏</p>
        <div style="margin-top: 8px;" class="badge">LUNAS</div>
    </div>
</body>
</html>
