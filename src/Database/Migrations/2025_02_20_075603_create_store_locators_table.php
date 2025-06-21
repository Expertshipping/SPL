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
        Schema::create('store_locators', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('carrier_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_locators');
    }
};
