<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ActivityLogger
{
    public static function log(
        string $module,
        string $action,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {

        if (!Auth::check()) {
            return;
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}