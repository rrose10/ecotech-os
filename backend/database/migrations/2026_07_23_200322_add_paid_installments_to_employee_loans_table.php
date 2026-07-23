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

            $table->integer('paid_installments')
                ->default(0)
                ->after('total_installments');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_loans', function (Blueprint $table) {

            $table->dropColumn('paid_installments');

        });
    }
};