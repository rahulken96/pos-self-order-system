<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Inertia\Inertia;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::orderBy('number')->get();
        return Inertia::render('Admin/Tables/Index', [
            'tables' => $tables
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|integer|unique:tables,number',
        ]);

        $table = Table::create([
            'number' => $request->number,
            'status' => 'available'
        ]);

        // Generate QR code right away
        $url = route('customer.order', $table->id);
        $qr  = QrCode::format('svg')->size(300)->generate($url);
        $table->update(['qr_code' => base64_encode($qr)]);

        return redirect()->route('tables.index')->with('success', 'Meja berhasil ditambahkan.');
    }

    public function update(Request $request, Table $table)
    {
        $request->validate([
            'number' => 'required|integer|unique:tables,number,' . $table->id,
            'status' => 'required|in:available,occupied'
        ]);

        $table->update([
            'number' => $request->number,
            'status' => $request->status,
        ]);

        return redirect()->route('tables.index')->with('success', 'Meja berhasil diperbarui.');
    }

    public function destroy(Table $table)
    {
        $table->delete();
        return redirect()->route('tables.index')->with('success', 'Meja berhasil dihapus.');
    }

    public function generateQr(Table $table)
    {
        $url = route('customer.order', $table->id);
        $qr  = QrCode::format('svg')->size(300)->generate($url);
        $table->update(['qr_code' => base64_encode($qr)]);
        return redirect()->route('tables.index')->with('success', 'QR Code berhasil di-generate untuk meja ' . $table->number);
    }
}
