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
        Schema::table('store_locators', function (Blueprint $table) {
            $table->unsignedBigInteger('google_business_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_locators', function (Blueprint $table) {
            $table->dropColumn('google_business_id');
        });
    }
};
