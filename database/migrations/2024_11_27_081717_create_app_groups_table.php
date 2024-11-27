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
        Schema::create('app_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->string('currency', 50)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->string('address', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_groups');
    }
};
