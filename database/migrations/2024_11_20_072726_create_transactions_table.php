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
        Schema::disableForeignKeyConstraints();

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('trans_no', 128)->unique();
            $table->string('wo_no', 128)->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->string('customer_name', 128)->nullable();
            $table->string('card_no', 50)->nullable();
            $table->decimal('amount', 20, 2)->default(0.00);
            $table->string('payment_type', 50)->nullable()->default('cash');
            $table->string('remark', 255)->nullable();
            $table->string('create_by', 50)->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
