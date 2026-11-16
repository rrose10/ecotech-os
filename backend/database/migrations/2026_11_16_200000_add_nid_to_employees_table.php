<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('nid_number', 20)->nullable()->unique()->after('emergency_contact');
            $table->string('nid_file')->nullable()->after('nid_number');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['nid_number']);
            $table->dropColumn(['nid_number', 'nid_file']);
        });
    }
};
