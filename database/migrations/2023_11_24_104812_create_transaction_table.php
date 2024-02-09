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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable();
            $table->string('order_id');
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('court_id');
            
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('transaction_status_id');
            
            $table->foreign('court_id')->references('id')->on('courts');
            $table->foreign('schedule_id')->references('id')->on('schedules');
            $table->foreign('transaction_status_id')->references('id')->on('transaction_statuses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};
