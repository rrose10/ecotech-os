<?php

namespace App\Console\Commands;

use App\Models\PortalNotification;
use Illuminate\Console\Command;

class AnnounceNotices extends Command
{
    protected $signature = 'notices:announce';

    protected $description = 'Send a portal notification for every notice that became visible (e.g. a future publish date arrived)';

    public function handle(): int
    {
        $this->info(PortalNotification::announceVisibleNotices() . ' notification(s) sent.');

        return 0;
    }
}
