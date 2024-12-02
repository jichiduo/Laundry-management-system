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
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->string('address', 255)->nullable();
            $table->string('tel', 50)->nullable();
            $table->string('license', 50)->nullable();
            $table->string('logo_file_url', 255)->nullable();
            $table->string('remark', 255)->nullable();
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
        Schema::dropIfExists('divisions');
    }
};
