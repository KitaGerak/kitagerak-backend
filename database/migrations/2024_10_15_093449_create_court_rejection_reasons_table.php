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
        Schema::create('court_rejection_reasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('court_id');
            $table->string('reason');
            $table->integer('status');
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
        Schema::dropIfExists('venue_rejection_reasons');
    }
};
