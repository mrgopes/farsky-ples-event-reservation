<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin user',
            'email' => 'admin@example.com',
        ]);

        $events = \App\Models\Event::factory()->count(10)->create();
        $events->each(function ($event) {
            \App\Models\Ticket::factory()->count(rand(0, 3))->create([
                'event_id' => $event->id,
            ]);
        });
    }
}
