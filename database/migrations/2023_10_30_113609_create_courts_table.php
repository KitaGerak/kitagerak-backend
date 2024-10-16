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
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->unsignedBigInteger('venue_id');
            $table->string('floor_type');
            $table->unsignedBigInteger('court_type_id')->nullable();
            $table->string('alternate_type')->nullable();
            $table->double('size')->default(0);
            $table->integer('regular_price')->nullable();
            $table->integer('member_price')->nullable();
            // $table->time('open_hour');
            // $table->time('close_hour');
            $table->integer('sum_rating')->default(0);
            $table->integer('number_of_people')->default(0);
            $table->foreign('venue_id')->references('id')->on('venues');
            $table->foreign('court_type_id')->references('id')->on('court_types');
            $table->integer('status')->default(-1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court');
    }
};
