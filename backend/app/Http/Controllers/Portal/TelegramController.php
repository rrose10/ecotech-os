<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Support\Telegram;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    /** Create a one-time code; the profile page shows the t.me link for it. */
    public function link()
    {
        abort_unless(Telegram::configured() && Telegram::username(), 404);

        Auth::user()->forceFill(['telegram_link_code' => Str::lower(Str::random(24))])->save();

        return back();
    }

    public function unlink()
    {
        Auth::user()->forceFill(['telegram_chat_id' => null, 'telegram_link_code' => null])->save();

        return back()->with('success', 'Telegram disconnected.');
    }
}
