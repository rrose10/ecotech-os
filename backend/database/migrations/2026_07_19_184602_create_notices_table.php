<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {

            $table->id();

            $table->string('title');

            $table->text('content');

            $table->enum('type', [
                'General',
                'Important',
                'Holiday',
                'Meeting'
            ])->default('General');

            $table->boolean('is_published')
                ->default(true);

            $table->date('publish_date');

            $table->date('expiry_date')
                ->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};