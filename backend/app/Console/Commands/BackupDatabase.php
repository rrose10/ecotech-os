<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=14 : Number of backups to keep}';

    protected $description = 'Create a database backup in storage/app/backups (SQLite or MySQL)';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $stamp      = now()->format('Y-m-d_His');
        $connection = config('database.default');
        $config     = config("database.connections.{$connection}");

        if ($config['driver'] === 'sqlite') {

            $source = $config['database'];

            if (!is_file($source)) {
                $this->error('SQLite database file not found: ' . $source);
                return self::FAILURE;
            }

            $target = "{$dir}/ecotech_{$stamp}.sqlite";
            File::copy($source, $target);

        } elseif (in_array($config['driver'], ['mysql', 'mariadb'], true)) {

            $target  = "{$dir}/ecotech_{$stamp}.sql";
            $process = new Process(array_filter([
                'mysqldump',
                '--single-transaction',
                env('DB_DUMP_SKIP_SSL') ? '--skip-ssl' : null,
                '--host=' . $config['host'],
                '--port=' . $config['port'],
                '--user=' . $config['username'],
                '--result-file=' . $target,
                $config['database'],
            ]), null, ['MYSQL_PWD' => (string) $config['password']]);

            $process->setTimeout(600)->run();

            if (!$process->isSuccessful()) {
                $this->error('mysqldump failed: ' . $process->getErrorOutput());
                return self::FAILURE;
            }

        } else {
            $this->error('Unsupported database driver: ' . $config['driver']);
            return self::FAILURE;
        }

        $this->backupUploads($dir, $stamp);

        // Keep only the newest backups, separately for database dumps and upload archives
        foreach (['ecotech_files_*.tar.gz', 'ecotech_2*'] as $pattern) {
            collect(glob("{$dir}/{$pattern}") ?: [])
                ->reject(fn ($f) => $pattern === 'ecotech_2*' && str_contains($f, 'ecotech_files_'))
                ->sortByDesc(fn ($f) => filemtime($f))
                ->values()
                ->slice((int) $this->option('keep'))
                ->each(fn ($f) => File::delete($f));
        }

        $this->info('Backup created: ' . $target);

        return self::SUCCESS;
    }

    /**
     * Archive uploaded files (employee photos, NID copies) next to the database dump.
     * A failure here is reported but never fails the database backup.
     */
    private function backupUploads(string $dir, string $stamp): void
    {
        $app = storage_path('app');
        $dirs = array_values(array_filter(['public', 'private'], fn ($d) => is_dir("{$app}/{$d}")));

        if (!$dirs) {
            return;
        }

        $archive = "{$dir}/ecotech_files_{$stamp}.tar.gz";
        $process = new Process(array_merge(['tar', '-czf', $archive, '-C', $app], $dirs));
        $process->setTimeout(600)->run();

        if ($process->isSuccessful()) {
            $this->info('Uploads archived: ' . $archive);
        } else {
            $this->warn('Uploads archive failed: ' . $process->getErrorOutput());
        }
    }
}
