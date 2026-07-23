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
        Schema::table('expenses', function (Blueprint $table) {

            $table->foreignId('advance_salary_id')
                ->nullable()
                ->after('payroll_id')
                ->constrained('advance_salaries')
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {

            $table->dropConstrainedForeignId('advance_salary_id');

        });
    }
};