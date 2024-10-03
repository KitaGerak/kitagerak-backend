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
            $table->string('external_id')->nullable()->unique();
            $table->string('invoice_id')->nullable();
            $table->unsignedBigInteger('schedule_id');
            $table->string('checkout_link')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->integer('amount_rp')->default(0);
            
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('transaction_status_id')->default(5);
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('transaction_status_id')->references('id')->on('transaction_statuses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
