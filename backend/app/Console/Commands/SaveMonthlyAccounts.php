<?php

namespace App\Console\Commands;

use App\Support\AccountsReport;
use Illuminate\Console\Command;

class SaveMonthlyAccounts extends Command
{
    protected $signature = 'reports:monthly {--month=} {--year=} {--all : Save every past month}';

    protected $description = 'Save accounts PDFs: last month, any missing older month, and the all-time one (storage/app/backups/reports)';

    public function handle(): int
    {
        if ($this->option('all')) {
            $this->info('Saved ' . AccountsReport::saveAll() . ' monthly PDF file(s).');

            return self::SUCCESS;
        }

        $last  = now()->subMonthNoOverflow();
        $month = (int) ($this->option('month') ?: $last->month);
        $year  = (int) ($this->option('year') ?: $last->year);

        $this->info('Saved ' . AccountsReport::save($month, $year));

        // Any older month that has no PDF yet (first run creates the whole history)
        $filled = AccountsReport::saveAll(false);
        $this->info("Created {$filled} missing monthly PDF file(s).");

        // Yearly PDFs (this year so far, last year, and any year without a PDF)
        $this->info('Saved ' . AccountsReport::saveYears(false) . ' yearly PDF file(s).');

        // Always refresh the all-time PDF
        $this->info('Saved ' . AccountsReport::saveAllTime());

        return self::SUCCESS;
    }
}
