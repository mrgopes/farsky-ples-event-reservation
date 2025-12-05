<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReservationController extends Controller
{
    public function create(string $url_slug, Request $request)
    {
        $event = Event::where('url_slug', $url_slug)
            ->with(['tickets', 'location'])
            ->firstOrFail();

        $contact_information = $request->get('name');
        $selected_tickets = $request->get('selected_tickets');

        return Inertia::render('event/SeatReservation', [
            'event' => $event,
            'tickets' => $event->tickets,
            'contact_information' => $contact_information,
            'selected_tickets' => $selected_tickets,
            'location' => $event->location,
        ]);
    }
}
