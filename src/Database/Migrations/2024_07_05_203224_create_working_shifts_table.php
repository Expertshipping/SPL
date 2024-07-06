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
        Schema::create('working_shifts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->index();
            $table->string('day');
            $table->integer('hours_number');
            $table->time('start_time');
            $table->time('end_time');
            $table->date('start_on');
            $table->date('end_on');
            $table->string('notes');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_shifts');
    }
};
