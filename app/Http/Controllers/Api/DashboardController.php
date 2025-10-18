<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = auth()->user();
        
        // Get days parameter (default 7, max 90)
        $days = min((int) $request->get('days', 7), 90);

        // Total items
        $totalItems = Item::count();
        $totalStock = Item::sum('stock');

        // Low stock items
        $lowStockCount = Item::whereColumn('stock', '<=', 'threshold')->count();
        $lowStockItems = Item::whereColumn('stock', '<=', 'threshold')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        // Transactions
        $transactionsQuery = Transaction::query();
        if ($user->role === 'staff') {
            $transactionsQuery->where('user_id', $user->id);
        }

        $totalTransactions = $transactionsQuery->count();
        $todayTransactions = $transactionsQuery->whereDate('date', Carbon::today())->count();

        // Transactions this month
        $thisMonthTransactions = $transactionsQuery
            ->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)
            ->count();

        // Recent transactions
        $recentTransactions = Transaction::with(['item', 'user'])
            ->when($user->role === 'staff', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart data - Dynamic days
        $chartData = collect(range($days - 1, 0))->map(function ($daysAgo) use ($user) {
            $date = Carbon::today()->subDays($daysAgo);
            
            $transactionsIn = Transaction::where('type', 'in')
                ->when($user->role === 'staff', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->whereDate('date', $date)
                ->sum('quantity');

            $transactionsOut = Transaction::where('type', 'out')
                ->when($user->role === 'staff', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->whereDate('date', $date)
                ->sum('quantity');

            return [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'in' => (int) $transactionsIn,
                'out' => (int) $transactionsOut,
            ];
        })->values();

        return response()->json([
            'summary' => [
                'total_items' => $totalItems,
                'total_stock' => $totalStock,
                'low_stock_count' => $lowStockCount,
                'total_transactions' => $totalTransactions,
                'today_transactions' => $todayTransactions,
                'month_transactions' => $thisMonthTransactions,
            ],
            'low_stock_items' => $lowStockItems,
            'recent_transactions' => $recentTransactions,
            'chart_data' => $chartData,
            'chart_days' => $days,
        ]);
    }

    public function activityLog()
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $activities = Transaction::with(['item', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'user' => $transaction->user->name,
                    'action' => $transaction->type === 'in' ? 'Menambah stok' : 'Mengurangi stok',
                    'item' => $transaction->item->name,
                    'quantity' => $transaction->quantity,
                    'date' => $transaction->date,
                    'created_at' => $transaction->created_at,
                ];
            });

        return response()->json($activities);
    }
}