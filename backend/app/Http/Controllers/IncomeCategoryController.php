<?php

namespace App\Http\Controllers;

use App\Models\IncomeCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncomeCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $incomeCategories = IncomeCategory::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('income_categories.index', compact('incomeCategories', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('income_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:income_categories,name',
            'status' => 'required|boolean',
        ]);

        IncomeCategory::create($validated);

        return redirect()
            ->route('income-categories.index')
            ->with('success', 'Income category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(IncomeCategory $incomeCategory)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IncomeCategory $incomeCategory)
    {
        return view('income_categories.edit', compact('incomeCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IncomeCategory $incomeCategory)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('income_categories')->ignore($incomeCategory->id),
            ],
            'status' => 'required|boolean',
        ]);

        $incomeCategory->update($validated);

        return redirect()
            ->route('income-categories.index')
            ->with('success', 'Income category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncomeCategory $incomeCategory)
    {
        $incomeCategory->delete();

        return redirect()
            ->route('income-categories.index')
            ->with('success', 'Income category deleted successfully.');
    }
}