<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('facilities')->insert([
            "name" => "Kamar Mandi",
        ]);

        DB::table('facilities')->insert([
            "name" => "Kantin",
        ]);

        DB::table('facilities')->insert([
            "name" => "Parkir Motor",
        ]);

        DB::table('facilities')->insert([
            "name" => "Parkir Mobil",
        ]);

        DB::table('facilities')->insert([
            "name" => "Wifi",
        ]);

        DB::table('facilities')->insert([
            "name" => "Smoking",
        ]);
    }
}
