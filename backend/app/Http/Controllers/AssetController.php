<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Employee;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $assets = Asset::with('employee')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('asset_code', 'like', $term)
                    ->orWhere('category', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('assets.index', [
            'assets' => $assets,
            'statuses' => Asset::STATUSES,
            'totalValue' => Asset::where('status', '!=', 'Retired')->sum('purchase_price'),
        ]);
    }

    public function create()
    {
        return view('assets.form', [
            'asset' => new Asset(['status' => 'Available']),
            'employees' => Employee::orderBy('name')->get(),
            'statuses' => Asset::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $asset = Asset::create($data);

        ActivityLogger::log('Asset', 'Created', 'Created asset: '.$asset->name, null, $asset->toArray());

        return redirect()->route('assets.index')->with('success', 'Asset added.');
    }

    public function edit(Asset $asset)
    {
        return view('assets.form', [
            'asset' => $asset,
            'employees' => Employee::orderBy('name')->get(),
            'statuses' => Asset::STATUSES,
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $old = $asset->toArray();

        $asset->update($this->validated($request, $asset));

        ActivityLogger::log('Asset', 'Updated', 'Updated asset: '.$asset->name, $old, $asset->fresh()->toArray());

        return redirect()->route('assets.index')->with('success', 'Asset updated.');
    }

    public function destroy(Asset $asset)
    {
        ActivityLogger::log('Asset', 'Deleted', 'Deleted asset: '.$asset->name, $asset->toArray(), null);

        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Asset deleted.');
    }

    private function validated(Request $request, ?Asset $asset = null): array
    {
        $data = $request->validate([
            'asset_code' => ['required', 'string', 'max:50', Rule::unique('assets', 'asset_code')->ignore($asset?->id)],
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'status' => ['required', Rule::in(Asset::STATUSES)],
            'employee_id' => 'nullable|exists:employees,id',
            'notes' => 'nullable|string',
        ]);

        $data['purchase_price'] = $data['purchase_price'] ?? 0;

        // Keep status and holder consistent
        if ($data['status'] === 'Assigned') {
            if (empty($data['employee_id'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['employee_id' => 'Choose who this asset is assigned to.']);
            }
        } else {
            $data['employee_id'] = null;
        }

        return $data;
    }
}
