<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogger;

class NoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notices = Notice::latest()->paginate(10);

        return view('notices.index', compact('notices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('notices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'type'           => 'required|in:General,Important,Holiday,Meeting',
            'publish_date'   => 'required|date',
            'expiry_date'    => 'nullable|date|after_or_equal:publish_date',
            'is_published'   => 'nullable|boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_published'] = $request->boolean('is_published');

        $notice = Notice::create($validated);

        ActivityLogger::log(
            'Notice',
            'Created',
            'Created notice: ' . $notice->title,
            null,
            $notice->toArray()
        );

        return redirect()
            ->route('notices.index')
            ->with('success', 'Notice created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Notice $notice)
    {
        return view('notices.show', compact('notice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notice $notice)
    {
        return view('notices.edit', compact('notice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notice $notice)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'type'           => 'required|in:General,Important,Holiday,Meeting',
            'publish_date'   => 'required|date',
            'expiry_date'    => 'nullable|date|after_or_equal:publish_date',
            'is_published'   => 'nullable|boolean',
        ]);

        $oldData = $notice->toArray();

        $validated['is_published'] = $request->boolean('is_published');

        $notice->update($validated);

        ActivityLogger::log(
            'Notice',
            'Updated',
            'Updated notice: ' . $notice->title,
            $oldData,
            $notice->fresh()->toArray()
        );

        return redirect()
            ->route('notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notice $notice)
    {
        ActivityLogger::log(
            'Notice',
            'Deleted',
            'Deleted notice: ' . $notice->title,
            $notice->toArray(),
            null
        );

        $notice->delete();

        return redirect()
            ->route('notices.index')
            ->with('success', 'Notice deleted successfully.');
    }
}