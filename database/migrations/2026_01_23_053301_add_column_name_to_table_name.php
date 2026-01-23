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
        Schema::table('transactions', function (Blueprint $table) {
            //
            $table->biginteger('division_id');
            $table->string('division_name')->nullable();
            $table->biginteger('group_id');
            $table->string('group_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //drop columns
            $table->dropColumn('division_id');
            $table->dropColumn('division_name');
            $table->dropColumn('group_id');
            $table->dropColumn('group_name');
        });
    }
};
