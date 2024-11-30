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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_no', 128)->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name', 128)->nullable()->index();
            $table->string('customer_tel', 50)->nullable()->index();
            $table->string('customer_email', 50)->nullable();
            $table->string('customer_address', 255)->nullable();
            $table->string('currency', 50)->nullable();
            $table->string('base_currency', 50)->nullable();
            $table->decimal('exchange_rate', 16, 8)->nullable();
            $table->string('explain', 255)->nullable();
            $table->integer('piece')->nullable();
            $table->decimal('total', 20, 2)->default(0.00);
            $table->decimal('discount', 20, 2)->default(0.00);
            $table->decimal('tax', 20, 2)->default(0.00);
            $table->decimal('grand_total', 20, 2)->default(0.00);
            $table->string('status', 50)->default('draft')->index();
            $table->dateTime('pickup_date')->nullable();
            $table->dateTime('collect_date')->nullable();
            $table->boolean('is_express')->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 50)->nullable();
            $table->unsignedBigInteger('division_id')->nullable();
            $table->string('division_name', 128)->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('group_name', 128)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
