<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            "id" => 1,
            "name" => "user",
        ]);

        DB::table('roles')->insert([
            "id" => 2,
            "name" => "venue_owner",
        ]);

        DB::table('roles')->insert([
            "id" => 3,
            "name" => "employee",
        ]);
    }
}
