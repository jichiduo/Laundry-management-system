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

        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_no', 128)->unique();
            $table->unsignedBigInteger('division_id');
            $table->foreign('division_id')->references('id')->on('Divisions');
            $table->string('division_name', 128)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('Customers');
            $table->string('customer_name', 128)->nullable();
            $table->string('customer_tel', 50)->nullable();
            $table->string('customer_email', 50)->nullable();
            $table->string('customer_address', 255)->nullable();
            $table->string('credit_term', 50)->nullable();
            $table->string('currency', 50)->nullable();
            $table->string('base_currency', 50)->nullable();
            $table->decimal('exchange_rate', 16, 8)->nullable();
            $table->string('explain', 255)->nullable();
            $table->string('remark', 255)->nullable();
            $table->decimal('weight', 20, 2)->default(0.00);
            $table->decimal('total', 20, 2)->default(0.00);
            $table->decimal('discount', 20, 2)->default(0.00);
            $table->decimal('tax', 20, 2)->default(0.00);
            $table->decimal('grand_total', 20, 2)->default(0.00);
            $table->unsignedBigInteger('submit_by_userid')->nullable();
            $table->foreign('submit_by_userid')->references('id')->on('users');
            $table->string('submit_by_username', 50)->nullable();
            $table->dateTime('submit_date')->nullable();
            $table->string('status', 50)->default('draft')->index();
            $table->dateTime('pickup_date')->nullable();
            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')->references('id')->on('App_groups');
            $table->string('group_name', 128)->nullable();
            $table->string('delivery_status', 50)->nullable();
            $table->boolean('export_tag')->default(0);
            $table->dateTime('export_date')->nullable();
            $table->foreignId('user_id');
            $table->foreignId('app_group_id');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
