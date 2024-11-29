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
        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->string('wo_no', 128)->index();
            $table->string('barcode', 128)->nullable();
            $table->string('name', 255)->index();
            $table->text('description')->nullable();
            $table->decimal('quantity', 20, 2)->default(0.00);
            $table->string('unit', 20)->nullable();
            $table->decimal('price', 20, 4)->default(0.00);
            $table->decimal('total', 20, 2)->default(0.00);
            $table->decimal('discount', 20, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax', 20, 2)->default(0.00);
            $table->decimal('sub_total', 20, 2)->default(0.00);
            $table->decimal('turnover', 8, 2)->nullable();
            $table->string('acc_code', 50)->nullable();
            $table->string('acc_name', 128)->nullable();
            $table->string('status', 50)->default('draft')->index();
            $table->string('remark', 255)->nullable();
            $table->string('location', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
    }
};
