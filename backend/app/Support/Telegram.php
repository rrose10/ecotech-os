<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * Tiny Telegram Bot API client. The bot token is kept in Settings.
 * Every call fails quietly (returns null/false) so Telegram problems
 * can never break attendance or payroll.
 */
class Telegram
{
    public const TOKEN = 'telegram_bot_token';
    public const USERNAME = 'telegram_bot_username';
    public const OFFSET = 'telegram_update_offset';

    public static function token(): ?string
    {
        return Setting::get(self::TOKEN);
    }

    public static function configured(): bool
    {
        return (bool) self::token();
    }

    public static function username(): ?string
    {
        return Setting::get(self::USERNAME);
    }

    /** Call a Bot API method. Returns the decoded "result" or null on failure. */
    public static function call(string $method, array $params = [], ?string $token = null, int $timeout = 10): mixed
    {
        $token = $token ?? self::token();

        if (!$token) {
            return null;
        }

        try {
            $response = Http::timeout($timeout)->asForm()
                ->post("https://api.telegram.org/bot{$token}/{$method}", $params);

            return $response->successful() && $response->json('ok') ? $response->json('result') : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    public static function send(string|int $chatId, string $text): bool
    {
        return self::call('sendMessage', ['chat_id' => $chatId, 'text' => $text]) !== null;
    }

    /** The bot's @username for a token, or null when the token is wrong. */
    public static function botUsername(string $token): ?string
    {
        $me = self::call('getMe', [], $token);

        return is_array($me) ? ($me['username'] ?? null) : null;
    }
}
