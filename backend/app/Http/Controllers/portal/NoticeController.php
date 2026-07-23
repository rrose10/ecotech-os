<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Notice;

class NoticeController extends Controller
{
    /**
     * Display all published notices.
     */
    public function index()
    {
        $notices = Notice::where('is_published', true)
            ->whereDate('publish_date', '<=', today())
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', today());
            })
            ->latest('publish_date')
            ->paginate(10);

        return view('portal.notices.index', compact('notices'));
    }

    /**
     * Display a single notice.
     */
    public function show(Notice $notice)
    {
        if (
            !$notice->is_published ||
            $notice->publish_date->isFuture() ||
            (
                $notice->expiry_date &&
                $notice->expiry_date->isPast()
            )
        ) {
            abort(404);
        }

        return view('portal.notices.show', compact('notice'));
    }
}