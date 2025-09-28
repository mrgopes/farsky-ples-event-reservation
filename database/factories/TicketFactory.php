<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'title' => $this->faker->words(2, true),
            'price' => $this->faker->randomFloat(2, 5, 100),
            'reservations' => $this->faker->numberBetween(0, 100),
        ];
    }
}

