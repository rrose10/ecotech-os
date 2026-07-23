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
        Schema::create('employee_loans', function (Blueprint $table) {

            $table->id();

            // Employee
            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            // Loan Paid From
            $table->foreignId('bank_account_id')
                ->constrained()
                ->cascadeOnDelete();

            // Loan Information
            $table->decimal('loan_amount', 12, 2);

            $table->decimal('monthly_installment', 12, 2);

            $table->decimal('remaining_balance', 12, 2);

            // Loan Date
            $table->date('loan_date');

            // Payroll Start
            $table->unsignedTinyInteger('start_month');

            $table->unsignedSmallInteger('start_year');

            // Loan Status
            $table->enum('status', [
                'Active',
                'Closed'
            ])->default('Active');

            // Remarks
            $table->text('remarks')->nullable();

            // Expense Relation
            $table->foreignId('expense_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Payroll Relation
            $table->foreignId('payroll_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Created By
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_loans');
    }
};