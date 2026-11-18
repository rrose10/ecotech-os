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
    public const GROUP = 'telegram_group_chat_id';
    public const GROUP_CODE = 'telegram_group_code';

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

    public static function groupChatId(): ?string
    {
        return Setting::get(self::GROUP);
    }

    /** Secret the admin types in the group (/setgroup CODE) to pick that group. */
    public static function groupCode(): string
    {
        $code = Setting::get(self::GROUP_CODE);

        if (!$code) {
            $code = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(10));
            Setting::put(self::GROUP_CODE, $code);
        }

        return $code;
    }

    /**
     * Send a reminder about some team members.
     * Group set: ONE message in the group listing their names.
     * No group: a private message to each person who connected Telegram.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $users
     */
    public static function remind($users, string $text): void
    {
        if ($users->isEmpty() || !self::configured()) {
            return;
        }

        if ($group = self::groupChatId()) {
            self::send($group, $text . "\n" . $users->map(fn ($u) => '• ' . $u->name)->implode("\n"));

            return;
        }

        foreach ($users as $user) {
            if ($user->telegram_chat_id) {
                self::send($user->telegram_chat_id, $text);
            }
        }
    }
}
