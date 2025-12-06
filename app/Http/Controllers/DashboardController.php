<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the user's dashboard with their events.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get events owned by the user (via events.user_id)
        $ownedEvents = $user->events()->with(['location', 'tickets', 'orders.reservations'])->get();

        // Get events shared with the user (via pivot table with role)
        $sharedEvents = $user->sharedEvents()->with(['location', 'tickets', 'orders.reservations'])->get();

        // Merge and add a computed 'user_role' property
        $ownedEvents = $ownedEvents->map(function ($event) {
            $event->user_role = 'owner';
            $event->reserved_seats = $event->computeReservedSeats();
            return $event;
        });

        $sharedEvents = $sharedEvents->map(function ($event) {
            $event->user_role = $event->pivot->role;
            $event->reserved_seats = $event->computeReservedSeats();;
            return $event;
        });

        $events = $ownedEvents->merge($sharedEvents)->sortByDesc('start_time')->values();

        return Inertia::render('Dashboard', [
            'events' => $events,
        ]);
    }
}

