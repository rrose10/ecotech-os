<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Support\OfficeNetwork;
use App\Support\Telegram;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public const KEYS = ['company_name', 'company_email', 'company_phone', 'company_address', OfficeNetwork::SETTING];

    public function edit()
    {
        $values = [];
        foreach (self::KEYS as $key) {
            $values[$key] = Setting::get($key, $key === 'company_name' ? 'EcoTech' : '');
        }

        $currentIp = request()->ip();
        $telegramBot = Telegram::username();
        $telegramGroup = Telegram::groupChatId();
        $groupCode = Telegram::configured() ? Telegram::groupCode() : null;

        return view('settings.edit', compact('values', 'currentIp', 'telegramBot', 'telegramGroup', 'groupCode'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:60',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:500',
            OfficeNetwork::SETTING => [
                'nullable', 'string', 'max:1000',
                function ($attribute, $value, $fail) {
                    foreach (OfficeNetwork::parse($value) as $entry) {
                        if (! OfficeNetwork::validEntry($entry)) {
                            $fail("\"{$entry}\" is not a valid IP address or range (example: 192.168.10.0/24).");
                        }
                    }
                },
            ],
            'telegram_bot_token' => 'nullable|string|max:200',
        ]);

        // Telegram bot: blank keeps the saved token; a new token must work.
        if (!empty($data['telegram_bot_token'])) {
            $username = Telegram::botUsername(trim($data['telegram_bot_token']));

            if (!$username) {
                return back()->withInput()->withErrors(['telegram_bot_token' => 'Telegram did not accept this token. ' . (Telegram::$lastError ?? 'Copy it again from @BotFather.')]);
            }

            Setting::put(Telegram::TOKEN, trim($data['telegram_bot_token']));
            Setting::put(Telegram::USERNAME, $username);
        }

        unset($data['telegram_bot_token']);

        // Store the cleaned list, one entry per line
        $data[OfficeNetwork::SETTING] = implode("\n", OfficeNetwork::parse($data[OfficeNetwork::SETTING] ?? ''));

        foreach (self::KEYS as $key) {
            Setting::put($key, $data[$key] ?? null);
        }

        ActivityLogger::log('Settings', 'Updated', 'Updated company settings', null, $data);

        return back()->with('success', 'Settings saved.');
    }
}
