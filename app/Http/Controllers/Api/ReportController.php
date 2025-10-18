<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{

    public function exportItems(Request $request)
    {
        $items = Item::orderBy('name', 'asc')->get();
        
        $data = [
            'title' => 'Laporan Data Barang',
            'date' => Carbon::now()->format('d F Y'),
            'items' => $items,
            'total_items' => $items->count(),
            'total_stock' => $items->sum('stock'),
            'low_stock_count' => $items->filter(function($item) {
                return $item->stock <= $item->threshold;
            })->count(),
        ];

        $pdf = Pdf::loadView('reports.items', $data);
        return $pdf->download('laporan-barang-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function exportTransactions(Request $request)
    {
        $query = Transaction::with(['item', 'user']);

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
            $periodText = Carbon::parse($request->start_date)->format('d M Y') . ' - ' . Carbon::parse($request->end_date)->format('d M Y');
        } else {
            // Default: current month
            $query->whereYear('date', Carbon::now()->year)
                  ->whereMonth('date', Carbon::now()->month);
            $periodText = Carbon::now()->format('F Y');
        }

        $transactions = $query->orderBy('date', 'desc')->get();

        $transactionsIn = $transactions->where('type', 'in');
        $transactionsOut = $transactions->where('type', 'out');

        $data = [
            'title' => 'Laporan Transaksi',
            'period' => $periodText,
            'date' => Carbon::now()->format('d F Y'),
            'transactions' => $transactions,
            'total_transactions' => $transactions->count(),
            'total_in' => $transactionsIn->sum('quantity'),
            'total_out' => $transactionsOut->sum('quantity'),
            'transactions_in_count' => $transactionsIn->count(),
            'transactions_out_count' => $transactionsOut->count(),
        ];

        $pdf = Pdf::loadView('reports.transactions', $data);
        return $pdf->download('laporan-transaksi-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function exportStock()
    {
        $items = Item::orderBy('stock', 'asc')->get();
        
        $lowStockItems = $items->filter(function($item) {
            return $item->stock <= $item->threshold;
        });

        $data = [
            'title' => 'Laporan Stok Barang',
            'date' => Carbon::now()->format('d F Y'),
            'items' => $items,
            'low_stock_items' => $lowStockItems,
            'total_items' => $items->count(),
            'low_stock_count' => $lowStockItems->count(),
        ];

        $pdf = Pdf::loadView('reports.stock', $data);
        return $pdf->download('laporan-stok-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
}