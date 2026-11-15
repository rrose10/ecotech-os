<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * An early, unused "assets" table (name, category, serial_number, condition…)
 * already exists. Upgrade it in place to the schema the Assets module needs.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assets')) {
            return;
        }

        $addCode = ! Schema::hasColumn('assets', 'asset_code');

        Schema::table('assets', function (Blueprint $table) use ($addCode) {
            if ($addCode) {
                $table->string('asset_code')->nullable();
            }
            if (! Schema::hasColumn('assets', 'status')) {
                $table->string('status')->default('Available');
            }
            if (! Schema::hasColumn('assets', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->index();
            }
            if (! Schema::hasColumn('assets', 'notes')) {
                $table->text('notes')->nullable();
            }
        });

        // Give any pre-existing rows a code, then enforce uniqueness
        if ($addCode) {
            foreach (DB::table('assets')->whereNull('asset_code')->pluck('id') as $id) {
                DB::table('assets')->where('id', $id)->update(['asset_code' => 'AST-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT)]);
            }

            Schema::table('assets', function (Blueprint $table) {
                $table->unique('asset_code');
            });
        }

        Schema::table('assets', function (Blueprint $table) {
            $table->string('category')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Non-destructive upgrade; nothing to undo.
    }
};
