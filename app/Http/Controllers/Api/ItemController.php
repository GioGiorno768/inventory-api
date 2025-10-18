<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{

    public function index(Request $request)
    {
        $query = Item::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $items = $query->paginate($perPage);

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'threshold' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $item = Item::create($validator->validated());

        return response()->json([
            'message' => 'Barang berhasil ditambahkan',
            'data' => $item
        ], 201);
    }

    public function show($id)
    {
        $item = Item::with('transactions.user')->find($id);

        if (!$item) {
            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:255',
            'stock' => 'sometimes|required|integer|min:0',
            'unit' => 'sometimes|required|string|max:50',
            'threshold' => 'sometimes|required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $item->update($validator->validated());

        return response()->json([
            'message' => 'Barang berhasil diupdate',
            'data' => $item
        ]);
    }

    public function destroy($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }

        $item->delete();

        return response()->json([
            'message' => 'Barang berhasil dihapus'
        ]);
    }

    public function lowStock()
    {
        $items = Item::whereColumn('stock', '<=', 'threshold')
            ->orderBy('stock', 'asc')
            ->get();

        return response()->json($items);
    }

    public function categories()
    {
        $categories = Item::select('category')
            ->distinct()
            ->pluck('category');

        return response()->json($categories);
    }
}