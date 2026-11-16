<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PortalNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = PortalNotification::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('portal.notifications.index', compact('notifications'));
    }

    /** Mark one as read and go to what it points at. */
    public function open(PortalNotification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 404);

        $notification->update(['read_at' => $notification->read_at ?? now()]);

        return redirect($notification->url ?: route('portal.notifications'));
    }

    public function readAll()
    {
        PortalNotification::where('user_id', Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        return back();
    }
}
