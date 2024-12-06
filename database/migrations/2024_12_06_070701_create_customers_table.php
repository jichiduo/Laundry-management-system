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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->index();
            $table->string('password', 255)->nullable();
            $table->string('tel', 50)->nullable()->index();
            $table->string('email', 255)->nullable()->index();
            $table->string('address', 255)->nullable();
            $table->string('member_card', 50)->nullable();
            $table->unsignedBigInteger('member_level_id')->nullable();
            $table->string('member_level_name', 255)->nullable();
            $table->decimal('member_discount', 4, 2)->default(0);
            $table->dateTime('member_expire_date')->nullable();
            $table->string('last_trans_no', 128)->nullable();
            $table->decimal('balance', 20, 2)->default(0.00);
            $table->string('remark', 255)->nullable();
            $table->string('create_by', 50)->nullable();
            $table->string('update_by', 50)->nullable();
            $table->boolean('is_active')->default(1);
            $table->unsignedBigInteger('group_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
