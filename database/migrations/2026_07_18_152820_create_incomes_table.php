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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('income_category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('bank_account_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('title');

            $table->decimal('amount_bdt', 12, 2)->default(0);

            $table->decimal('amount_usd', 12, 2)->default(0);

            $table->date('income_date');

            $table->enum('payment_method', [
                'Cash',
                'Bank',
                'bKash',
                'Nagad',
                'Rocket',
                'Card',
            ]);

            $table->text('remarks')->nullable();

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
        Schema::dropIfExists('incomes');
    }
};