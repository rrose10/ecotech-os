<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $items = InventoryItem::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('sku', 'like', $term));
            })
            ->when($request->boolean('low'), fn ($q) => $q->whereColumn('quantity', '<=', 'reorder_level'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('inventory.index', [
            'items' => $items,
            'lowCount' => InventoryItem::whereColumn('quantity', '<=', 'reorder_level')->count(),
        ]);
    }

    public function create()
    {
        return view('inventory.form', ['item' => new InventoryItem(['unit' => 'pcs'])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => 'required|string|max:50|unique:inventory_items,sku',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'quantity' => 'nullable|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $opening = (int) ($data['quantity'] ?? 0);
        $data['quantity'] = $opening;
        $data['reorder_level'] = (int) ($data['reorder_level'] ?? 0);

        $item = DB::transaction(function () use ($data, $opening) {
            $item = InventoryItem::create($data);

            if ($opening > 0) {
                StockMovement::create([
                    'inventory_item_id' => $item->id,
                    'type' => 'In',
                    'quantity' => $opening,
                    'balance_after' => $opening,
                    'note' => 'Opening stock',
                    'created_by' => auth()->id(),
                ]);
            }

            return $item;
        });

        ActivityLogger::log('Inventory', 'Created', 'Created item: '.$item->name, null, $item->toArray());

        return redirect()->route('inventory-items.show', $item)->with('success', 'Item added.');
    }

    public function show(InventoryItem $inventory_item)
    {
        return view('inventory.show', [
            'item' => $inventory_item,
            'movements' => $inventory_item->movements()->with('creator')->paginate(15),
        ]);
    }

    public function edit(InventoryItem $inventory_item)
    {
        return view('inventory.form', ['item' => $inventory_item]);
    }

    /** Quantity is changed only through stock movements, never by editing the item. */
    public function update(Request $request, InventoryItem $inventory_item)
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('inventory_items', 'sku')->ignore($inventory_item->id)],
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'reorder_level' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $data['reorder_level'] = (int) ($data['reorder_level'] ?? 0);
        $old = $inventory_item->toArray();

        $inventory_item->update($data);

        ActivityLogger::log('Inventory', 'Updated', 'Updated item: '.$inventory_item->name, $old, $inventory_item->fresh()->toArray());

        return redirect()->route('inventory-items.show', $inventory_item)->with('success', 'Item updated.');
    }

    public function destroy(InventoryItem $inventory_item)
    {
        ActivityLogger::log('Inventory', 'Deleted', 'Deleted item: '.$inventory_item->name, $inventory_item->toArray(), null);

        $inventory_item->delete();

        return redirect()->route('inventory-items.index')->with('success', 'Item deleted.');
    }

    public function move(Request $request, InventoryItem $inventory_item)
    {
        $data = $request->validate([
            'type' => 'required|in:In,Out',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($data, $inventory_item) {
                $item = InventoryItem::lockForUpdate()->findOrFail($inventory_item->id);

                $balance = $data['type'] === 'In'
                    ? $item->quantity + $data['quantity']
                    : $item->quantity - $data['quantity'];

                if ($balance < 0) {
                    throw new \RuntimeException("Not enough stock. Only {$item->quantity} {$item->unit} available.");
                }

                $item->update(['quantity' => $balance]);

                StockMovement::create([
                    'inventory_item_id' => $item->id,
                    'type' => $data['type'],
                    'quantity' => $data['quantity'],
                    'balance_after' => $balance,
                    'note' => $data['note'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        ActivityLogger::log('Inventory', 'Stock '.$data['type'], "{$data['type']} {$data['quantity']} of {$inventory_item->name}");

        return back()->with('success', 'Stock updated.');
    }
}
