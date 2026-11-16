<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_reminders')) {
            return;
        }

        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->date('due_date');
            $table->string('repeat')->default('none'); // none, monthly, yearly
            $table->unsignedSmallInteger('remind_days')->default(3);
            $table->boolean('is_active')->default(true);
            $table->date('last_paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reminders');
    }
};
