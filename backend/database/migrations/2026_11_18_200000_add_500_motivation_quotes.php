<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Adds 500 more motivational quotes (original short lines, no attribution).
     * Safe to run on an existing install: quotes already present are skipped.
     */
    public function up(): void
    {
        $file = database_path('data/motivation_quotes.txt');
        $lines = array_values(array_filter(array_map('trim', file($file))));

        $existing = DB::table('motivational_quotes')->pluck('quote')
            ->map(fn ($q) => mb_strtolower(trim($q)))->flip();

        $now = now();
        $rows = [];

        foreach ($lines as $quote) {
            if ($existing->has(mb_strtolower($quote))) {
                continue;
            }

            $rows[] = [
                'quote'      => $quote,
                'author'     => null,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('motivational_quotes')->insert($chunk);
        }
    }

    public function down(): void
    {
        $lines = array_values(array_filter(array_map('trim', file(database_path('data/motivation_quotes.txt')))));

        foreach (array_chunk($lines, 100) as $chunk) {
            DB::table('motivational_quotes')->whereNull('author')->whereIn('quote', $chunk)->delete();
        }
    }
};
