<?php

namespace App\Console\Commands;

use App\Support\AccountsReport;
use Illuminate\Console\Command;

class SaveMonthlyAccounts extends Command
{
    protected $signature = 'reports:monthly {--month=} {--year=} {--all : Save every past month}';

    protected $description = 'Save the monthly accounts PDF (default: last month) in storage/app/backups/reports';

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

        return self::SUCCESS;
    }
}
