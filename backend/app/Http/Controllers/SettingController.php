<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public const KEYS = ['company_name', 'company_email', 'company_phone', 'company_address'];

    public function edit()
    {
        $values = [];
        foreach (self::KEYS as $key) {
            $values[$key] = Setting::get($key, $key === 'company_name' ? 'EcoTech' : '');
        }

        return view('settings.edit', compact('values'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:60',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:500',
        ]);

        foreach (self::KEYS as $key) {
            Setting::put($key, $data[$key] ?? null);
        }

        ActivityLogger::log('Settings', 'Updated', 'Updated company settings', null, $data);

        return back()->with('success', 'Settings saved.');
    }
}
