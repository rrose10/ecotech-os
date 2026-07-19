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
        Schema::table('attendances', function (Blueprint $table) {


            $table->integer('late_minutes')
                ->default(0)
                ->after('working_minutes');


            $table->string('shift')
                ->default('Night')
                ->after('late_minutes');


        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {


            $table->dropColumn([
                'late_minutes',
                'shift',
            ]);


        });
    }

};