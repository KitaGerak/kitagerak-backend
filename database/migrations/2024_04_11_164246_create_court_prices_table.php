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
        Schema::create('court_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('court_id');
            $table->integer('duration_in_hour');
            $table->integer('price');
            $table->boolean('is_member_price');
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
        Schema::dropIfExists('court_price');
    }
};
