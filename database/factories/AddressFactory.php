<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "street" => fake()->streetName(),
            "city" => fake()->city(),
            "province" => fake()->city(),
            "postal_code" => fake()->postcode(),
            "latitude" => fake()->latitude(),
            "longitude" => fake()->longitude()
        ];
    }
}
