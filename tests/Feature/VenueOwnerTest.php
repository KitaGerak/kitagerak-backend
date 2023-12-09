<?php

namespace Tests\Feature;

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

    public function testEmailAlreadyExist(): void
    {
        // TODO:
    }

    public function testPhoneNumberAlreadyExist(): void
    {
        // TODO:
    }

    public function testNationalIDNumberAlreadyExist(): void
    {
        // TODO:
    }
}
