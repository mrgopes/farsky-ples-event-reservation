<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Inertia\Inertia;

class EventController extends Controller
{
    public function show($url_slug)
    {
        $event = Event::where('url_slug', $url_slug)
            ->with(['tickets', 'location'])
            ->firstOrFail();

        return Inertia::render('event/Show', [
            'event' => array_merge($event->only([
                'id',
                'title',
                'start_time',
                'url_slug',
                'seats_total',
                'registration_start',
                'registration_end',
                'user_id',
            ])),
            'location' => $event->location,
            'tickets' => $event->tickets,
        ]);
    }
}
