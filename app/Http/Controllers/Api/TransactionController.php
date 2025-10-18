<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{


    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Transaction::with(['item', 'user']);

        // Staff hanya bisa lihat transaksi mereka sendiri
        if ($user->role === 'staff') {
            $query->where('user_id', $user->id);
        }

        // Filter by date
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by item
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $transactions = $query->paginate($perPage);

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $item = Item::find($request->item_id);

        // Validasi stok untuk transaksi keluar
        if ($request->type === 'out' && $item->stock < $request->quantity) {
            return response()->json([
                'error' => 'Stok tidak mencukupi',
                'available_stock' => $item->stock,
                'requested' => $request->quantity
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Handle file upload
            $filePath = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('transactions', $fileName, 'public');
            }

            // Create transaction
            $transaction = Transaction::create([
                'item_id' => $request->item_id,
                'user_id' => auth()->id(),
                'type' => $request->type,
                'quantity' => $request->quantity,
                'date' => $request->date,
                'description' => $request->description,
                'file_path' => $filePath,
            ]);

            // Update stock
            if ($request->type === 'in') {
                $item->stock += $request->quantity;
            } else {
                $item->stock -= $request->quantity;
            }
            $item->save();

            DB::commit();

            return response()->json([
                'message' => 'Transaksi berhasil ditambahkan',
                'data' => $transaction->load(['item', 'user'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if exists
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return response()->json([
                'error' => 'Transaksi gagal',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        $transaction = Transaction::with(['item', 'user'])->find($id);

        if (!$transaction) {
            return response()->json(['error' => 'Transaksi tidak ditemukan'], 404);
        }

        // Staff hanya bisa lihat transaksi mereka sendiri
        if ($user->role === 'staff' && $transaction->user_id !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json($transaction);
    }

    public function destroy($id)
    {
        // Only admin can delete
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['error' => 'Transaksi tidak ditemukan'], 404);
        }

        DB::beginTransaction();

        try {
            // Revert stock
            $item = $transaction->item;
            if ($transaction->type === 'in') {
                $item->stock -= $transaction->quantity;
            } else {
                $item->stock += $transaction->quantity;
            }
            $item->save();

            // Delete file if exists
            if ($transaction->file_path && Storage::disk('public')->exists($transaction->file_path)) {
                Storage::disk('public')->delete($transaction->file_path);
            }

            $transaction->delete();

            DB::commit();

            return response()->json([
                'message' => 'Transaksi berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal menghapus transaksi',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}