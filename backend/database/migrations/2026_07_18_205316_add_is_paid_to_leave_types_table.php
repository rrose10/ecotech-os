<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('leave_types', 'is_paid')) {
            return; // column already created by an earlier migration
        }

        Schema::table('leave_types', function (Blueprint $table) {

            $table->boolean('is_paid')
                ->default(true)
                ->after('days_per_year');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {

            $table->dropColumn('is_paid');

        });
    }
};