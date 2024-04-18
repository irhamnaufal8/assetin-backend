<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $categoryId = $request->query('category_id');

        if ($categoryId) {
            $inventories = Inventory::whereHas('category', function ($query) use ($categoryId) {
                $query->where('id', $categoryId);
            })->with('category')->get();
        } else {
            $inventories = Inventory::with('category')->get();
        }

        return response()->json($inventories);
    }

    public function getByCategory(Request $request, $categoryId)
    {
        $inventories = Inventory::where('category_id', $categoryId)->get();
        return response()->json($inventories);
    }

    public function store(Request $request)
    {
        $inventory = Inventory::create($request->all());
        return response()->json($inventory, 201);
    }

    public function show(Inventory $inventory)
    {
        return response()->json($inventory);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $inventory->update($request->all());
        return response()->json($inventory);
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return response()->json(null, 204);
    }
}