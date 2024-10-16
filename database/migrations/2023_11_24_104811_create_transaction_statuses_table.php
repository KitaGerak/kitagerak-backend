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
        Schema::create('transaction_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        DB::table('transaction_statuses')->insert(
            [
                'status' => 'sedang berlangsung',
            ]
        );

        DB::table('transaction_statuses')->insert(
            [
                'status' => 'dibatalkan sistem',
            ]
        );

        DB::table('transaction_statuses')->insert(
            [
                'status' => 'dibatalkan penyewa',
            ]
        );

        DB::table('transaction_statuses')->insert(
            [
                'status' => 'dibatalkan pemilik lapangan',
            ]
        );

        DB::table('transaction_statuses')->insert(
            [
                'status' => 'menunggu pembayaran',
            ]
        );
        DB::table('transaction_statuses')->insert(
            [
                'status' => 'selesai',
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
        Schema::dropIfExists('order_statuses');
    }
};
