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
        Schema::create('advance_salaries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('bank_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);

            $table->date('advance_date');

            $table->unsignedTinyInteger('deduction_month');

            $table->unsignedSmallInteger('deduction_year');

            $table->enum('status', [
                'Pending',
                'Deducted',
                'Cancelled'
            ])->default('Pending');

            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_salaries');
    }
};