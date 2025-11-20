<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Hall',
            'svg_map' => '/seatmap.svg',
            'places_total' => $this->faker->numberBetween(20, 400),
        ];
    }
}

