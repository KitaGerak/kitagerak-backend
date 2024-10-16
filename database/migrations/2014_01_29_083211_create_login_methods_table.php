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
        Schema::create('login_methods', function (Blueprint $table) {
            $table->id();
            $table->string('login_with');
            $table->timestamps();
        });

        DB::table('login_methods')->insert(
            [
                'login_with' => 'email',
            ]
        );
        DB::table('login_methods')->insert(
            [
                'login_with' => 'google_gmail',
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
        Schema::dropIfExists('login_methods');
    }
};
