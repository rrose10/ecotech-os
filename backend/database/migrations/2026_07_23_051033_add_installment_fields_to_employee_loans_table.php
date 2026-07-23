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
        Schema::table('employee_loans', function (Blueprint $table) {

            $table->unsignedInteger('total_installments')
                ->default(1)
                ->after('monthly_installment');

            $table->unsignedInteger('paid_installments')
                ->default(0)
                ->after('total_installments');

            $table->unsignedTinyInteger('end_month')
                ->after('start_year');

            $table->unsignedSmallInteger('end_year')
                ->after('end_month');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_loans', function (Blueprint $table) {

            $table->dropColumn([
                'total_installments',
                'paid_installments',
                'end_month',
                'end_year',
            ]);

        });
    }
};