<?php

namespace Database\Seeders;

use App\Models\VenueOwner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VenueOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VenueOwner::create([
            'name' => 'John Wick',
            'email' => 'johnwick@wickjohn.com',
            'phone_number' => '08123456789',
            'password' => Hash::make('wickjohn1234'),
            'national_id_number' => '2222222222222222'
        ]);

        VenueOwner::create([
            'name' => 'test',
            'email' => 'test@test.com',
            'phone_number' => '11111111111',
            'password' => 'test',
            'national_id_number' => '3333333333333333'
        ]);
    }
}
