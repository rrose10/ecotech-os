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
        Schema::table('payrolls', function (Blueprint $table) {

            $table->decimal('advance_amount', 12, 2)
                ->default(0)
                ->after('bonus');

            $table->decimal('loan_amount', 12, 2)
                ->default(0)
                ->after('advance_amount');

            $table->decimal('net_salary', 12, 2)
                ->default(0)
                ->after('total_salary');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {

            $table->dropColumn([
                'advance_amount',
                'loan_amount',
                'net_salary',
            ]);

        });
    }
};