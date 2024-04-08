<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venue>
 */
class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "name" => fake()->words(2, true),
            "address_id" => Address::all()->random()->id,
            "description" => fake()->words(5, true),
            "image_url" => "https://picsum.photos/200/300",
            "owner_id" => User::where('role_id', 2)->get()->random()->id,
            "status" => 1,
            "status" => 1,
            "open_hour" => fake()->time(),
            "close_hour" => fake()->time(),
            "interval" => 1,
        ];
    }
}
