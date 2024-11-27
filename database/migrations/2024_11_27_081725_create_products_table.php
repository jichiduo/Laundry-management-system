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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('unit', 20)->nullable();
            $table->decimal('price', 20, 2)->default(0.00);
            $table->string('acc_code', 50)->nullable();
            $table->string('acc_name', 128)->nullable();
            $table->string('status', 50)->nullable()->default('draft');
            $table->string('remark', 255)->nullable();
            $table->boolean('equipment')->default(0);
            $table->string('brand', 50)->nullable();
            $table->string('model', 50)->nullable();
            $table->string('warranty_period', 50)->nullable();
            $table->dateTime('warranty_start_date')->nullable();
            $table->dateTime('warranty_end_date')->nullable();
            $table->string('useful_life', 50)->nullable();
            $table->dateTime('life_end_date')->nullable();
            $table->string('location', 50)->nullable();
            $table->string('type', 50)->nullable()->default('Luandry');
            $table->unsignedBigInteger('group_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
