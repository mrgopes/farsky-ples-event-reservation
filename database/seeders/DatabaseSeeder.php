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
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create additional users
        $users = User::factory()->count(5)->create();
        $allUsers = collect([$admin])->merge($users);

        // Get existing locations
        $locations = \App\Models\Location::all();

        if ($locations->isEmpty()) {
            $this->command->error('No locations found. Please create locations first.');
            return;
        }

        // Create realistic events
        $events = collect([
            [
                'title' => 'Farský ples 2025',
                'url_slug' => 'farsky-ples-2025',
                'description' => "# Srdečne Vás pozývame\n\nNa tradičný **Farský ples**, ktorý sa uskutoční vo Vajnoroch.\n\n## Program večera:\n- 19:00 - Otvorenie plesu\n- 20:00 - Večera\n- 21:00 - Tombola\n- 22:00 - Tanec do rána\n\nTešíme sa na Vás!",
                'start_time' => now()->addMonths(2)->setTime(19, 0),
                'registration_start' => now()->subDays(5),
                'registration_end' => now()->addMonths(2)->subDays(3),
                'contact_name' => 'Ján Novák',
                'contact_email' => 'jan.novak@example.sk',
                'contact_phone' => '+421 901 234 567',
                'bank_account' => 'SK31 1200 0000 1987 4263 7541',
                'multiple_reservations_per_ticket' => true,
            ],
            [
                'title' => 'Vianočný koncert 2024',
                'url_slug' => 'vianocny-koncert-2024',
                'description' => "# Vianočný koncert\n\nPríďte si vychutnať krásne vianočné melódie v podaní miestneho speváckeho zboru.\n\n**Vstup voľný!**",
                'start_time' => now()->addWeeks(2)->setTime(18, 0),
                'registration_start' => now()->subDays(10),
                'registration_end' => now()->addWeeks(2)->subDays(1),
                'contact_name' => 'Mária Kováčová',
                'contact_email' => 'maria.kovacova@example.sk',
                'contact_phone' => '+421 902 345 678',
                'bank_account' => 'SK42 0900 0000 0051 1234 5678',
                'multiple_reservations_per_ticket' => false,
            ],
            [
                'title' => 'Divadelné predstavenie - Cyrano z Bergeracu',
                'url_slug' => 'cyrano-z-bergeracu',
                'description' => "# Cyrano z Bergeracu\n\nKlasická divadelná hra v modernom prevedení.\n\n## O predstavení\nCyrano je príbeh o láske, odvahe a vnútornej kráse. Nenechajte si ujsť túto jedinečnú príležitosť vidieť túto nesmrteľnú klasiku!",
                'start_time' => now()->addMonth()->setTime(19, 30),
                'registration_start' => now(),
                'registration_end' => now()->addMonth()->subDays(2),
                'contact_name' => 'Peter Horváth',
                'contact_email' => 'peter.horvath@example.sk',
                'contact_phone' => '+421 903 456 789',
                'bank_account' => 'SK83 1100 0000 0026 1234 5678',
                'multiple_reservations_per_ticket' => true,
            ],
            [
                'title' => 'Študentský ples 2025',
                'url_slug' => 'studentsky-ples-2025',
                'description' => "# Študentský ples gymnázia\n\nPozývame všetkých študentov, rodičov a priateľov školy na náš každoročný ples!\n\n- Živá hudba\n- Bohaté občerstvenie\n- Tombola s cennými cenami",
                'start_time' => now()->addMonths(3)->setTime(18, 0),
                'registration_start' => now(),
                'registration_end' => now()->addMonths(3)->subWeeks(1),
                'contact_name' => 'Eva Slováková',
                'contact_email' => 'eva.slovakova@example.sk',
                'contact_phone' => '+421 904 567 890',
                'bank_account' => 'SK94 0200 0000 0000 1234 5678',
                'multiple_reservations_per_ticket' => true,
            ],
            [
                'title' => 'Jazz večer s Petrom Lípom',
                'url_slug' => 'jazz-vecer-peter-lipa',
                'description' => "# Jazz večer\n\nLegendárny slovenský jazzman **Peter Lípa** vystúpi v našom kultúrnom dome!\n\nPríďte si vychutnať autentický slovenský jazz.",
                'start_time' => now()->addDays(45)->setTime(20, 0),
                'registration_start' => now()->subDays(3),
                'registration_end' => now()->addDays(44),
                'contact_name' => 'Tomáš Varga',
                'contact_email' => 'tomas.varga@example.sk',
                'contact_phone' => '+421 905 678 901',
                'bank_account' => 'SK05 8180 0000 0070 0012 3456',
                'multiple_reservations_per_ticket' => false,
            ],
        ]);

        $createdEvents = [];
        foreach ($events as $eventData) {
            $event = \App\Models\Event::create([
                'user_id' => $allUsers->random()->id,
                'location_id' => $locations->random()->id,
                'seats_total' => $locations->random()->places_total,
                ...$eventData,
            ]);
            $createdEvents[] = $event;

            // Create tickets for each event
            $ticketTypes = [
                ['title' => 'Vstupné - dospelí', 'price' => 15.00],
                ['title' => 'Vstupné - študenti', 'price' => 10.00],
                ['title' => 'Vstupné - deti do 12 rokov', 'price' => 5.00],
                ['title' => 'VIP miesta', 'price' => 25.00],
            ];

            // Randomly select 2-4 ticket types for each event
            $selectedTickets = collect($ticketTypes)->random(rand(2, 4));

            foreach ($selectedTickets as $ticketData) {
                \App\Models\Ticket::create([
                    'event_id' => $event->id,
                    'title' => $ticketData['title'],
                    'price' => $ticketData['price'],
                ]);
            }

            // Create some orders for the event
            $numOrders = rand(3, 8);
            for ($i = 0; $i < $numOrders; $i++) {
                $orderUser = $allUsers->random();

                // Weighted random: 60% pending, 35% paid, 5% cancelled
                $rand = rand(1, 100);
                if ($rand <= 60) {
                    $orderStatus = 'pending';
                } elseif ($rand <= 95) {
                    $orderStatus = 'paid';
                } else {
                    $orderStatus = 'cancelled';
                }

                // Generate unique variable symbol and url slug
                $variableSymbol = str_pad(rand(1000000, 9999999), 10, '0', STR_PAD_LEFT);
                $urlSlug = \Illuminate\Support\Str::random(32);

                $order = \App\Models\Order::create([
                    'event_id' => $event->id,
                    'name' => $orderUser->name,
                    'email' => $orderUser->email,
                    'phone' => '+421 90' . rand(1, 9) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                    'status' => $orderStatus,
                    'variable_symbol' => $variableSymbol,
                    'url_slug' => $urlSlug,
                ]);

                // Add tickets to order
                $eventTickets = $event->tickets;
                $numTicketsInOrder = rand(1, 3);
                $totalPrice = 0;

                for ($j = 0; $j < $numTicketsInOrder; $j++) {
                    $ticket = $eventTickets->random();
                    $amount = rand(1, 4);

                    $order->tickets()->attach($ticket->id, [
                        'amount' => $amount,
                    ]);

                    $totalPrice += $ticket->price * $amount;

                    // Create reservations for paid orders
                    if ($orderStatus === 'paid') {
                        for ($k = 0; $k < $amount; $k++) {
                            \App\Models\Reservation::create([
                                'order_id' => $order->id,
                                'guest_name' => $orderUser->name,
                                'seat_number' => rand(1, 100),
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('✓ Created ' . count($createdEvents) . ' events with tickets and orders');
        $this->command->info('✓ Created ' . $allUsers->count() . ' users');
    }
}
