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
        // Already created under the old (mis-ordered) migration name on existing installs.
        if (Schema::hasTable('payrolls')) {
            return;
        }

        Schema::create('payrolls', function (Blueprint $table) {

            $table->id();

            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('bank_account_id')
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedTinyInteger('month');

            $table->unsignedSmallInteger('year');

            $table->decimal('basic_salary', 12, 2);

            $table->decimal('bonus', 12, 2)
                ->default(0);

            $table->decimal('total_salary', 12, 2);

            $table->date('payment_date')
                ->nullable();

            $table->enum('status', [
                'Pending',
                'Paid',
            ])->default('Pending');

            $table->text('remarks')
                ->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'employee_id',
                'month',
                'year'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};