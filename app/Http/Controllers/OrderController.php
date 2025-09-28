<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function create($url_slug)
    {
        $event = Event::where('url_slug', $url_slug)
            ->with('tickets')
            ->firstOrFail();
        return Inertia::render('event/Order', [
            'event' => $event,
            'tickets' => $event->tickets,
        ]);
    }
}
