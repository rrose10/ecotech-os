<?php

namespace App\Console\Commands;

use App\Models\Notice;
use App\Models\User;
use App\Support\Celebrations;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PostCelebrationNotices extends Command
{
    protected $signature = 'notices:celebrations';

    private int $authorId = 0;

    protected $description = "Post today's birthday and work-anniversary wishes to the notice board";

    public function handle(): int
    {
        $celebrations = Celebrations::today();
        $posted = 0;

        // Notices need an author; use the first super admin
        $author = User::where('role', 'super_admin')->orderBy('id')->first();

        if (! $author) {
            $this->warn('No super admin found, nothing posted.');

            return self::SUCCESS;
        }

        $this->authorId = $author->id;

        foreach ($celebrations['birthdays'] as $person) {
            $posted += $this->post(
                "Happy Birthday, {$person->name}!",
                "Everyone at EcoTech wishes {$person->name} a very happy birthday. Have a wonderful day!"
            );
        }

        foreach ($celebrations['anniversaries'] as $person) {
            $years = $person->years_with_us;
            $posted += $this->post(
                "{$person->name} completes {$years} ".Str::plural('year', $years).' with us',
                "Congratulations {$person->name} on {$years} ".Str::plural('year', $years).' at EcoTech. Thank you for your work!'
            );
        }

        $this->info("Celebration notices posted: {$posted}");

        return self::SUCCESS;
    }

    /** Create the notice unless the same one already exists for today. Returns 1 when created. */
    private function post(string $title, string $content): int
    {
        $today = today();

        $exists = Notice::where('title', $title)
            ->whereDate('publish_date', $today)
            ->exists();

        if ($exists) {
            return 0;
        }

        Notice::create([
            'title' => $title,
            'content' => $content,
            'type' => 'General',
            'is_published' => true,
            'publish_date' => $today,
            'expiry_date' => $today,
            'created_by' => $this->authorId,
        ]);

        return 1;
    }
}
