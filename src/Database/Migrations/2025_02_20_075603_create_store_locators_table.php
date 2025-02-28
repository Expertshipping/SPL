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
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade');
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
