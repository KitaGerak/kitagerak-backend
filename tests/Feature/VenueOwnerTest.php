<?php

namespace Tests\Feature;

use Database\Seeders\VenueOwnerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VenueOwnerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterStatus(): void
    {
        $response = $this->post('/api/venue_owners', [
            'name' => 'John Doe',
            'email' => 'johndoe@johndoe.com',
            'phone_number' => '08123456789',
            'password' => 'secretjohndoe',
            'national_id_number' => '1050245708900001',
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                "data" => [
                    "name" => "John Doe",
                    'email' => 'johndoe@johndoe.com',
                    'phone_number' => '08123456789',
                    'national_id_number' => '1050245708900001',
                ]
            ]);
    }

    public function testRegisterEmailAlreadyExist(): void
    {
        // TODO:
    }

    public function testRegisterPhoneNumberAlreadyExist(): void
    {
        // TODO:
    }

    public function testRegisterNationalIDNumberAlreadyExist(): void
    {
        // TODO:
    }

    public function testLoginSuccess(): void
    {
        $this->seed([VenueOwnerSeeder::class]);
        $response = $this->post('/api/venue_owners/login', [
            'email' => 'johnwick@wickjohn.com',
            'password' => 'wickjohn1234'
        ]);

        $response->assertStatus(200);
    }

    public function testLoginFailEmailAlreadyExist(): void
    {
        // TODO:
    }

    public function testLoginFailPasswordWrong(): void
    {
        // TODO:
    }
}
