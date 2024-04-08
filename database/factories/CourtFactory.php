<?php

namespace Database\Factories;

use App\Models\CourtType;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Court>
 */
class CourtFactory extends Factory
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
            "description" => fake()->words(5, true),
            "venue_id" => Venue::all()->random()->id,
            "floor_type" => "Wood",
            "court_type_id" => CourtType::all()->random()->id,
            "size" => 200,
            "price" => fake()->numberBetween(10000, 100000),
            "sum_rating" => 0,
            "number_of_people" => 0,
            "status" => 1,
        ];
    }
}
