<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    /** Request-level cache so the sidebar does not query on every call. */
    protected static ?array $cache = null;

    public static function get(string $key, ?string $default = null): ?string
    {
        if (static::$cache === null) {
            static::$cache = Schema::hasTable('settings')
                ? static::query()->pluck('value', 'key')->all()
                : [];
        }

        $value = static::$cache[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    /** Forget the request-level cache (used by tests and after bulk changes). */
    public static function flush(): void
    {
        static::$cache = null;
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::$cache = null;
    }
}
