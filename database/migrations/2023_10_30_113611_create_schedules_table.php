<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('court_id');
            $table->date('date');
            $table->time('time_start');
            $table->time('time_finish');
            $table->integer('interval');
            $table->integer('availability')->default(1);
            $table->double('regular_price')->default(0);
            $table->double('member_price')->default(0);
            $table->double('regular_discount')->default(0);
            $table->double('member_discount')->default(0);
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->integer('status')->default(1);
            $table->foreign('court_id')->references('id')->on('courts');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
};
