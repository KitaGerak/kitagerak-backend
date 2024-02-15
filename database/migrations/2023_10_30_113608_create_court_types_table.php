<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('court_types', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('status')->default(1);
            $table->timestamps();
        });

        DB::table('court_types')->insert(
            [
                'type' => 'Basket',
            ]
        );

        DB::table('court_types')->insert(
            [
                'type' => 'Badminton',
            ]
        );

        DB::table('court_types')->insert(
            [
                'type' => 'Futsal',
            ]
        );

        DB::table('court_types')->insert(
            [
                'type' => 'Voli',
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('court_types');
    }
};
