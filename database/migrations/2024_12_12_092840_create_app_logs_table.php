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
        Schema::create('app_logs', function (Blueprint $table) {
            $table->id();
            $table->string('wo_no', 128)->nullable()->index();
            $table->string('trans_no', 128)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 50)->nullable();
            $table->string('action', 20)->nullable();
            $table->decimal('amount', 20, 2)->nullable();
            $table->string('remark', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_logs');
    }
};
