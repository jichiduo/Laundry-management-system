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

        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->string('wo_no', 128);
            $table->foreign('wo_no')->references('wo_no')->on('work_orders');
            $table->string('barcode', 128)->nullable();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('quantity', 20, 2)->default(0.00);
            $table->string('unit', 20)->nullable();
            $table->decimal('price', 20, 4)->default(0.00);
            $table->decimal('total', 20, 2)->default(0.00);
            $table->decimal('discount', 20, 2)->default(0.00);
            $table->decimal('tax_rate', 6, 2)->default(0.00);
            $table->decimal('tax', 20, 2)->default(0.00);
            $table->decimal('sub_total', 20, 2)->default(0.00);
            $table->string('acc_code', 50);
            $table->string('acc_name', 128)->nullable();
            $table->string('status', 50)->default('draft');
            $table->string('remark', 255)->nullable();
            $table->string('location', 100)->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
    }
};
