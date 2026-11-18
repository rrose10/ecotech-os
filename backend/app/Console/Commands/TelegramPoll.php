<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use App\Support\Telegram;
use Illuminate\Console\Command;

class TelegramPoll extends Command
{
    protected $signature = 'telegram:poll';

    protected $description = 'Read new Telegram messages and connect team members who sent /start <code>';

    public function handle(): int
    {
        if (!Telegram::configured()) {
            return 0;
        }

        $offset = (int) Setting::get(Telegram::OFFSET, '0');

        $updates = Telegram::call('getUpdates', ['offset' => $offset, 'timeout' => 0, 'limit' => 50]);

        if (!is_array($updates)) {
            return 0;
        }

        foreach ($updates as $update) {
            $offset = max($offset, ((int) $update['update_id']) + 1);
            $message = $update['message'] ?? null;

            if (!$message || ($message['chat']['type'] ?? '') !== 'private') {
                continue;
            }

            $chatId = (string) $message['chat']['id'];
            $text = trim((string) ($message['text'] ?? ''));

            if (preg_match('/^\/start\s+([A-Za-z0-9]+)$/', $text, $m)) {
                $this->link($chatId, $m[1]);
            } elseif (str_starts_with($text, '/start')) {
                Telegram::send($chatId, "Open EcoTech OS > My Profile > Connect Telegram, then press the link shown there.\nEcoTech OS > My Profile > Connect Telegram থেকে লিংকটি চাপুন।");
            }
        }

        Setting::put(Telegram::OFFSET, (string) $offset);

        return 0;
    }

    private function link(string $chatId, string $code): void
    {
        $user = User::where('telegram_link_code', $code)->first();

        if (!$user) {
            Telegram::send($chatId, "This link has expired. Create a new one in My Profile.\nলিংকটির মেয়াদ শেষ। My Profile থেকে নতুন লিংক নিন।");

            return;
        }

        // One chat belongs to one person: release it from anyone else first.
        User::where('telegram_chat_id', $chatId)->where('id', '!=', $user->id)
            ->update(['telegram_chat_id' => null]);

        $user->forceFill(['telegram_chat_id' => $chatId, 'telegram_link_code' => null])->save();

        Telegram::send($chatId, "✅ Connected, {$user->name}. You will get your check-out reminders here.\n✅ সংযুক্ত হয়েছে, {$user->name}। চেক-আউটের রিমাইন্ডার এখানে পাবেন।");
    }
}
