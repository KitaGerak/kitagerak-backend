<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourtTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
}
