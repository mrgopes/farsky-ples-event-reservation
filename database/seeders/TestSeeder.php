<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create additional users
        $users = User::factory()->count(5)->create();
        $allUsers = collect([$admin])->merge($users);

        // Get DK Vajnory location
        $location = \App\Models\Location::where('address', 'LIKE', '%Vajnory%')->first();

        if (!$location) {
            $this->command->error('Vajnory location not found. Please run migrations first.');
            return;
        }

        $this->command->info("Using location: {$location->address} with {$location->places_total} total places");

        // Define 3 events
        $eventsData = [
            [
                'title' => 'Farský ples 2025 - VYPREDANÉ',
                'url_slug' => 'farsky-ples-2025-vypredane',
                'description' => "# Farský ples 2025\n\n**VYPREDANÉ** - všetky miesta sú obsadené.\n\n## Program večera:\n- 19:00 - Otvorenie plesu\n- 20:00 - Večera\n- 21:00 - Tombola\n- 22:00 - Tanec do rána",
                'start_time' => now()->addMonths(1)->setTime(19, 0),
                'registration_start' => now()->subDays(30),
                'registration_end' => now()->addMonths(1)->subDays(3),
                'target_reservations' => $location->places_total, // Fully booked: 144
            ],
            [
                'title' => 'Študentský ples 2025 - POSLEDNÉ MIESTA',
                'url_slug' => 'studentsky-ples-2025-posledne-miesta',
                'description' => "# Študentský ples 2025\n\n**POZOR: Zostávajú len 2 voľné miesta!**\n\nPozývame všetkých študentov, rodičov a priateľov školy na náš každoročný ples!\n\n- Živá hudba\n- Bohaté občerstvenie\n- Tombola s cennými cenami",
                'start_time' => now()->addMonths(2)->setTime(18, 0),
                'registration_start' => now()->subDays(20),
                'registration_end' => now()->addMonths(2)->subWeeks(1),
                'target_reservations' => $location->places_total - 2, // Almost booked: 142
            ],
            [
                'title' => 'Divadelné predstavenie - Cyrano z Bergeracu',
                'url_slug' => 'cyrano-z-bergeracu-2025',
                'description' => "# Cyrano z Bergeracu\n\nKlasická divadelná hra v modernom prevedení.\n\n## O predstavení\nCyrano je príbeh o láske, odvahe a vnútornej kráse. Ešte je dostatok voľných miest!",
                'start_time' => now()->addMonths(3)->setTime(19, 30),
                'registration_start' => now()->subDays(10),
                'registration_end' => now()->addMonths(3)->subDays(2),
                'target_reservations' => 22, // Few places booked: 22
            ],
        ];

        foreach ($eventsData as $index => $eventData) {
            $targetReservations = $eventData['target_reservations'];
            unset($eventData['target_reservations']);

            $event = \App\Models\Event::create([
                'user_id' => $admin->id,
                'location_id' => $location->id,
                'seats_total' => $location->places_total,
                'contact_name' => 'Ján Novák',
                'contact_email' => 'jan.novak@example.sk',
                'contact_phone' => '+421 901 234 567',
                'bank_account' => 'SK31 1200 0000 1987 4263 7541',
                'multiple_reservations_per_ticket' => true,
                ...$eventData,
            ]);

            // Create ticket types
            $tickets = [
                \App\Models\Ticket::create([
                    'event_id' => $event->id,
                    'title' => 'Vstupné - dospelí',
                    'price' => 15.00,
                ]),
                \App\Models\Ticket::create([
                    'event_id' => $event->id,
                    'title' => 'Vstupné - študenti',
                    'price' => 10.00,
                ]),
                \App\Models\Ticket::create([
                    'event_id' => $event->id,
                    'title' => 'Vstupné - deti',
                    'price' => 5.00,
                ]),
            ];

            // Create orders to reach target reservations
            $this->createOrdersForEvent($event, $tickets, $allUsers, $targetReservations, $location->places_total);

            $this->command->info("✓ Created event: {$event->title} (Target: {$targetReservations} reservations)");
        }

        $this->command->info('✓ Created ' . $allUsers->count() . ' users');
        $this->command->info('✓ Successfully seeded 3 events at DK Vajnory');
    }

    private function createOrdersForEvent($event, $tickets, $allUsers, $targetReservations, $totalSeats)
    {
        $currentReservations = 0;
        $seatCounter = 1;
        $orderCount = 0;

        // Distribution: 50% paid, 30% pending, 20% cancelled
        $statusDistribution = ['paid', 'paid', 'paid', 'paid', 'paid', 'pending', 'pending', 'pending', 'cancelled', 'cancelled'];

        while ($currentReservations < $targetReservations) {
            $orderUser = $allUsers->random();
            $status = $statusDistribution[array_rand($statusDistribution)];

            // Variable number of tickets per order (1-8 seats)
            $remainingNeeded = $targetReservations - $currentReservations;
            $maxTickets = min(8, $remainingNeeded);
            $numSeatsInOrder = rand(1, max(1, $maxTickets));

            // Generate unique identifiers
            $variableSymbol = str_pad(rand(1000000, 9999999), 10, '0', STR_PAD_LEFT);
            $urlSlug = \Illuminate\Support\Str::random(32);

            $order = \App\Models\Order::create([
                'event_id' => $event->id,
                'name' => $orderUser->name,
                'email' => $orderUser->email,
                'phone' => '+421 90' . rand(1, 9) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                'status' => $status,
                'variable_symbol' => $variableSymbol,
                'url_slug' => $urlSlug,
            ]);

            // Attach tickets to order
            $selectedTicket = $tickets[array_rand($tickets)];
            $order->tickets()->attach($selectedTicket->id, [
                'amount' => $numSeatsInOrder,
            ]);

            // Create reservations for all orders (matching ticket amount)
            for ($i = 0; $i < $numSeatsInOrder; $i++) {
                if ($currentReservations >= $targetReservations) {
                    break;
                }

                \App\Models\Reservation::create([
                    'order_id' => $order->id,
                    'guest_name' => $orderUser->name,
                    'seat_number' => $seatCounter,
                ]);

                $seatCounter++;
                $currentReservations++;
            }

            $orderCount++;

            // Safety check to avoid infinite loop
            if ($orderCount > 200) {
                $this->command->warn("Reached order limit for {$event->title}. Created {$currentReservations} reservations.");
                break;
            }
        }

        // Create some additional cancelled and pending orders (for realism)
        $additionalOrders = rand(3, 6);
        for ($i = 0; $i < $additionalOrders; $i++) {
            $orderUser = $allUsers->random();
            $status = rand(0, 1) ? 'cancelled' : 'pending';
            $numTickets = rand(1, 4);

            $variableSymbol = str_pad(rand(1000000, 9999999), 10, '0', STR_PAD_LEFT);
            $urlSlug = \Illuminate\Support\Str::random(32);

            $order = \App\Models\Order::create([
                'event_id' => $event->id,
                'name' => $orderUser->name,
                'email' => $orderUser->email,
                'phone' => '+421 90' . rand(1, 9) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                'status' => $status,
                'variable_symbol' => $variableSymbol,
                'url_slug' => $urlSlug,
            ]);

            $selectedTicket = $tickets[array_rand($tickets)];
            $order->tickets()->attach($selectedTicket->id, [
                'amount' => $numTickets,
            ]);

            // Create reservations for these additional orders too
            for ($j = 0; $j < $numTickets; $j++) {
                \App\Models\Reservation::create([
                    'order_id' => $order->id,
                    'guest_name' => $orderUser->name,
                    'seat_number' => $seatCounter,
                ]);
                $seatCounter++;
            }
        }

        $finalCount = \App\Models\Reservation::whereHas('order', function ($query) use ($event) {
            $query->where('event_id', $event->id);
        })->count();

        $orderCountFinal = \App\Models\Order::where('event_id', $event->id)->count();

        $this->command->info("  → Created {$orderCountFinal} orders with {$finalCount} total reservations");
    }
}
