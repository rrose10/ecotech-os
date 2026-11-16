<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_notifications', function (Blueprint $table) {
            $table->foreignId('notice_id')->nullable()->after('user_id')
                ->constrained('notices')->cascadeOnDelete();
            $table->unique(['user_id', 'notice_id']);
        });
    }

    public function down(): void
    {
        Schema::table('portal_notifications', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'notice_id']);
            $table->dropConstrainedForeignId('notice_id');
        });
    }
};
