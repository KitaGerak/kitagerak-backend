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
        Schema::create('venue_open_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venue_id');
            $table->integer('day_of_week');
            $table->time('time_open');
            $table->time('time_close');
            $table->integer('status')->default(1);
            $table->foreign('venue_id')->references('id')->on('venues');
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
        Schema::dropIfExists('venue_open_days');
    }
};
