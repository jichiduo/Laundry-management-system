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
        Schema::table('member_levels', function (Blueprint $table) {
            $table->decimal('topup_amount', 20, 2)->default(0.00);
            $table->integer('effective_days')->default(0);
            $table->string('remark')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_levels', function (Blueprint $table) {
            //
            $table->dropColumn('topup_amount');
            $table->dropColumn('effective_days');
            $table->dropColumn('remark');
        });
    }
};
